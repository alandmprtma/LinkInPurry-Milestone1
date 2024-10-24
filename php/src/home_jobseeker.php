<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])|| $_SESSION['role'] !== 'jobseeker') {
    header('Location: auth/index.html');
    exit();
}

// Konfigurasi koneksi database
$host = 'db'; // Jika menggunakan Docker
$dbname = 'linkinpurry_db';
$user = 'user';
$password = 'userpassword';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}


// Pagination
$perPage = 3; // Jumlah lowongan per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini
$offset = ($page - 1) * $perPage; // Hitung offset untuk SQL

// Tangkap nilai filter dari URL (GET request)
$jobType = isset($_GET['job_type']) ? $_GET['job_type'] : 'all';  // Default: 'all'
$locationType = isset($_GET['location_type']) ? $_GET['location_type'] : 'all';  // Default: 'all'
$sortCategory = isset($_GET['sort_category']) ? $_GET['sort_category'] : 'none';  // Default: 'none'
$sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'none';  // Default: 'none'
$searchKeyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '';

// Query dasar untuk menghitung total lowongan (tanpa batasan company_id)
$totalQuery = "SELECT COUNT(*) FROM Lowongan L where 1 = 1";

// Array untuk menampung kondisi tambahan dan parameter
$totalConditions = [];
$totalParams = [];

// Tambahkan filter berdasarkan jenis pekerjaan (jobType)
if ($jobType != 'all') {
    $totalConditions[] = "L.jenis_pekerjaan = :jobType";
    $totalParams[':jobType'] = $jobType;
}

// Tambahkan filter berdasarkan jenis lokasi (locationType)
if ($locationType != 'all') {
    $totalConditions[] = "L.jenis_lokasi = :locationType";
    $totalParams[':locationType'] = $locationType;
}

// Tambahkan filter pencarian berdasarkan keyword (searchKeyword)
if (!empty($searchKeyword)) {
    $totalConditions[] = "(L.posisi ILIKE :searchKeyword OR L.company_id IN (SELECT U.user_id FROM Users U WHERE U.nama ILIKE :searchKeyword))";
    $totalParams[':searchKeyword'] = '%' . $searchKeyword . '%';
}

// Gabungkan semua kondisi jika ada
if (!empty($totalConditions)) {
    $totalQuery .= " AND " . implode(' AND ', $totalConditions);
}

// Persiapkan query untuk menghitung total lowongan
$totalStmt = $pdo->prepare($totalQuery);
$totalStmt->execute($totalParams);
$totalLowongan = $totalStmt->fetchColumn();

// Query dasar
$query = "SELECT L.*, U.nama AS company_name FROM Lowongan L JOIN Users U ON L.company_id = U.user_id";

// Array untuk menampung kondisi tambahan dan parameter
$conditions = [];
$params = [];

// Tambahkan kondisi filter berdasarkan jobType (jenis_pekerjaan)
if ($jobType != 'all') {
    $conditions[] = "L.jenis_pekerjaan = :jobType";
    $params[':jobType'] = $jobType;
}

// Tambahkan kondisi filter berdasarkan locationType (jenis_lokasi)
if ($locationType != 'all') {
    $conditions[] = "L.jenis_lokasi = :locationType";
    $params[':locationType'] = $locationType;
}

if (!empty($searchKeyword)) {
    $conditions[] = "(L.posisi ILIKE :searchKeyword OR U.nama ILIKE :searchKeyword)";
    $params[':searchKeyword'] = '%' . $searchKeyword . '%';  // Tambahkan wildcard untuk pencarian
}

// Gabungkan semua kondisi jika ada
if (!empty($conditions)) {
    $query .= " AND " . implode(' AND ', $conditions);
}

// Tentukan field yang akan digunakan untuk sorting berdasarkan sortCategory
switch ($sortCategory) {
    case 'name':
        $orderByField = 'L.posisi';  // Mengurutkan berdasarkan nama pekerjaan
        break;
    case 'availability':
        $orderByField = 'L.is_open';  // Mengurutkan berdasarkan ketersediaan pekerjaan
        break;
    case 'recency':  // Tambahkan case untuk recency
        $orderByField = 'L.updated_at';  // Menggunakan lowongan_id untuk sorting berdasarkan urutan
        break;
    default:
        $orderByField = '';  // Jika tidak ada, biarkan kosong
        break;
}

