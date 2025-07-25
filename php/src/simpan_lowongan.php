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

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $posisi = $_POST['posisi'];
    $deskripsi = $_POST['deskripsi'];
    $jenis_pekerjaan = $_POST['jenis_pekerjaan'];
    $jenis_lokasi = $_POST['jenis_lokasi'];

    // Mulai transaksi
    $pdo->beginTransaction();

    try {
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

        // Dapatkan ID lowongan yang baru saja dimasukkan
        $lowongan_id = $pdo->lastInsertId();

        // Proses upload attachment
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

        // Commit transaksi jika semua berjalan lancar
        $pdo->commit();

        // Redirect ke halaman home company setelah sukses
        header('Location: home_company.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaksi jika ada error
        $pdo->rollBack();
        die("Terjadi kesalahan: " . $e->getMessage());
    }
}
?>
