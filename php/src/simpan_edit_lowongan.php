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
$lowongan_id = $_POST['lowongan_id']; // lowongan_id dari form hidden input
$posisi = $_POST['posisi'];
$deskripsi = $_POST['deskripsi'];
$jenis_pekerjaan = $_POST['jenis_pekerjaan'];
$jenis_lokasi = $_POST['jenis_lokasi'];

// Update data lowongan
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

// Hapus attachment lama jika dipilih
if (isset($_POST['delete_attachments'])) {
    foreach ($_POST['delete_attachments'] as $attachment_id) {
        // Ambil path file sebelum dihapus
        $query = "SELECT file_path FROM AttachmentLowongan WHERE attachment_id = :attachment_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['attachment_id' => $attachment_id]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($file) {
            $file_path = $file['file_path'];

            // Hapus file dari server
            if (file_exists($file_path)) {
                unlink($file_path); // Hapus file dari folder
            }

            // Hapus file dari database
            $query = "DELETE FROM AttachmentLowongan WHERE attachment_id = :attachment_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['attachment_id' => $attachment_id]);
        }
    }

}

// Proses upload attachment baru
if (isset($_FILES['attachments'])) {
    $uploadDir = 'uploads/images/'; // Direktori penyimpanan file

    foreach ($_FILES['attachments']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
            $file_ext = pathinfo($_FILES['attachments']['name'][$key], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_ext;
            $file_path = $uploadDir . $file_name;

            // Pindahkan file ke direktori yang ditentukan
            if (move_uploaded_file($tmpName, $file_path)) {
                // Simpan path file ke tabel AttachmentLowongan
                $queryAttachment = "INSERT INTO AttachmentLowongan (lowongan_id, file_path) VALUES (:lowongan_id, :file_path)";
                $stmtAttachment = $pdo->prepare($queryAttachment);
                $stmtAttachment->execute([
                    'lowongan_id' => $lowongan_id,
                    'file_path' => $file_path,
                ]);
            }
        }
    }

    
}

// Setelah berhasil, arahkan kembali ke halaman home company
header('Location: home_company.php');
exit();
?>