// Tentukan arah sorting berdasarkan sortOrder (asc atau desc)
if ($orderByField && $sortOrder != 'none') {
    $query .= " ORDER BY $orderByField " . ($sortOrder == 'asc' ? 'ASC' : 'DESC');
}

// Persiapkan query
$stmt = $pdo->prepare($query);

// Jalankan query dengan parameter
$stmt->execute($params);

// Ambil hasil query
// $lowonganList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tambahkan LIMIT dan OFFSET untuk pagination
$query .= " LIMIT :perPage OFFSET :offset";
$params[':perPage'] = $perPage;
$params[':offset'] = $offset;

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$lowonganList = $stmt->fetchAll();

// Hitung total halaman
$totalPages = ceil($totalLowongan / $perPage);

// REKOMENDASI

// Ambil jenis pekerjaan yang paling sering dilamar oleh user
$queryJobType = "
    SELECT l.jenis_pekerjaan, COUNT(*) AS jumlah
    FROM Lamaran la
    JOIN Lowongan l ON la.lowongan_id = l.lowongan_id
    WHERE la.user_id = :user_id
    GROUP BY l.jenis_pekerjaan
    ORDER BY jumlah DESC
    LIMIT 1
";

$stmtJobType = $pdo->prepare($queryJobType);
$stmtJobType->execute(['user_id' => $_SESSION['user_id']]);
$jobTypeRec = $stmtJobType->fetch(PDO::FETCH_ASSOC);

// Jika ada jenis pekerjaan, rekomendasikan pekerjaan dengan jenis yang sama
if ($jobTypeRec) {
    $queryRekomendasi = "
        SELECT L.*, U.nama AS company_name
        FROM Lowongan L
        JOIN Users U ON L.company_id = U.user_id
        WHERE L.jenis_pekerjaan = :jenis_pekerjaan
        AND L.is_open = TRUE
        ORDER BY L.created_at DESC
        LIMIT 5
    ";

    $stmtRekomendasi = $pdo->prepare($queryRekomendasi);
    $stmtRekomendasi->execute(['jenis_pekerjaan' => $jobTypeRec['jenis_pekerjaan']]);
    $rekomendasiList = $stmtRekomendasi->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Jika pengguna belum melamar pekerjaan, rekomendasikan pekerjaan terbaru
    $queryRekomendasi = "
        SELECT L.*, U.nama AS company_name
        FROM Lowongan L
        JOIN Users U ON L.company_id = U.user_id
        WHERE L.is_open = TRUE
        ORDER BY L.created_at DESC
        LIMIT 5
    ";

    $stmtRekomendasi = $pdo->query($queryRekomendasi);
    $rekomendasiList = $stmtRekomendasi->fetchAll(PDO::FETCH_ASSOC);
}

// Cari lowongan trending berdasarkan jumlah pelamar dalam 7 hari terakhir
$queryTrending = "
    SELECT L.*, U.nama AS company_name, COUNT(la.lamaran_id) AS jumlah_pelamar
    FROM Lowongan L
    LEFT JOIN Lamaran la ON L.lowongan_id = la.lowongan_id
    JOIN Users U ON L.company_id = U.user_id
    WHERE la.created_at >= NOW() - INTERVAL '7 days'
    AND L.is_open = TRUE
    GROUP BY L.lowongan_id, U.nama
    ORDER BY jumlah_pelamar DESC
    LIMIT 3
";


$stmtTrending = $pdo->query($queryTrending);
$trendingList = $stmtTrending->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LinkedInPurry is a platform for job seekers to find their dream job.">
    <title>LinkInPurry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles_js.css">
