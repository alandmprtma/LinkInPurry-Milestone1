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
$query ="SELECT u.nama, c.*
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
</head>
<body style="flex-direction: column; justify-content: flex-start; width: 100%;">
<div class="nameplate" style="background-color: #D9D9D9; width: 80%; padding: 5% 10% 5% 10%;">
    <img style="width: 10dvw; height: 10dvw;" src="../test.png">
    <h1><?php echo htmlspecialchars($profile['nama']);?></h1>
    <strong>Lokasi:</strong> <?php echo htmlspecialchars($profile['lokasi']);?>
</div>
<div class="container">
    <p><strong>About</strong></p>
    <p><?php echo htmlspecialchars($profile['about']);?></p>
</div>
<div class="container">
    <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."</p>
</div>

</body>
</html>