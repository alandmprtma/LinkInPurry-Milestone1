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
$lowongan_id = $_GET['lowongan_id'];

// Query untuk mendapatkan detail lowongan
$query = "SELECT * FROM Lowongan WHERE lowongan_id = :lowongan_id AND company_id = :company_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['lowongan_id' => $lowongan_id, 'company_id' => $_SESSION['user_id']]);
$lowongan = $stmt->fetch();

if (!$lowongan) {
    echo "<p>Lowongan tidak ditemukan atau Anda tidak berhak mengakses lowongan ini.</p>";
    exit();
}

// Jika lowongan ditemukan, tampilkan detailnya
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Lowongan - Company</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Menggunakan CSS global -->
</head>
<body>

<div class="container">
    <h1>Detail Lowongan: <?php echo htmlspecialchars($lowongan['posisi']); ?></h1>
    <p><strong>Deskripsi Pekerjaan:</strong></p>
    <p><?php echo htmlspecialchars($lowongan['deskripsi']); ?></p>

    <p><strong>Jenis Pekerjaan:</strong> <?php echo htmlspecialchars($lowongan['jenis_pekerjaan']); ?></p>
    <p><strong>Jenis Lokasi:</strong> <?php echo htmlspecialchars($lowongan['jenis_lokasi']); ?></p>

    <p><strong>Status Lowongan:</strong> <?php echo $lowongan['is_open'] ? 'Terbuka' : 'Tertutup'; ?></p>

    <!-- Opsi untuk menutup lowongan -->
    <?php if ($lowongan['is_open']): ?>
    <form action="tutup_lowongan.php" method="POST">
        <input type="hidden" name="lowongan_id" value="<?php echo $lowongan['lowongan_id']; ?>">
        <button type="submit" class="btn-danger">Tutup Lowongan</button>
    </form>
    <?php else: ?>
    <p>Lowongan ini telah ditutup.</p>
    <?php endif; ?>

    <a href="edit_lowongan.php?lowongan_id=<?php echo $lowongan['lowongan_id']; ?>" class="btn">Edit Lowongan</a>
    <a href="home_company.php" class="btn">Kembali ke Home</a>
</div>

</body>
</html>
