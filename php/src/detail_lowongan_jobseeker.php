<?php
session_start();

// Cek apakah pengguna sudah login dan role-nya adalah 'jobseeker'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
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

// Query untuk mendapatkan detail lowongan dan profil perusahaan
$query = "SELECT L.*, U.nama AS company_name, U.email AS company_email FROM Lowongan L
          JOIN Users U ON L.company_id = U.user_id
          WHERE L.lowongan_id = :lowongan_id AND L.is_open = TRUE";
$stmt = $pdo->prepare($query);
$stmt->execute(['lowongan_id' => $lowongan_id]);
$lowongan = $stmt->fetch();

if (!$lowongan) {
    echo "<p>Lowongan tidak ditemukan atau sudah ditutup.</p>";
    exit();
}

// Cek apakah job seeker sudah melamar ke lowongan ini
$query = "SELECT * FROM Lamaran WHERE lowongan_id = :lowongan_id AND user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['lowongan_id' => $lowongan_id, 'user_id' => $_SESSION['user_id']]);
$lamaran = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Lowongan - <?php echo htmlspecialchars($lowongan['posisi']); ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">
    <h1><?php echo htmlspecialchars($lowongan['posisi']); ?></h1>

    <!-- Menampilkan Profil Perusahaan -->
    <section class="company-profile">
        <h2>Company: <?php echo htmlspecialchars($lowongan['company_name']); ?></h2>
        <p>Email: <?php echo htmlspecialchars($lowongan['company_email']); ?></p>
    </section>

    <!-- Menampilkan Deskripsi Lowongan -->
    <section class="job-description">
        <h3>Job Description</h3>
        <p><?php echo htmlspecialchars($lowongan['deskripsi']); ?></p>
        <p><strong>Job Type:</strong> <?php echo htmlspecialchars($lowongan['jenis_pekerjaan']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($lowongan['jenis_lokasi']); ?></p>
    </section>

    <!-- Cek apakah job seeker sudah melamar -->
    <section class="application-status">
        <?php if (!$lamaran): ?>
            <p>You have not applied for this position yet.</p>
            <!-- Tombol navigasi ke halaman lamaran -->
            <a href="apply.php?lowongan_id=<?php echo $lowongan_id; ?>" class="btn">Apply for this Job</a>
        <?php else: ?>
            <h3>Your Application</h3>
            <p>Status: <?php echo htmlspecialchars($lamaran['status']); ?></p>

            <!-- Tampilkan alasan/tindak lanjut jika ada -->
            <?php if (!empty($lamaran['status_reason'])): ?>
                <p><strong>Reason/Next Step:</strong> <?php echo htmlspecialchars($lamaran['status_reason']); ?></p>
            <?php endif; ?>

            <!-- Cek apakah ada lampiran CV atau video perkenalan -->
            <?php if (!empty($lamaran['cv_path'])): ?>
                <p><a href="<?php echo htmlspecialchars($lamaran['cv_path']); ?>" target="_blank">View your CV</a></p>
            <?php endif; ?>
            <?php if (!empty($lamaran['video_path'])): ?>
                <p><a href="<?php echo htmlspecialchars($lamaran['video_path']); ?>" target="_blank">Watch your introduction video</a></p>
            <?php endif; ?>

            

            <p>You cannot reapply for the same job.</p>
        <?php endif; ?>
    </section>

    <a href="home_jobseeker.php" class="btn">Back to Home</a>
</div>

</body>
</html>
