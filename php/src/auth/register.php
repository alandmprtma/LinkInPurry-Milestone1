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

// Ambil data dari form
$role = $_POST['role']; // 'jobseeker' atau 'company'
$nama = $_POST['nama'];
$email = $_POST['email'];
$password = $_POST['password'];

// Hash password sebelum menyimpan ke database
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Cek apakah email sudah ada di database
$query = "SELECT COUNT(*) FROM Users WHERE email = :email";
$stmt = $pdo->prepare($query);
$stmt->execute(['email' => $email]);
$emailCount = $stmt->fetchColumn();

if ($emailCount > 0) {
    // Jika email sudah terdaftar
    echo "<p style='color:red;text-align:center;'>Email sudah terdaftar!</p>";
    echo "<a href='register.html'>Kembali ke Register</a>";
    exit();
}

// Jika email belum terdaftar, masukkan ke database
$query = "INSERT INTO Users (email, password, role, nama) VALUES (:email, :password, :role, :nama)";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'email' => $email,
    'password' => $hashedPassword,
    'role' => $role,
    'nama' => $nama
]);

// Setelah registrasi berhasil, arahkan ke halaman login
header('Location: login.html');
exit();
?>
