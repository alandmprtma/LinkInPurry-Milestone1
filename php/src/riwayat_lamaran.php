<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.html');
    exit();
}

// Gatekeep for jobseekers only
if ($_SESSION['role'] == 'company') {
    header('Location: home_company.php');
    exit();
}
else if ($_SESSION['role'] != 'jobseeker') {
    header('Location: auth/login.html');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET"){
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

    if (isset($_GET['data'])){
        $query ="WITH deskripsilowongan AS (SELECT lowongan_id, company_id, posisi FROM lowongan),
                namelist AS (SELECT user_id, nama FROM users)
                SELECT la.lowongan_id, n.nama AS company_name, dl.posisi, la.status, la.created_at
                FROM lamaran AS la
                INNER JOIN deskripsilowongan AS dl ON la.lowongan_id = dl.lowongan_id
                INNER JOIN namelist AS n ON dl.company_id = n.user_id
                WHERE la.user_id = :active_user";

        $vars = ['active_user' => $_SESSION['user_id']];

        if (isset($_GET['filter'])) {
            $query .= " AND la.status = :filter";
            $vars['filter'] = $_GET['filter'];
        } 

        if (isset($_GET['limit'])) {
            $query .= " LIMIT :limit";
            $vars['limit'] = $_GET['limit'];
        }

        if (isset($_GET['skip'])) {
            $query .= " OFFSET :skip";
            $vars['skip'] = $_GET['skip'];
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($vars);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    }
    else if (isset($_GET['count'])){
        $query ="WITH deskripsilowongan AS (SELECT lowongan_id, company_id, posisi FROM lowongan),
                namelist AS (SELECT user_id, nama FROM users)
                SELECT COUNT(*)
                FROM lamaran AS la
                INNER JOIN deskripsilowongan AS dl ON la.lowongan_id = dl.lowongan_id
                INNER JOIN namelist AS n ON dl.company_id = n.user_id
                WHERE la.user_id = :active_user";

        $vars = ['active_user' => $_SESSION['user_id']];

        if (isset($_GET['filter'])) {
            $query .= " AND la.status = :filter";
            $vars['filter'] = $_GET['filter'];
        } 

        $stmt = $pdo->prepare($query);
        $stmt->execute($vars);
        $count = $stmt->fetchColumn();
        echo $count;
        exit();
    }
    else {
        echo isset($_GET['data']);
        echo isset($_GET['count']);
    }
}
else{
    echo $_SERVER["REQUEST_METHOD"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Company</title>
    <link rel="stylesheet" href="../css/riwayat_lamaran.css">
    <link rel="stylesheet" href="../css/styles_js.css"> <!-- Menggunakan CSS global -->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.6.0/css/all.css">
</head>
<body>

<nav class="navbar">
            <img class="logo" src="assets/LinkInPurry-crop.png">
            <form method="GET" action="home_company.php">
            </form>
            <div class="hamburger-menu" id="hamburger-menu">
                <i class="fas fa-bars"></i>
           </div>
            <ul class="nav-links">
                <li><a class="inactive" href="/"> <img class="home" src="assets/home_grey.png"> Home</a></li>
                <li><a class="current" href="/riwayat_lamaran.php"> <img class="job" src="assets/suitcase-grey.png"> My Jobs</a></li>
                <li><a class="inactive" href="auth/logout.php"> <img class="logout" src="assets/logout-grey.png"> Log Out</a></li>
            </ul>
</nav>

<main class="riwayat-container">
    <div class="container">
        <p>Filter by status</p>
        <ul>
            <ul class="button_list">
                <li><button class="toggle_button" onclick="filter(this, 'accepted')">Accepted</button></li>
                <li><button class="toggle_button" onclick="filter(this, 'waiting')">Waiting</button></li>
                <li><button class="toggle_button" onclick="filter(this, 'rejected')">Rejected</button></li>
            </ul>
        </ul>
    </div>
    <p><strong>Riwayat lamaran:</strong></p>
    <div class="expand">
        <div class="container lamaran-container" id="lamaran-container">
            <a class="lamaran" href="#">            
                <span class="corpname">
                    <b>BCorp</b>Job name
                </span>
                <span class="status rejected"> <b> Rejected </b> </span>
            </a>
        </div>
        <span class="pagination-container">
            <button class="riwayat_pagination_button" id="leftbttn" onclick="pageoffset(-1);" hidden><i class="fa-solid fa-angle-left"></i></button>
            <input id="pageno" type="text"> <label> of <b id="lamar_count"></b></label>
            <button class="riwayat_pagination_button" id="rightbttn" onclick="pageoffset(1);" hidden><i class="fa-solid fa-angle-right"></i></button>
        </span>
    </div>
</main>

</body>

<script src="../public/riwayat_lamaran.js"></script>

</html>