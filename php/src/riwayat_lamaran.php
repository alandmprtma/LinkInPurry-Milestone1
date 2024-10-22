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

if ($_SERVER["REQUEST_METHOD"] == "POST"){
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

    if (!isset($_POST['count'])){
        // Query untuk mendapatkan detail lamaran
        $query ="WITH deskripsilowongan AS (SELECT lowongan_id, company_id, posisi FROM lowongan),
        namelist AS (SELECT user_id, nama FROM users)
        SELECT ll.lamaran_id ,n.nama AS company_name, ll.posisi, ll.status, ll.created_at
        FROM (lamaran AS la INNER JOIN deskripsilowongan AS dl ON la.lowongan_id = dl.lowongan_id) AS ll
        INNER JOIN namelist as n ON ll.company_id = n.user_id
        WHERE ll.user_id = :active_user";

        $vars = ['active_user' => $_SESSION['user_id']];

        if (isset($_POST['filter'])) {
            $query .= " AND ll.status = :filter";
            $vars['filter'] = $_POST['filter'];
        } 

        if (isset($_POST['limit'])) {
            $query .= " LIMIT :limit";
            $vars['limit'] = $_POST['limit'];
        }

        if (isset($_POST['skip'])) {
            $query .= " OFFSET :skip";
            $vars['skip'] = $_POST['skip'];
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($vars);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    }
    else{
        $query ="WITH deskripsilowongan AS (SELECT lowongan_id, company_id, posisi FROM lowongan),
        namelist AS (SELECT user_id, nama FROM users)
        SELECT COUNT(*) AS count
        FROM (lamaran AS la INNER JOIN deskripsilowongan AS dl ON la.lowongan_id = dl.lowongan_id) AS ll
        INNER JOIN namelist as n ON ll.company_id = n.user_id
        WHERE ll.user_id = :active_user";

        $vars = ['active_user' => $_SESSION['user_id']];

        if (isset($_POST['filter'])) {
            $query .= " AND ll.status = :filter";
            $vars['filter'] = $_POST['filter'];
        } 

        $stmt = $pdo->prepare($query);
        $stmt->execute($vars);
        $count = $stmt->fetchColumn();
        echo $count;
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Company</title>
    <link rel="stylesheet" href="../css/riwayat_lamaran.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.6.0/css/all.css">
</head>
<body>
<div class="">
    <div class="container">
        <ul>
            <ul class="button_list">
                <li><button class="toggle_button" onclick="filter(this, 'accepted')">Accepted</button></li>
                <li><button class="toggle_button" onclick="filter(this, 'waiting')">Waiting</button></li>
                <li><button class="toggle_button" onclick="filter(this, 'rejected')">Rejected</button></li>
            </ul>
        </ul>
    </div>
    <p><strong>Riwayat lamaran:</strong></p>
    <div>
        <div class="container lamaran-container" id="lamaran-container">
            <a class="lamaran" href="#">            
                <span class="corpname">
                    <b>BCorp</b>Job name
                </span>
                <span class="status rejected"> <b> Rejected </b> </span>
            </a>
        </div>
        <span>
            <button class="riwayat_pagination_button" id="leftbttn" onclick="pageoffset(-1);" hidden><i class="fa-solid fa-angle-left"></i></button>
            <input id="pageno" type="text"> <label> of <b id="lamar_count"></b></label>
            <button class="riwayat_pagination_button" id="rightbttn" onclick="pageoffset(1);" hidden><i class="fa-solid fa-angle-right"></i></button>
        </span>
    </div>
</div>

</body>

<script src="../public/riwayat_lamaran.js"></script>

</html>