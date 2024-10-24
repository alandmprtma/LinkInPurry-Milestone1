<?php
session_start();

// Koneksi ke database
$host = 'db'; // Jika di luar Docker, gunakan 'localhost'
$dbname = 'linkinpurry_db';
$user = 'user';
$password = 'userpassword';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}

function emailcheck($emailtocheck){
    global $pdo;
    
    if (!preg_match('/^[\w\-\.]+@([\w\-]+\.)+[\w\-]+$/',$emailtocheck)){
        return "Bad email!";
    }
    else{
        try{
            // Cek apakah email sudah ada di database
            $query = "SELECT COUNT(*) FROM Users WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['email' => $emailtocheck]);
            $emailCount = $stmt->fetchColumn();

            if ($emailCount > 0) {
                // Jika email sudah terdaftar
                return "Email already exists!";
            }
            else {
                return "";
            }
        }
        catch (PDOException $e){
            return $e;
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST"){

    $rawjsondata = file_get_contents('php://input');
    $jsondata = json_decode($rawjsondata, TRUE);

    $retval = array("success"=>FALSE,"reason"=>"");

    if (!isset($jsondata)){
        $retval["reason"] = "JSON Missing!";
        echo json_encode($retval);
        exit();
    }

    // Ambil data dari form
    $email = $jsondata['email'];

    if ($jsondata["intent"] == "email-check"){
        $emailcheck = emailcheck($email);
        if ($emailcheck != ""){
            $retval["reason"] = $emailcheck;
        }
        else{
            $retval["success"] = TRUE;
        }
    }
    else if ($jsondata["intent"] == "register"){
        $role = $jsondata['role']; // 'jobseeker' atau 'company'
        $nama = $jsondata['nama'];
        $password = $jsondata['password'];

        // Hash password sebelum menyimpan ke database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if ($nama != NULL && $email != NULL && $password != NULL){

            try {
                $emailcheck = emailcheck($email);
                if ($emailcheck != "") {
                    $retval["reason"] = $emailcheck;
                }
                else{
                    // Jika email belum terdaftar, masukkan ke database
                    $query = "INSERT INTO Users (email, password, role, nama) VALUES (:email, :password, :role, :nama)";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([
                        'email' => $email,
                        'password' => $hashedPassword,
                        'role' => $role,
                        'nama' => $nama
                    ]);

                    if ($role == 'company'){
                        $location = "";
                        $about = "";

                        if (isset($jsondata["location"])) {$location = $jsondata["location"];}
                        if (isset($jsondata["about"])) {$about = $jsondata["about"];}

                        $query = " WITH email AS (SELECT user_id FROM users WHERE email = :email)
                                   INSERT INTO companydetail (user_id, lokasi, about)
                                   SELECT email.user_id, :lokasi, :about
                                   FROM email;
                                 ";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute([
                            'email' => $email,
                            'lokasi' => $location,
                            'about' => $about
                        ]);
                    }

                    $retval["success"] = TRUE;
                }
            }
            catch (PDOException $e){
                $retval["reason"] = $e;
            }
            
        }
        else{
            //missing data
            $retval["reason"] = "Missing data!";
        }
    }
    echo json_encode($retval);
    exit();
}
else{
    header("Location: ../auth/register.html");
    exit();
}
?>