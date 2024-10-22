<?php
session_start();

// Cek apakah pengguna sudah login dan apakah role adalah 'company'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: auth/index.html');
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
$query = "SELECT Lamaran.*, Users.nama, Users.email, Lowongan.posisi, Lowongan.jenis_lokasi, Lowongan.jenis_pekerjaan
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
    <title>Buat Lowongan Baru</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles_dl.css"> <!-- Menggunakan CSS global -->
</head>
<body>

<nav class="navbar">
    <img class="logo" src="assets/LinkInPurry-crop.png">
    </div>
    <ul class="nav-links">
        <li><a class="inactive" href="/"> <img class="home" src="assets/home_grey.png"> Home</a></li>
        <li><a class="inactive" href="/jobs"> <img class="job" src="assets/suitcase-grey.png"> My Jobs</a></li>
        <li><a class="inactive" href="auth/logout.php"> <img class="logout" src="assets/logout-grey.png">Log Out</a></li>
    </ul>
</nav>
<main style='align-content: center;'>
<section style='width:60%'>
<section class="job-details">
    <div class="container">
    <div class="form-header">
    <h1 class='form-heading'><?php echo htmlspecialchars($lamaran['posisi']); ?></h1>
    <a href="lowongan_detail.php?lowongan_id=<?php echo $lamaran['lowongan_id']; ?>" class="btn btn-secondary">Back</a>
    </div>
        <h4 style='color: #666;'><i class="fa fa-briefcase" style='margin-right:10px'></i><?php echo htmlspecialchars($lamaran['jenis_lokasi']); ?> • <?php echo htmlspecialchars($lamaran['jenis_pekerjaan']); ?></h4>
        <h5 style='color: #666;'>Applied At: <?php echo htmlspecialchars(date('F d, Y H:i', strtotime($lamaran['created_at']))); ?></h5>
        <li class="line" style="padding-bottom: 8px; padding-top: 12px;"><hr class="divider" /></li>
        <h2  style='font-size: 20px; color: #333;'>Contact Info</h2>
        <h3  style='font-size: 16px; color: #333; margin-top: 5px;'><?php echo htmlspecialchars($lamaran['nama']); ?></h3>
        <h4 style='padding-top: 20px; color: #666';>Email address</h4>
        <p   style='color: #333;'><?php echo htmlspecialchars($lamaran['email']); ?></p>
        <li class="line" style="padding-top: 10px; padding-bottom: 10px;"><hr class="divider" /></li>
        <h2  style='font-size: 20px; color: #333;'>Resume</h2>
        <div class="embed-pdf-container">
            <embed src="<?php echo htmlspecialchars($lamaran['cv_path']); ?>" type="application/pdf" />
        </div>
        <li class="line" style="padding-top: 10px; padding-bottom: 10px;"><hr class="divider" /></li>
        <h2  style='font-size: 20px; color: #333;'>Video</h2>
        <p><strong>Introduction Video:</strong></p>
        <?php if (!empty($lamaran['video_path'])): ?>
        <div class="video-container">

            <!-- Tampilkan video perkenalan -->
            <video controls>
                <source src="<?php echo htmlspecialchars($lamaran['video_path']); ?>" type="video/mp4">
                Browser Anda tidak mendukung pemutar video.
            </video>
        </div>
        <?php else: ?>
            <p>Introduction Video are not available.</p>
        <?php endif; ?>

    </div>
</section>

    <!-- Opsi untuk mengubah status lamaran, hanya jika status waiting -->
    <?php if ($lamaran['status'] == 'waiting'): ?>
        <div class="form-container">
            <h3>Application Status</h3>
            <form action="ubah_status_lamaran.php" method="POST">
                <input type="hidden" name="lamaran_id" value="<?php echo $lamaran['lamaran_id']; ?>">
                
                <p style = 'margin-top:10px; margin-bottom:10px;'><strong>Status:</strong> <?php echo htmlspecialchars($lamaran['status']); ?></p>
                <select name="status" id="status" required>
                    <option value="accepted" <?php if($lamaran['status'] == 'accepted') echo 'selected'; ?>>Diterima</option>
                    <option value="rejected" <?php if($lamaran['status'] == 'rejected') echo 'selected'; ?>>Ditolak</option>
                    <option value="waiting" <?php if($lamaran['status'] == 'waiting') echo 'selected'; ?>>Dalam Peninjauan</option>
                </select>

                <!-- Tindak lanjut dalam bentuk rich text (HTML editor) -->
                <label for="status_reason">Follow-Up / Reasons:</label>
                <textarea name="status_reason" id="status_reason" rows="4"></textarea>
                <div style='display: flex; align-items: end; justify-content: flex-end;'>
                    <button type="submit" class="btn">Save</button>
                </div>
            </form>
    <?php endif; ?>
</section>

<aside class="job-seeker-guidance">
<div class="profile-card" style="width: 100%;" >
        <div class="header">
        <div class="avatar">
            <img src="assets/company.jpg" alt="Avatar">
        </div>
        </div>
        <div class="body">
            <h3><?php echo $_SESSION['nama']; ?></h3>
            <p><?php echo $_SESSION['email']; ?></p>
            <p class="location">Tangerang, Banten</p>
        </div>
        <div class="footer">
            <span>Company</span>
        </div>
    </div>
            <div class="guidance-card">
                <h3>Post a free job</h3>
                <p class="recommendation">Reach top talent effortlessly</p>
                <div class="guidance-content">
                    <div class="guidance-text">
                        <div class="guidance-headline">
                            <strong style="margin-top:5px;">Find Your Ideal Candidate Today</strong>
                            <div class="guidance-image">
                                <img class="" src="assets/candidate.png" alt="Resume Improvement">
                            </div>
                        </div>
                        <p style="margin-top:15px;">Ready to expand your team? Posting a job has never been easier! Share your job listing with a wide audience of qualified candidates looking for opportunities just like yours. With our user-friendly platform, you can customize your job post to attract the best talent, all at no cost to you!</p>
                        <a href="buat_lowongan.php" class="show-more">Start Posting Now <span>&#8594;</span></a>
                    </div>
                </div>
            </div>
        </aside>
<main>


</body>
</html>