</head>
<body>
    <nav class="navbar">
        <img class="logo" src="assets/LinkInPurry-crop.png" alt=".">
        
        <form method="GET" action="home_jobseeker.php" id="search-form" class="search-form">
            <div class="search-bar">
                <div class="icon">
                    <img src="assets/search-icon-removebg-preview-mirror.png" alt="Search Icon" >
                </div>
                <div class="search-bar-container">
                    <input type="text" id="search_keyword" name="search_keyword" onkeyup="handleSearchInput(event)" placeholder="Search by position or company" value="<?= isset($_GET['search_keyword']) ? htmlspecialchars($_GET['search_keyword']) : '' ?>">
                    <div id="autocomplete-results" class="autocomplete-results"></div>
                </div>
            </div>
        </form>

        <!-- Hamburger menu for mobile -->
        <div class="hamburger-menu" id="hamburger-menu">
            <i class="fas fa-bars"></i>
        </div>

        <!-- Navigation Links -->
        <ul class="nav-links" id="nav-links">
            <li><a class="current" href="/"> <img src="assets/home_black.png" alt="."> Home</a></li>
            <li><a class="inactive" href="/riwayat_lamaran.php"> <img class="job" src="assets/suitcase-grey.png" alt="."> My Jobs</a></li>
            <li><a class="inactive" href="auth/logout.php"> <img class="logout" src="assets/logout-grey.png" alt="."> Log Out</a></li>
        </ul>
    </nav>
    <main style='align-content: center;'>
    <aside class='left-aside'>
    <div class="profile-card">
        <div class="header">
        <div class="avatar">
            <img src="assets/job-seeker-rem.png" alt="Avatar">
        </div>
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
            <form method="GET" action="home_jobseeker.php">
            <div class="filter-group">
            <label for="location-type">Location Type</label>
            <select id="location-type" name="location_type">
                <option value="all">All</option>
                <option value="on-site" <?= (isset($_GET['location_type']) && $_GET['location_type'] == 'on-site') ? 'selected' : '' ?>>On-site</option>
                <option value="hybrid" <?= (isset($_GET['location_type']) && $_GET['location_type'] == 'hybrid') ? 'selected' : '' ?>>Hybrid</option>
                <option value="remote" <?= (isset($_GET['location_type']) && $_GET['location_type'] == 'remote') ? 'selected' : '' ?>>Remote</option>
            </select>
            </div>
            <div class="filter-group">
                <label for="job-type">Job Type</label>
                    <select id="job-type" name="job_type">
                        <option value="all">All</option>
                        <option value="full-time" <?= (isset($_GET['job_type']) && $_GET['job_type'] == 'full-time') ? 'selected' : '' ?>>Full-time</option>
                        <option value="part-time" <?= (isset($_GET['job_type']) && $_GET['job_type'] == 'part-time') ? 'selected' : '' ?>>Part-time</option>
                        <option value="internship" <?= (isset($_GET['job_type']) && $_GET['job_type'] == 'internship') ? 'selected' : '' ?>>Internship</option>
                    </select>
             </div>
             <div class="filter-group">
    <label for="sort-category">Sort By</label>
    <select id="sort-category" name="sort_category">
        <option value="none" <?= (isset($_GET['sort_category']) && $_GET['sort_category'] == 'none') ? 'selected' : '' ?>>None</option>
        <option value="name" <?= (isset($_GET['sort_category']) && $_GET['sort_category'] == 'name') ? 'selected' : '' ?>>Name</option>
        <option value="availability" <?= (isset($_GET['sort_category']) && $_GET['sort_category'] == 'availability') ? 'selected' : '' ?>>Availability</option>
        <option value="recency" <?= (isset($_GET['sort_category']) && $_GET['sort_category'] == 'recency') ? 'selected' : '' ?>>Recency</option>
    </select>
        </div>
            <div class="filter-group">
                <label for="sort-order">Order By</label>
                <select id="sort-order" name="sort_order">
                    <option value="none" <?= (isset($_GET['sort_order']) && $_GET['sort_order'] == 'none') ? 'selected' : '' ?>>None</option>
                    <option value="asc" <?= (isset($_GET['sort_order']) && $_GET['sort_order'] == 'asc') ? 'selected' : '' ?>>Ascending</option>
                    <option value="desc" <?= (isset($_GET['sort_order']) && $_GET['sort_order'] == 'desc') ? 'selected' : '' ?>>Descending</option>
                </select>
            </div>
        <button class="apply-filters">Apply Filters</button>
        </form>
        </aside>
    </aside>
    <section class="job-recommendations">
    <div class="header">
        <h2>Recommended Jobs for You</h2>
        <p>Based on your profile, preferences, and activity like applies, searches, and saves</p>
    </div>

    <ul class="job-cards">
        <?php if ($rekomendasiList): ?>
            <?php foreach ($rekomendasiList as $index => $rekomendasi): ?>
                <li class="job-card">
                    <h4><a href="detail_lowongan_jobseeker.php?lowongan_id=<?= htmlspecialchars($rekomendasi['lowongan_id']); ?>" class="job-link"><?= htmlspecialchars($rekomendasi['posisi']) ?></a></h4>
                    <p class="company"><?= htmlspecialchars($rekomendasi['company_name']) ?></p>
                    <p class="location"><?= htmlspecialchars($rekomendasi['jenis_lokasi']) ?></p>
                    <span class="promoted"><?= htmlspecialchars($rekomendasi['jenis_pekerjaan']) ?></span>
                    <p style="font-weight:bold; font-size:14px; color:#666666;">
                        <?php if ($rekomendasi['is_open']): ?>
                            <span>Open</span>
                        <?php else: ?>
                            <span>Closed</span>
                        <?php endif; ?>
                    </p>
                </li>
                <?php if ($index !== array_key_last($rekomendasiList)): ?>
                    <li class="line"><hr class="divider" /></li>
                 <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="cactus-placeholder">
            <p style="text-align: center;">No recommendations available at the moment.</p>
            <img src="assets/cactus.png" class="cactus"/>
            </div>
        <?php endif; ?>
    </ul>
