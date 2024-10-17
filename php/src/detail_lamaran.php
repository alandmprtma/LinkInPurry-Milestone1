<?php
session_start();

// Cek apakah pengguna sudah login dan apakah role adalah 'company'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: auth/login.html');
    exit();
}

// Cek apakah parameter lamaran_id ada di URL
if (!isset($_GET['lamaran_id'])) {
    echo "ID Lamaran tidak ditemukan!";
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

// Ambil lamaran_id dari URL
$lamaran_id = $_GET['lamaran_id'];

// Query untuk mendapatkan detail lamaran
$query = "SELECT Lamaran.*, Users.nama, Users.email, Lowongan.posisi
          FROM Lamaran
          JOIN Users ON Lamaran.user_id = Users.user_id
          JOIN Lowongan ON Lamaran.lowongan_id = Lowongan.lowongan_id
          WHERE Lamaran.lamaran_id = :lamaran_id AND Lowongan.company_id = :company_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['lamaran_id' => $lamaran_id, 'company_id' => $_SESSION['user_id']]);
$lamaran = $stmt->fetch();

if (!$lamaran) {
    echo "<p>Lamaran tidak ditemukan atau Anda tidak berhak mengakses lamaran ini.</p>";
    exit();
}

// Jika lamaran ditemukan, tampilkan detailnya
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Lamaran - Company</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Menggunakan CSS global -->
</head>
<body>

<div class="container">
    <h1>Detail Lamaran untuk Posisi: <?php echo htmlspecialchars($lamaran['posisi']); ?></h1>

    <p><strong>Nama Pelamar:</strong> <?php echo htmlspecialchars($lamaran['nama']); ?></p>
    <p><strong>Email Pelamar:</strong> <?php echo htmlspecialchars($lamaran['email']); ?></p>

    <p><strong>Status Lamaran:</strong> <?php echo htmlspecialchars($lamaran['status']); ?></p>
    <p><strong>Tanggal Lamaran:</strong> <?php echo htmlspecialchars($lamaran['tanggal_lamaran']); ?></p>

    <p><strong>Catatan Pelamar:</strong></p>
    <p><?php echo nl2br(htmlspecialchars($lamaran['catatan'])); ?></p>

    <!-- Opsi untuk mengubah status lamaran -->
    <h3>Ubah Status Lamaran</h3>
    <form action="ubah_status_lamaran.php" method="POST">
        <input type="hidden" name="lamaran_id" value="<?php echo $lamaran['lamaran_id']; ?>">
        <label for="status">Status:</label>
        <select name="status" id="status" required>
            <option value="diterima" <?php if($lamaran['status'] == 'diterima') echo 'selected'; ?>>Diterima</option>
            <option value="ditolak" <?php if($lamaran['status'] == 'ditolak') echo 'selected'; ?>>Ditolak</option>
            <option value="dalam peninjauan" <?php if($lamaran['status'] == 'dalam peninjauan') echo 'selected'; ?>>Dalam Peninjauan</option>
        </select>
        <button type="submit" class="btn">Simpan Perubahan</button>
    </form>

    <a href="lowongan_detail.php?lowongan_id=<?php echo $lamaran['lowongan_id']; ?>" class="btn">Kembali ke Detail Lowongan</a>
</div>

</body>
</html>
