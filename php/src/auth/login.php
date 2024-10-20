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
    // Jika gagal terhubung, kirim respon JSON dengan pesan error
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Koneksi ke database gagal']);
    exit();
}

// Validasi input
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password_input = $_POST['password'];

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
    exit();
}

// Query untuk mendapatkan pengguna berdasarkan email
$query = "SELECT * FROM Users WHERE email = :email";
$stmt = $pdo->prepare($query);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

header('Content-Type: application/json');

if ($user && password_verify($password_input, $user['password'])) {
    // Jika login berhasil, buat session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['nama'] = $user['nama'];

    // Kirim respon JSON berhasil dengan role
    echo json_encode(['success' => true, 'role' => $user['role']]);
} else {
    // Jika login gagal, kirim respon JSON gagal
    echo json_encode(['success' => false, 'message' => 'Email atau password salah']);
}
