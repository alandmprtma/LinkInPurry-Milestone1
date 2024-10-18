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
if (!isset($_GET['lowongan_id'])) {
    echo "ID Lowongan tidak ditemukan!";
    exit();
}

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

// Query untuk mendapatkan daftar lamaran dari lowongan ini
$queryLamaran = "SELECT Lamaran.lamaran_id, Users.nama, Lamaran.status 
                 FROM Lamaran 
                 JOIN Users ON Lamaran.user_id = Users.user_id 
                 WHERE Lamaran.lowongan_id = :lowongan_id";
$stmtLamaran = $pdo->prepare($queryLamaran);
$stmtLamaran->execute(['lowongan_id' => $lowongan_id]);
$lamaranList = $stmtLamaran->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Lowongan Baru</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles_js.css"> <!-- Menggunakan CSS global -->
</head>
<body>

<nav class="navbar">


    <img class="logo" src="assets/LinkInPurry-crop.png">
    <div class="search-bar">
        <div class="icon">
            <img src="assets/search-icon-removebg-preview-mirror.png" alt="Search Icon">
        </div>
        <input type="text" placeholder="Search">
    </div>
    <ul class="nav-links">
        <li><a class="inactive" href="/"> <img class="home" src="assets/home_grey.png"> Home</a></li>
        <li><a class="inactive" href="/jobs"> <img class="job" src="assets/suitcase-grey.png"> My Jobs</a></li>
        <li><a class="inactive" href="auth/logout.php"> <img class="logout" src="assets/logout-grey.png"> Log Out</a></li>
    </ul>
</nav>
<main style='align-content: center;'>
<section style='width:60%'>
<section class="job-details">
    <div class="container">
        
        <h1 class="form-heading"> <i class="fas fa-building"></i> <?php echo $_SESSION['nama']; ?></h1>
        <li class="line" style="padding-bottom: 10px"><hr class="divider" /></li>
        <h2 ><?php echo htmlspecialchars($lowongan['posisi']); ?></h2>
        <h4 style='color: #666;'><i class="fa fa-briefcase" style='margin-right:10px'></i><?php echo htmlspecialchars($lowongan['jenis_lokasi']); ?> • <?php echo htmlspecialchars($lowongan['jenis_pekerjaan']); ?></h4>
        <h3 style='padding-top: 20px;'>About the job</h3>
        <p  style='padding-top: 20px; text-align:justify;'><?php echo htmlspecialchars($lowongan['deskripsi']); ?></p>
        <p style="font-weight:bold; font-size:14px; color:#666666; margin-top:15px;">
                <?php if ($lowongan['is_open']): ?>
                    <span>Open</span>
                <?php else: ?>
                    <span>Closed</span>
                <?php endif; ?>
            </p>
        <?php
            // Query untuk menghitung jumlah applicants (pelamar)
            $lowongan_id = $lowongan['lowongan_id'];
            $stmt = $pdo->prepare("SELECT COUNT(*) AS total_applicants FROM Lamaran WHERE lowongan_id = :lowongan_id");
            $stmt->execute(['lowongan_id' => $lowongan_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_applicants = $result['total_applicants'];
            ?>

            <!-- Menampilkan jumlah applicants -->
            <p style="font-size:14px; color:#666666;"> <?= htmlspecialchars($total_applicants); ?> applicants</p>
        <div style='padding-top: 20px; display: flex; align-items: end; justify-content: flex-end;'>
            <i class="fas fa-edit delete-icon"></i>
            <a href="hapus_lowongan.php?lowongan_id=<?php echo $lowongan['lowongan_id']; ?>">  <i class="fas fa-trash-alt delete-icon"></i>
            </a>
        </div>

    </div>
</section>
<div  class="applicant-list">
            <h2>Daftar Pelamar</h2>

            <!-- Jika ada pelamar, tampilkan dalam tabel -->
            <?php if ($lamaranList): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Pelamar</th>
                            <th>Status Lamaran</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lamaranList as $lamaran): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($lamaran['nama']); ?></td>
                                <td><?php echo htmlspecialchars($lamaran['status']); ?></td>
                                <td>
                                    <!-- Link ke halaman detail lamaran -->
                                    <a href="detail_lamaran.php?lamaran_id=<?php echo $lamaran['lamaran_id']; ?>" class="btn">Lihat Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada pelamar untuk lowongan ini.</p>
            <?php endif; ?>
        </div>
</section>

<aside class="job-seeker-guidance">
<div class="profile-card" style="width: 100%;" >
        <div class="header">
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
