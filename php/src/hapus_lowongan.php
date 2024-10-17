<?php
session_start();

// Cek apakah pengguna sudah login dan apakah role adalah 'company'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
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

// Ambil lowongan_id dari URL
if (!isset($_GET['lowongan_id'])) {
    echo "ID Lowongan tidak ditemukan!";
    exit();
}

$lowongan_id = $_GET['lowongan_id'];

// Hapus lowongan dan semua lamaran terkait
$query = "DELETE FROM Lowongan WHERE lowongan_id = :lowongan_id AND company_id = :company_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['lowongan_id' => $lowongan_id, 'company_id' => $_SESSION['user_id']]);

// Setelah berhasil, arahkan kembali ke halaman home
header('Location: home_company.php');
exit();
?>
