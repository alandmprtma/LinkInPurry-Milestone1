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

$query = "SELECT L.*, U.nama AS company_name FROM Lowongan L JOIN Users U ON L.company_id = U.user_id WHERE L.company_id = :company_id";;
$stmt = $pdo->prepare($query);
$stmt->execute(['company_id' => $_SESSION['user_id']]);
$lowonganList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkInPurry</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles_js.css">
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
                <li><a class="current" href="/"> <img src="assets/home_black.png"> Home</a></li>
                <li><a class="inactive" href="/jobs"> <img class="job" src="assets/suitcase-grey.png"> My Jobs</a></li>
                <li><a class="inactive" href="auth/logout.php"> <img class="logout" src="assets/logout-grey.png"> Log Out</a></li>
            </ul>
        </nav>

    <main style='align-content: center;'>
    <aside class='left-aside'>
    <div class="profile-card">
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
        <aside class="filters">
            <h3>Filter</h3>
            <div class="filter-group">
                <label for="location">Location</label>
                <input type="text" id="location" placeholder="Enter location">
            </div>
            <div class="filter-group">
                <label for="job-type">Job Type</label>
                <select id="job-type">
                    <option value="all">All</option>
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="contract">Contract</option>
                </select>
            </div>
            <button class="apply-filters">Apply Filters</button>
        </aside>
    </aside>
<section style="width: 38%;">
<div class="card-header">
    <div class="card-content">
        <h2>Hi <?php echo $_SESSION['nama'];?>, are you hiring?</h2>
        <p>Discover free and easy ways to find a great hire, fast!</p>
    </div>
    </div>
    <section class="job-listings">
    <div class="header">
        <h2>Posted Jobs</h2>
    </div>

    <ul class="job-cards">
        <?php foreach ($lowonganList as $index => $lowongan): ?>
            <li class="vacancy-card">
        <div>
            <h4> <a href="lowongan_detail.php?lowongan_id=<?php echo $lowongan['lowongan_id']; ?>"><?= htmlspecialchars($lowongan['posisi']) ?></a></h4>
            <p class="company"><?= htmlspecialchars($lowongan['company_name']) ?></p>
            <p class="location"><?= htmlspecialchars($lowongan['jenis_lokasi']) ?></p>
            <span class="promoted" style='margin-top:0px'><?= htmlspecialchars($lowongan['jenis_pekerjaan']) ?></span>
            <p style="font-weight:bold; font-size:14px; color:#666666;">
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
        </div>
        <a href="hapus_lowongan.php?lowongan_id=<?php echo $lowongan['lowongan_id']; ?>"><i class="fas fa-trash-alt delete-icon"></i></a> <!-- Ikon sampah -->
    </li>

            <!-- Tambahkan horizontal line kecuali untuk item terakhir -->
            <?php if ($index !== array_key_last($lowonganList)): ?>
                <li class="line"><hr class="divider" /></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    </section>
</section>

        <aside class="job-seeker-guidance">
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
    </main>

</body>
</html>
