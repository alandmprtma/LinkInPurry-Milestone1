<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.html');
    exit();
}

// Menampilkan UI untuk job seeker
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Job Seeker</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Menggunakan CSS global -->
</head>
<body>

    <div class="container">
        <h1>Welcome, <?php echo $_SESSION['nama']; ?> (Job Seeker)</h1>
        <p>Anda sekarang masuk sebagai pencari kerja.</p>
        <a href="auth/logout.php">Logout</a>
    </div>

</body>
</html>
