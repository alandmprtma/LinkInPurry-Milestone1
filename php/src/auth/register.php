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

if ($_SERVER["REQUEST_METHOD"] == "POST"){


    // Ambil data dari form
    $role = $_POST['role']; // 'jobseeker' atau 'company'
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash password sebelum menyimpan ke database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $retval = array("Success"=>FALSE,"Reason"=>"");

    if ($nama != NULL && $email != NULL && $password != NULL){

        try {
            if (!preg_match('/^[\w\-\.]+@([\w\-]+\.)+[\w\-]+$/',$email)){
                $retval["Reason"] = "Bad email!";
            }
            else{
            // Cek apakah email sudah ada di database
            $query = "SELECT COUNT(*) FROM Users WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['email' => $email]);
            $emailCount = $stmt->fetchColumn();

            if ($emailCount > 0) {
                // Jika email sudah terdaftar
                $retval["Reason"] = "Email already exists!";
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
                $retval["Success"] = TRUE;
                }
            }
            }
            catch (PDOException $e){
                $retval["Reason"] = $e;
            }
        
    }
    else{
               //input form gak bener
    }
    echo json_encode($retval);
    exit();
}
else{
    echo "What";
    exit();
}
?>