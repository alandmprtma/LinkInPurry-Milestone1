<?php
session_start();

// Cek apakah pengguna sudah login dan apakah role adalah 'company'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: auth/index.html');
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
$posisi = $_POST['posisi'];
$deskripsi = $_POST['deskripsi'];
$jenis_pekerjaan = $_POST['jenis_pekerjaan'];
$jenis_lokasi = $_POST['jenis_lokasi'];

// Simpan data lowongan ke database
$query = "INSERT INTO Lowongan (company_id, posisi, deskripsi, jenis_pekerjaan, jenis_lokasi, is_open, created_at) 
          VALUES (:company_id, :posisi, :deskripsi, :jenis_pekerjaan, :jenis_lokasi, TRUE, NOW())";

$stmt = $pdo->prepare($query);
$stmt->execute([
    'company_id' => $_SESSION['user_id'],
    'posisi' => $posisi,
    'deskripsi' => $deskripsi,
    'jenis_pekerjaan' => $jenis_pekerjaan,
    'jenis_lokasi' => $jenis_lokasi,
]);

// Setelah berhasil, arahkan kembali ke halaman home company
header('Location: home_company.php');
exit();
?>