</section>
<section>
<div class="card-header">
    <div class="card-content">
        <h2>Hi <?php echo $_SESSION['nama']; ?>, are you looking for work?</h2>
        <p>Explore new opportunities and get closer to your dream career!</p>
    </div>
    </div>
    <section class="job-listings">
    <div class="header">
        <h2>Job Listings Results</h2>
        <p>Explore job opportunities tailored to your filters and search criteria.</p>
    </div>
    <ul class="job-cards">
    <?php if (!empty($lowonganList)): ?>
        <?php foreach ($lowonganList as $index => $lowongan): ?>
            <li class="job-card">
                <!-- Tambahkan link di sekitar nama posisi -->
                <h4><a href="detail_lowongan_jobseeker.php?lowongan_id=<?= htmlspecialchars($lowongan['lowongan_id']); ?>" class="job-link"><?= htmlspecialchars($lowongan['posisi']) ?></a></h4>
                <p class="company"><?= htmlspecialchars($lowongan['company_name']) ?></p>
                <p class="location"><?= htmlspecialchars($lowongan['jenis_lokasi']) ?></p>
                <span class="promoted"><?= htmlspecialchars($lowongan['jenis_pekerjaan']) ?></span>
                <p style="font-weight:bold; font-size:14px; color:#666666;">
                    <?php if ($lowongan['is_open']): ?>
                        <span>Open</span>
                    <?php else: ?>
                        <span>Closed</span>
                    <?php endif; ?>
                </p>
            </li>

            <!-- Tambahkan horizontal line kecuali untuk item terakhir -->
            <?php if ($index !== array_key_last($lowonganList)): ?>
                <li class="line"><hr class="divider" /></li>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Jika lowonganList kosong, tampilkan pesan -->
        <div class="cactus-placeholder"> 
            <p>No Jobs Listing at the moment.</p>
            <img src="assets/cactus.png" class="cactus"/>
        </div>
    <?php endif; ?>
