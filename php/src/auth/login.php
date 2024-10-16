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

// Mengambil data dari form
$email = $_POST['email'];
$password_input = $_POST['password'];

// Query untuk mendapatkan pengguna berdasarkan email
$query = "SELECT * FROM Users WHERE email = :email";
$stmt = $pdo->prepare($query);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if ($user && password_verify($password_input, $user['password'])) {
    // Jika login berhasil, buat session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['nama'] = $user['nama'];

    // Redirect ke halaman yang sesuai berdasarkan role
    if ($user['role'] == 'company') {
        header('Location: ../home_company.php'); // Halaman untuk company
    } else {
        header('Location: ../home_jobseeker.php'); // Halaman untuk job seeker
    }
    exit();
} else {
    // Jika login gagal
    echo "<p style='color:red;text-align:center;'>Email atau password salah!</p>";
    echo "<a href='login.html'>Kembali ke Login</a>";
}
?>
