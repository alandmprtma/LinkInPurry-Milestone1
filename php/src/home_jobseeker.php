<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.html');
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

// Tangkap nilai filter dari URL (GET request)
$jobType = isset($_GET['job_type']) ? $_GET['job_type'] : 'all';  // Default: 'all'
$locationType = isset($_GET['location_type']) ? $_GET['location_type'] : 'all';  // Default: 'all'
$sortCategory = isset($_GET['sort_category']) ? $_GET['sort_category'] : 'none';  // Default: 'none'
$sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'none';  // Default: 'none'
$searchKeyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '';

// Query dasar
$query = "SELECT L.*, U.nama AS company_name FROM Lowongan L JOIN Users U ON L.company_id = U.user_id WHERE L.is_open = TRUE";

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
$lowonganList = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <form method="GET" action="home_jobseeker.php">
            <div class="search-bar">
                    <div class="icon">
                        <img src="assets/search-icon-removebg-preview-mirror.png" alt="Search Icon">
                    </div>
                    <input type="text" name="search_keyword" id="search_keyword" placeholder="Search by position or company" onkeyup="searchAutocomplete()" autocomplete="off">
                    <div id="autocomplete-results" class="autocomplete-results"></div> <!-- Container untuk hasil autocomplete -->
                </div>
            </form>
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
        <h2>Hi <?php echo $_SESSION['nama']; ?>, are you looking for work?</h2>
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

<script>
function searchAutocomplete() {
    const query = document.getElementById('search_keyword').value;

    if (query.length > 2) {  // Mulai pencarian jika panjang input > 2 karakter
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "autocomplete_search.php?query=" + query, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const results = JSON.parse(xhr.responseText);
                let autocompleteResults = document.getElementById('autocomplete-results');
                autocompleteResults.innerHTML = '';  // Kosongkan hasil sebelumnya

                // Tampilkan maksimal 3 hasil
                results.forEach(result => {
                    const item = document.createElement('div');
                    item.classList.add('autocomplete-item');
                    item.innerHTML = `<a href="detail_lowongan_jobseeker.php?lowongan_id=${result.lowongan_id}">
                        <strong>${result.posisi}</strong> at ${result.company_name}
                    </a>`;
                    autocompleteResults.appendChild(item);
                });
            }
        };
        xhr.send();
    } else {
        document.getElementById('autocomplete-results').innerHTML = '';  // Kosongkan hasil jika input kurang dari 3 karakter
    }
}
</script>