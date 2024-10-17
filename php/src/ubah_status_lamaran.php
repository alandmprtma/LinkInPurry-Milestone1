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

// Ambil data dari form
$lamaran_id = $_POST['lamaran_id'];
$status = $_POST['status'];

// Update status lamaran di database
$query = "UPDATE Lamaran SET status = :status WHERE lamaran_id = :lamaran_id AND EXISTS (
              SELECT 1 FROM Lowongan WHERE Lowongan.lowongan_id = Lamaran.lowongan_id AND company_id = :company_id)";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'status' => $status,
    'lamaran_id' => $lamaran_id,
    'company_id' => $_SESSION['user_id']
]);

// Setelah berhasil, kembali ke halaman detail lamaran
header("Location: detail_lamaran.php?lamaran_id=$lamaran_id");
exit();
?>
