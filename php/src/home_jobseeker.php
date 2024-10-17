<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.html');
    exit();
}

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

$query = "SELECT L.*, U.nama AS company_name FROM Lowongan L JOIN Users U ON L.company_id = U.user_id WHERE L.is_open = TRUE";
$stmt = $pdo->prepare($query);
$stmt->execute();
$lowonganList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Menampilkan UI untuk job seeker
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkInPurry</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
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
                <li><a class="active" href="/"> <img src="assets/home_black.png"> Home</a></li>
                <li><a class="inactive" href="/jobs"> <img src="assets/suitcase-grey.png"> My Jobs</a></li>
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
            <span>Job Seeker</span>
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
<section>
<div class="card-header">
    <div class="card-content">
        <h2>Hi Aland Mulia, are you looking for work?</h2>
        <p>Explore new opportunities and get closer to your dream career!</p>
    </div>
    </div>
    <section class="job-listings">
    <div class="header">
        <h2>Top job picks for you</h2>
        <p>Based on your profile, preferences, and activity like applies, searches, and saves</p>
    </div>

    <ul class="job-cards">
        <?php foreach ($lowonganList as $index => $lowongan): ?>
            <li class="job-card">
                <h4><?= htmlspecialchars($lowongan['posisi']) ?></h4>
                <p class="company"><?= htmlspecialchars($lowongan['company_name']) ?></p>
                <p class="location"><?= htmlspecialchars($lowongan['jenis_lokasi']) ?></p>
                <span class="promoted"><?= htmlspecialchars($lowongan['jenis_pekerjaan']) ?></span>
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
                <h3>Job seeker guidance</h3>
                <p class="recommendation">Recommended based on your activity</p>
                <div class="guidance-content">
                    <div class="guidance-text">
                        <div class="guidance-headline">
                            <strong style="margin-top:5px;">I want to improve my resume</strong>
                            <div class="guidance-image">
                                <img class="" src="assets/resume.png" alt="Resume Improvement">
                            </div>
                        </div>
                        <p style="margin-top:15px;">Explore our curated guide of expert-led courses, such as how to improve your resume and grow your network, to help you land your next opportunity.</p>
                        <a href="#" class="show-more">Show more <span>&#8594;</span></a>
                    </div>
                </div>
            </div>
        </aside>
    </main>

</body>
</html>
