<?php
session_start();

// Cek apakah pengguna sudah login dan apakah role adalah 'company'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: auth/login.html');
    exit();
}

// Koneksi ke database
$host = 'db';
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

// Filter dan Sortir seperti sebelumnya
$jobType = isset($_GET['job_type']) ? $_GET['job_type'] : 'all';
$locationType = isset($_GET['location_type']) ? $_GET['location_type'] : 'all';
$sortCategory = isset($_GET['sort_category']) ? $_GET['sort_category'] : 'none';
$sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'none';
$searchKeyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '';

// Query untuk mendapatkan total lowongan
$totalQuery = "SELECT COUNT(*) FROM Lowongan L WHERE L.company_id = :company_id";

$conditions = [];
$params = [':company_id' => $_SESSION['user_id']];

// Add job type filter
if ($jobType != 'all') {
    $conditions[] = "L.jenis_pekerjaan = :jobType";
    $params[':jobType'] = $jobType;
}

// Add location type filter
if ($locationType != 'all') {
    $conditions[] = "L.jenis_lokasi = :locationType";
    $params[':locationType'] = $locationType;
}

// Add search keyword filter
if (!empty($searchKeyword)) {
    $conditions[] = "(L.posisi ILIKE :searchKeyword)";
    $params[':searchKeyword'] = '%' . $searchKeyword . '%';
}

// Add conditions to the query for total count
if (!empty($conditions)) {
    $totalQuery .= " AND " . implode(' AND ', $conditions);
}

// Execute the total query with filters
$totalStmt = $pdo->prepare($totalQuery);
$totalStmt->execute($params);
$totalLowongan = $totalStmt->fetchColumn();


// Query untuk mendapatkan daftar lowongan
$query = "SELECT L.*, U.nama AS company_name 
          FROM Lowongan L 
          JOIN Users U ON L.company_id = U.user_id 
          WHERE L.company_id = :company_id";

$conditions = [];
$params = [':company_id' => $_SESSION['user_id']];

// Add job type filter
if ($jobType != 'all') {
    $conditions[] = "L.jenis_pekerjaan = :jobType";
    $params[':jobType'] = $jobType;
}

// Add location type filter
if ($locationType != 'all') {
    $conditions[] = "L.jenis_lokasi = :locationType";
    $params[':locationType'] = $locationType;
}

// Add search keyword filter
if (!empty($searchKeyword)) {
    $conditions[] = "(L.posisi ILIKE :searchKeyword)";
    $params[':searchKeyword'] = '%' . $searchKeyword . '%';
}

// Add conditions to the query
if (!empty($conditions)) {
    $query .= " AND " . implode(' AND ', $conditions);
}

// Sorting logic
switch ($sortCategory) {
    case 'name':
        $orderByField = 'L.posisi';
        break;
    case 'availability':
        $orderByField = 'L.is_open';
        break;
    case 'recency':  // Tambahkan case untuk recency
        $orderByField = 'L.updated_at';  // Menggunakan lowongan_id untuk sorting berdasarkan urutan
        break;
    default:
        $orderByField = '';
        break;
}

if ($orderByField && $sortOrder != 'none') {
    $query .= " ORDER BY $orderByField " . ($sortOrder == 'asc' ? 'ASC' : 'DESC');
}

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
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles_js.css">
</head>
<body>
        <nav class="navbar">
            <img class="logo" src="assets/LinkInPurry-crop.png">
            <form method="GET" action="home_company.php">
            <div class="search-bar">
                <div class="icon">
                    <img src="assets\search-icon-removebg-preview-mirror.png" alt="Search Icon">
                </div>
                <div class="search-bar-container">
                <input type="hidden" id="company_id" name="company_id" value="<?php echo $_SESSION['user_id'];?>">
                <input type="text" id="search_keyword"  name="search_keyword" onkeyup="searchAutocomplete()" placeholder="Search by position or company" value="<?= isset($_GET['search_keyword']) ? htmlspecialchars($_GET['search_keyword']) : '' ?>">
                <div id="autocomplete-results" class="autocomplete-results"></div>
                </div>
            </div>
            </form>
            <div class="hamburger-menu" id="hamburger-menu">
                <i class="fas fa-bars"></i>
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
        <aside class="filters">
            <h3>Filter</h3>
            <form method="GET" action="home_company.php">
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
                    <h4><a href="lowongan_detail.php?lowongan_id=<?php echo $lowongan['lowongan_id']; ?>"><?= htmlspecialchars($lowongan['posisi']) ?></a></h4>
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
                    <!-- Jumlah applicants -->
                    <?php
                    $lowongan_id = $lowongan['lowongan_id'];
                    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_applicants FROM Lamaran WHERE lowongan_id = :lowongan_id");
                    $stmt->execute(['lowongan_id' => $lowongan_id]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $total_applicants = $result['total_applicants'];
                    ?>
                    <p style="font-size:14px; color:#666666;"> <?= htmlspecialchars($total_applicants); ?> applicants</p>
                </div>
                <a href="hapus_lowongan.php?lowongan_id=<?php echo $lowongan['lowongan_id']; ?>"><i class="fas fa-trash-alt delete-icon"></i></a>
            </li>
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
                        <p style="margin-top:15px; text-align:justify;">Ready to expand your team? Posting a job has never been easier! Share your job listing with a wide audience of qualified candidates looking for opportunities just like yours. With our user-friendly platform, you can customize your job post to attract the best talent, all at no cost to you!</p>
                        <a href="buat_lowongan.php" class="show-more">Start Posting Now <span>&#8594;</span></a>
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
    <script src="public/autocomplete_h.js"></script>
</body>
</html>

<script>
const hamburgerMenu = document.getElementById('hamburger-menu');
const navLinks = document.querySelector('.nav-links');

hamburgerMenu.addEventListener('click', () => {
    navLinks.classList.toggle('active'); // Toggle class untuk menampilkan atau menyembunyikan nav links
});

</script>