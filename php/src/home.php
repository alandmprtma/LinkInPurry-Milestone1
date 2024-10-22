<?php
session_start();

// Cek apakah pengguna sudah login
// Redirect jika pengguna memiliki role 'jobseeker'
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'jobseeker') {
    header('Location: home_jobseeker.php');
    exit();
}

// Redirect jika pengguna memiliki role 'company'
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'company') {
    header('Location: home_company.php');
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
        $orderByField = 'L.lowongan_id';  // Menggunakan lowongan_id untuk sorting berdasarkan urutan
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkInPurry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles_js.css">
</head>
<body>
    <nav class="navbar">
        <img class="logo" src="assets/LinkInPurry-crop.png">
        
        <form method="GET" action="home.php" class="search-form">
            <div class="search-bar">
                <div class="icon">
                    <img src="assets/search-icon-removebg-preview-mirror.png" alt="Search Icon">
                </div>
                <div class="search-bar-container">
                    <input type="text" id="search_keyword" name="search_keyword" onkeyup="searchAutocomplete()" placeholder="Search by position or company" value="<?= isset($_GET['search_keyword']) ? htmlspecialchars($_GET['search_keyword']) : '' ?>">
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
            <li><a class="current" href="/"> <img src="assets/home_black.png"> Home</a></li>
            <li>
        <a class="inactive" href="/auth/register.html">
            <i class="fa fa-user-plus"></i> Register
        </a>
        </li>
        <li>
        <a class="inactive" href="auth/login.html">
            <i class="fas fa-sign-in-alt"></i> Log In
        </a>
        </li>
        </ul>
    </nav>
    <main style='align-content: center;'>
    <aside class='left-aside'>
    <div class="profile-card">
        <div class="header">
        <div class="avatar">
            <img src="assets/incognito.png" alt="Avatar">
        </div>
        </div>
        <div class="body">
            <h3>Unauthorized User</h3>
            <p>Email not available</p>
            <p class="location">Location unknown</p>
        </div>
        <div class="footer">
            <span>Incognito Mode</span>
        </div>
    </div>
        <aside class="filters">
            <h3>Filter</h3>
            <form method="GET" action="home.php">
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
<section>
<div class="card-header">
    <div class="card-content">
        <h2>Hi there, are you hiring / looking for work?</h2>
        <p>Discover exciting opportunities, connect with potential employers, and take a step towards your ideal career!</p>
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
    </ul>


    </section>
     <!-- Pagination -->
     <div id="pagination" class="pagination">
            <?php if ($totalPages > 1): ?>
                <?php if ($page > 1): ?>
                    <!-- Tombol << mundur 2 halaman -->
                    <a href="?page=<?= max(1, $page - 2) ?>&job_type=<?= $jobType ?>&location_type=<?= $locationType ?>&sort_category=<?= $sortCategory ?>&sort_order=<?= $sortOrder ?>">«</a>
                <?php endif; ?>

                <!-- Tombol halaman pertama -->
                <a href="?page=1&job_type=<?= $jobType ?>&location_type=<?= $locationType ?>&sort_category=<?= $sortCategory ?>&sort_order=<?= $sortOrder ?>" class="<?= $page == 1 ? 'active' : '' ?>">1</a>

                <!-- Jika halaman lebih dari 3, tampilkan ... setelah halaman 1 -->
                <?php if ($page > 3): ?>
                    <span>...</span>
                <?php endif; ?>

                <!-- Tombol halaman di sekitar halaman saat ini -->
                <?php for ($i = max(2, $page - 1); $i <= min($totalPages - 1, $page + 1); $i++): ?>
                    <a href="?page=<?= $i ?>&job_type=<?= $jobType ?>&location_type=<?= $locationType ?>&sort_category=<?= $sortCategory ?>&sort_order=<?= $sortOrder ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <!-- Jika halaman saat ini lebih dari 3 halaman sebelum halaman terakhir, tampilkan ... sebelum halaman terakhir -->
                <?php if ($page < $totalPages - 2): ?>
                    <span>...</span>
                <?php endif; ?>

                <!-- Tombol halaman terakhir -->
                <a href="?page=<?= $totalPages ?>&job_type=<?= $jobType ?>&location_type=<?= $locationType ?>&sort_category=<?= $sortCategory ?>&sort_order=<?= $sortOrder ?>" class="<?= $page == $totalPages ? 'active' : '' ?>"><?= $totalPages ?></a>

                <!-- Tombol >> lompat 2 halaman -->
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= min($totalPages, $page + 2) ?>&job_type=<?= $jobType ?>&location_type=<?= $locationType ?>&sort_category=<?= $sortCategory ?>&sort_order=<?= $sortOrder ?>">»</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
</section>

        <aside class="job-seeker-guidance">
        <div class="guidance-card">
        <h3>Join Our Community</h3>
        <p class="recommendation">Unlock new opportunities</p>
            <div class="guidance-content">
                <div class="guidance-text">
                    <div class="guidance-headline">
                        <strong style="margin-top:5px;">Ready to take your career to the next level?</strong>
                        <div class="guidance-image">
                            <img class="" src="assets/resume.png" alt="Career Guidance">
                        </div>
                    </div>
                    <p style="margin-top:15px; text-align:justify;">
                        Sign up today to access tailored job recommendations, expert resources to improve your resume, and valuable networking opportunities. Whether you're a job seeker looking to land your dream role or a company seeking top talent, our platform connects you with the right resources.
                    </p>
                    <a href="auth/register.html" class="show-more">Register Now <span>&#8594;</span></a>
                </div>
            </div>
        </div>
            <div class="footer-section" style="margin-top: 20px; text-align: center;">
                <img src="assets/LinkInPurry-crop.png" alt="LinkedInPurry Logo" style="height: 25px; vertical-align: middle;">
                <span style="font-size: 14px; margin-left: 8px;">
                    LinkedInPurry Corporation © 2024
                </span>
            </div>
        </aside>
    </main>


    <script src="public/autocomplete_js.js"></script>
</body>
</html>

<script>
    document.getElementById('hamburger-menu').addEventListener('click', function() {
        const navLinks = document.getElementById('nav-links');
        navLinks.classList.toggle('active');
    });
</script>