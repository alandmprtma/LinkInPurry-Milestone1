<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.html');
    exit();
}

// Koneksi ke database
$host = 'db'; // Atau localhost jika di luar Docker
$dbname = 'linkinpurry_db';
$user = 'user';
$password = 'userpassword';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}

// Toss the mf user back if he inputted nothing lmao
// Cek apakah pengguna sudah login
if (!isset($_GET['user_id'])) {
    if ($_SESSION['role'] == "company") {
        header('Location: home_company.php');
    }
    else if ($_SESSION['role'] == "jobseeker") {
        header('Location: home_jobseeker.php');
    }
    else {
        header('Location: auth/login.html');
    }
    exit();
}

// Get profile id
$wanted_id = $_GET['user_id'];

// Query untuk mendapatkan detail lamaran
$query ="SELECT u.nama as nama, c.*
         FROM users AS u, companydetail AS c
         WHERE u.user_id = :wanted_user AND u.user_id = c.user_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['wanted_user' => $wanted_id]);
$profile = $stmt->fetch();

if (!$profile) {
    echo "<p>Profile perusahaan tidak ditemukan.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Company</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Menggunakan CSS global -->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.6.0/css/all.css">
</head>
<body style="flex-direction: column; justify-content: flex-start; width: 100%; background-color: #EFEFEF;">
    <div class="box" style=" width: 80% ;border: 10%; border-radius: 1dvw; margin: 1%; background-color: white; box-shadow: 0px 0px 5px;">
        <div class="nameplate" style="background-color: #D9D9D9; width: 80%; padding: 5% 10% 5% 10%; border-top-left-radius: inherit; border-top-right-radius: inherit;">
        <!-- <img style="width: 10dvw; height: 10dvw; transform: translateY(90%);" src="../test.png"> -->
        </div>
        <div class="text-content" style="padding: 5%;">
            <h1><?php echo $profile["nama"]?></h1>
            <i class="fa-solid fa-location-dot"></i> <?php echo $profile["lokasi"]?>
        </div>
    </div>
    <div class="box" style="width: 80% ;border: 10%; border-radius: 1dvw; margin: 1%; background-color: white; box-shadow: 0px 0px 5px;">
        <div class="text-content" style="padding: 5%;">
            <h1>About</h1>
            <p><?php echo $profile["about"]?></p>
        </div>

    </div>
    <div class="deadspace" style="height: 5%;">
        <hr>
    </div>
</body>
</html>