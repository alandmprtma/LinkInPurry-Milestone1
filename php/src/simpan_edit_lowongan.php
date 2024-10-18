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
$lowongan_id = $_POST['lowongan_id']; // lowongan_id dari form hidden input
$posisi = $_POST['posisi'];
$deskripsi = $_POST['deskripsi'];
$jenis_pekerjaan = $_POST['jenis_pekerjaan'];
$jenis_lokasi = $_POST['jenis_lokasi'];

// Query untuk update data lowongan
$query = "UPDATE Lowongan 
          SET posisi = :posisi, 
              deskripsi = :deskripsi, 
              jenis_pekerjaan = :jenis_pekerjaan, 
              jenis_lokasi = :jenis_lokasi, 
              updated_at = NOW() 
          WHERE lowongan_id = :lowongan_id AND company_id = :company_id";

$stmt = $pdo->prepare($query);
$stmt->execute([
    'posisi' => $posisi,
    'deskripsi' => $deskripsi,
    'jenis_pekerjaan' => $jenis_pekerjaan,
    'jenis_lokasi' => $jenis_lokasi,
    'lowongan_id' => $lowongan_id,
    'company_id' => $_SESSION['user_id'],
]);

// Setelah berhasil, arahkan kembali ke halaman home company
header('Location: home_company.php');
exit();
?>
