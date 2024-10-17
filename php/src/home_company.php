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

// Query untuk mendapatkan daftar lowongan yang dibuat oleh company yang sedang login
$query = "SELECT * FROM Lowongan WHERE company_id = :company_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['company_id' => $_SESSION['user_id']]);
$lowonganList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Company</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Menggunakan CSS global -->
</head>
<body>

<div class="container">
    <h1>Welcome, <?php echo $_SESSION['nama']; ?> (Company)</h1>
    <p>Daftar lowongan yang Anda buat:</p>

    <!-- Tampilkan Daftar Lowongan -->
    <?php if ($lowonganList): ?>
        <table>
            <thead>
                <tr>
                    <th>Posisi</th>
                    <th>Status</th>
                    <th>Detail Lowongan</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lowonganList as $lowongan): ?>
                <tr>
                    <td><?php echo htmlspecialchars($lowongan['posisi']); ?></td>
                    <td><?php echo $lowongan['is_open'] ? 'Terbuka' : 'Tertutup'; ?></td>
                    <td>
                        <!-- Link ke halaman detail lowongan -->
                        <a href="lowongan_detail.php?lowongan_id=<?php echo $lowongan['lowongan_id']; ?>" class="btndetail">Lihat Detail</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Anda belum membuat lowongan pekerjaan.</p>
    <?php endif; ?>

    <a href="buat_lowongan.php" class="btn">Buat Lowongan</a>
    <a href="auth/logout.php" class="btn-danger">Logout</a>
</div>

</body>
</html>
