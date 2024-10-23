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

// Ambil lowongan_id dari POST request
if (!isset($_POST['lowongan_id'])) {
    echo "ID Lowongan tidak ditemukan!";
    exit();
}

$lowongan_id = $_POST['lowongan_id'];

// Query untuk mendapatkan nama lowongan
$queryLowongan = "SELECT posisi FROM Lowongan WHERE lowongan_id = :lowongan_id";
$stmtLowongan = $pdo->prepare($queryLowongan);
$stmtLowongan->execute(['lowongan_id' => $lowongan_id]);
$lowongan = $stmtLowongan->fetch(PDO::FETCH_ASSOC);

if (!$lowongan) {
    echo "Lowongan tidak ditemukan!";
    exit();
}

// Ambil nama lowongan
$namaLowongan = $lowongan['posisi'];

// Bersihkan nama lowongan dari karakter yang tidak diizinkan dalam nama file
$namaLowonganBersih = preg_replace('/[^a-zA-Z0-9-_]/', '_', $namaLowongan);

// Query untuk mendapatkan daftar lamaran
$queryLamaran = "SELECT Lamaran.lamaran_id, Users.nama, Lamaran.created_at, Lamaran.cv_path, Lamaran.video_path, Lamaran.status
                 FROM Lamaran 
                 JOIN Users ON Lamaran.user_id = Users.user_id 
                 WHERE Lamaran.lowongan_id = :lowongan_id";
$stmtLamaran = $pdo->prepare($queryLamaran);
$stmtLamaran->execute(['lowongan_id' => $lowongan_id]);
$lamaranList = $stmtLamaran->fetchAll(PDO::FETCH_ASSOC);

// Fungsi untuk ekspor CSV
function exportToCSV($lamaranList, $namaLowonganBersih, $namaLowongan) {
    // Set header untuk download file CSV dengan nama yang dinamis
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $namaLowonganBersih . '_daftar_pelamar.csv"');

    // Buka stream output
    $output = fopen('php://output', 'w');

    // Tulis header CSV
    fputcsv($output, ['Nama', 'Pekerjaan yang Dilamar', 'Tanggal Melamar', 'URL CV', 'URL Video', 'Status Lamaran']);

    // Tulis data ke CSV
    foreach ($lamaranList as $row) {
        fputcsv($output, [
            $row['nama'],
            $namaLowongan,  // Nama lowongan di kolom "Pekerjaan yang Dilamar"
            $row['created_at'],
            $row['cv_path'],
            $row['video_path'],
            $row['status']
        ]);
    }

    // Tutup stream
    fclose($output);
    exit();
}

// Panggil fungsi ekspor CSV dengan menambahkan $namaLowongan
exportToCSV($lamaranList, $namaLowonganBersih, $namaLowongan);
?>
