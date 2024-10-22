<?php
session_start();

// Cek apakah pengguna sudah login dan role-nya adalah 'company'
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'company') {
    header('Location: 404.html');
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
          WHERE L.lowongan_id = :lowongan_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['lowongan_id' => $lowongan_id]);
$lowongan = $stmt->fetch();

$is_open = $lowongan['is_open'];

// Query untuk mendapatkan gambar attachment yang terkait dengan lowongan
$queryAttachments = "SELECT file_path FROM AttachmentLowongan WHERE lowongan_id = :lowongan_id";
$stmtAttachments = $pdo->prepare($queryAttachments);
$stmtAttachments->execute(['lowongan_id' => $lowongan_id]);
$attachments = $stmtAttachments->fetchAll();


// Hanya cek lamaran jika pengguna sudah login
$lamaran = false;
if (isset($_SESSION['user_id'])) {
    // Cek apakah job seeker sudah melamar ke lowongan ini
    $query = "SELECT * FROM Lamaran WHERE lowongan_id = :lowongan_id AND user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['lowongan_id' => $lowongan_id, 'user_id' => $_SESSION['user_id']]);
    $lamaran = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Lowongan - <?php echo htmlspecialchars($lowongan['posisi']); ?></title>
    <link rel="stylesheet" href="css/styles_dlj.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <img class="logo" src="assets/LinkInPurry-crop.png">
    <ul class="nav-links">
        <li><a class="inactive" href="/home.php"> <img src="assets/home_grey.png"> Home</a></li>
        <li><a class="inactive" href="/jobs"> <img class="job" src="assets/suitcase-grey.png"> My Jobs</a></li>
        <li><a class="inactive" href="auth/logout.php"> <img class="logout" src="assets/logout-grey.png"> Log Out</a></li>
    </ul>
</nav>

<div class="parent-container">
<div class="job-details-js">
    <h2><?php echo htmlspecialchars($lowongan['company_name']); ?></h2>
    <h1><?php echo htmlspecialchars($lowongan['posisi']); ?></h1>
    <h3><i class="fa fa-briefcase" style='margin-right:10px'></i><?php echo htmlspecialchars($lowongan['jenis_lokasi']); ?> • <?php echo htmlspecialchars($lowongan['jenis_pekerjaan']); ?></h3>
    <div class="attachments">
        <h3>Job Attachments:</h3>
        <div class="attachment-images">
            <?php foreach ($attachments as $attachment): ?>
                <img src="<?php echo htmlspecialchars($attachment['file_path']); ?>" alt="Job Image" style="max-width: 200px; margin-right: 10px;">
            <?php endforeach; ?>
        </div>
    </div>

    
    <p style="font-size:14px; color:#666666; margin-top:15px;">
            <span><strong>Created At:</strong> <?php echo htmlspecialchars(substr($lowongan['created_at'], 0, 19)); ?></span><br>
            <span><strong>Updated At:</strong> <?php echo htmlspecialchars(substr($lowongan['updated_at'], 0, 19)); ?></span>
    </p>
    <!-- Cek apakah user login -->
    <section class="application-status">
        <?php if ($is_open): ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (!$lamaran): ?>
                    <p>You have not applied for this position yet.</p>
                    <!-- Tombol navigasi ke halaman lamaran -->
                    <form action="apply.php" method="get">
                        <input type="hidden" name="lowongan_id" value="<?php echo htmlspecialchars($lowongan_id); ?>">
                        <button type="submit" class="apply-button">Easy Apply</button>
                    </form>
                <?php else: ?>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($lamaran['status']); ?></p>
                    <?php if (!empty($lamaran['status_reason'])): ?>
                        <p><strong>Reason/Next Step:</strong> <?php echo htmlspecialchars($lamaran['status_reason']); ?></p>
                    <?php endif; ?>
                    <div class="attachment">
                        <?php if (!empty($lamaran['cv_path'])): ?>
                            <h4 class="cv-link"><a href="<?php echo htmlspecialchars($lamaran['cv_path']); ?>" target="_blank">View your CV</a></h4>
                        <?php endif; ?>
                        <?php if (!empty($lamaran['video_path'])): ?>
                            <h4><a href="<?php echo htmlspecialchars($lamaran['video_path']); ?>" target="_blank">Watch your introduction video</a></h4>
                        <?php endif; ?>
                    </div>
                    <p>You cannot reapply for the same job.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>You need to login to apply for this job.</p>
                <button class="apply-button" disabled>Easy Apply</button>
            <?php endif; ?>
        <?php else: ?>
            <p>This job position has been closed.</p>
            <button class="apply-button" disabled>Easy Apply</button>
        <?php endif; ?>
    </section>
</div>

<div class="job-details-a">
<h1>About the job</h1>
<p><?php echo $lowongan['deskripsi']; ?></p>
</div>

</div>

</body>
</html>