</ul>

    </section>
    


     <!-- Pagination -->
     <div id="pagination" class="pagination">
            <?php if ($totalPages > 1): ?>
                <?php if ($page > 1): ?>
                    <!-- Tombol << mundur 2 halaman -->
                    <a href="?page=<?= max(1, $page - 2) ?>&job_type=<?= $jobType ?>&location_type=<?= $locationType ?>&sort_category=<?= $sortCategory ?>&sort_order=<?= $sortOrder ?>&search_keyword=<?= $searchKeyword ?>">«</a>
                <?php endif; ?>

                <!-- Tombol halaman pertama -->
                <a href="?page=1&job_type=<?= $jobType ?>&location_type=<?= $locationType ?>&sort_category=<?= $sortCategory ?>&sort_order=<?= $sortOrder ?>&search_keyword=<?= $searchKeyword ?>" class="<?= $page == 1 ? 'active' : '' ?>">1</a>

                <!-- Jika halaman lebih dari 3, tampilkan ... setelah halaman 1 -->
                <?php if ($page > 3): ?>
                    <span>...</span>
                <?php endif; ?>

                <!-- Tombol halaman di sekitar halaman saat ini -->
                <?php for ($i = max(2, $page - 1); $i <= min($totalPages - 1, $page + 1); $i++): ?>
                    <a href="?page=<?= $i ?>&job_type=<?= $jobType ?>&location_type=<?= $locationType ?>&sort_category=<?= $sortCategory ?>&sort_order=<?= $sortOrder ?>&search_keyword=<?= $searchKeyword ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <!-- Jika halaman saat ini lebih dari 3 halaman sebelum halaman terakhir, tampilkan ... sebelum halaman terakhir -->
                <?php if ($page < $totalPages - 2): ?>
                    <span>...</span>
                <?php endif; ?>

                <!-- Tombol halaman terakhir -->
                <a href="?page=<?= $totalPages ?>&job_type=<?= $jobType ?>&location_type=<?= $locationType ?>&sort_category=<?= $sortCategory ?>&sort_order=<?= $sortOrder ?>&search_keyword=<?= $searchKeyword ?>" class="<?= $page == $totalPages ? 'active' : '' ?>"><?= $totalPages ?></a>

                <!-- Tombol >> lompat 2 halaman -->
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= min($totalPages, $page + 2) ?>&job_type=<?= $jobType ?>&location_type=<?= $locationType ?>&sort_category=<?= $sortCategory ?>&sort_order=<?= $sortOrder ?>>&search_keyword=<?= $searchKeyword ?>">»</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
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
                        <p style="margin-top:15px; text-align:justify;">Explore our curated guide of expert-led courses, such as how to improve your resume and grow your network, to help you land your next opportunity.</p>
                        <a href="#" class="show-more">Show more <span>&#8594;</span></a>
                    </div>
                </div>
            </div>
            <section class="job-trending">
            <div class="header">
                <h2><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" style="margin-right: 10px;" viewBox="0 0 576 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M384 160c-17.7 0-32-14.3-32-32s14.3-32 32-32l160 0c17.7 0 32 14.3 32 32l0 160c0 17.7-14.3 32-32 32s-32-14.3-32-32l0-82.7L342.6 374.6c-12.5 12.5-32.8 12.5-45.3 0L192 269.3 54.6 406.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l160-160c12.5-12.5 32.8-12.5 45.3 0L320 306.7 466.7 160 384 160z"/></svg>Trending Jobs</h2>
                <p>These jobs are trending based on recent applications</p>
            </div>

            <ul class="job-cards">
                <?php if ($trendingList): ?>
                    <?php foreach ($trendingList as $trending): ?>
                        <li class="job-card">
                            <h4><a href="detail_lowongan_jobseeker.php?lowongan_id=<?= htmlspecialchars($trending['lowongan_id']); ?>" class="job-link"><?= htmlspecialchars($trending['posisi']) ?></a></h4>
                            <p class="company"><?= htmlspecialchars($trending['company_name']) ?></p>
                            <p class="location"><?= htmlspecialchars($trending['jenis_lokasi']) ?></p>
                            <span class="promoted"><?= htmlspecialchars($trending['jenis_pekerjaan']) ?></span>
                            <p style="font-weight:bold; font-size:14px; color:#666666;">
                                <?php if ($trending['is_open']): ?>
                                    <span>Open</span>
                                <?php else: ?>
                                    <span>Closed</span>
                                <?php endif; ?>
                            </p>
                        </li>
                        <?php if ($index !== array_key_last($trendingList)): ?>
                            <li class="line"><hr class="divider" /></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="cactus-placeholder"> 
                    <p>No trending jobs at the moment.</p>
                    <img src="assets/cactus.png" class="cactus"/>
                    </div>
                <?php endif; ?>
            </ul>
        </section>
            <div class="footer-section" style="margin-top: 20px; text-align: center;">
                <img src="assets/LinkInPurry-crop.png" alt="LinkedInPurry Logo" style="height: 25px; vertical-align: middle;">
                <span style="font-size: 14px; margin-left: 8px;">
                    LinkedInPurry Corporation © 2024
                </span>
            </div>
        </aside>
    </main>


    <script src="public/autocomplete_js.js"></script>
    <script src="public/hamburgermenu.js"></script>
    <script src="public/searchdebounce.js"></script>
</body>
</html>
