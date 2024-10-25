<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
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

// handle PUT request
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    //only authenticate if the same user
    //otherwise fuck off
    $updatedata = file_get_contents("php://input");
    $data = json_decode($updatedata, true);
    $query = "";
    $vars = [
        'activeuser' => $_SESSION['user_id']
    ];
    $retval = array("success"=>FALSE,"reason"=>"");

    if (isset($data["location"])){
        $query .= "UPDATE companydetail SET lokasi = :lokasi
                   WHERE user_id = :activeuser";
        $vars['lokasi'] = $data["location"];
    }
    else if (isset($data["about"])){
        $query .= "UPDATE companydetail SET about = :about
                   WHERE user_id = :activeuser";
        $vars['about'] = $data["about"];
    }
    else{
        $retval["reason"] = "Invalid PUT parameters";
        echo $retval;
        exit();
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($vars);
    $rowcount = $stmt->rowCount();

    if ($rowcount > 0){
        $retval['success'] = TRUE;
    }

    echo json_encode($retval);
    exit();
}
else if ($_SERVER['REQUEST_METHOD'] == 'GET'){
    // Toss the mf user back if he inputted nothing lmao
    // Cek apakah pengguna sudah login

    if (!isset($_GET['user_id'])) {
        if ($_SESSION['role'] == "company") {
            header('Location: home_company.php');
        }
        else if ($_SESSION['role'] == "jobseeker") {
            header('Location: home_jobseeker.php');
        }
        else {
            header('Location: auth/login.html');
        }
        exit();
    }
    // Get profile id
    $wanted_id = $_GET['user_id'];

    // Query untuk mendapatkan detail lamaran
    $query ="SELECT u.nama as nama, c.*
            FROM users AS u, companydetail AS c
            WHERE u.user_id = :wanted_user AND u.user_id = c.user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['wanted_user' => $wanted_id]);
    $profile = $stmt->fetch();

    if (!$profile) {
        echo "<p>Profile perusahaan tidak ditemukan.</p>";
        exit();
    }

    
    //$success = isset($_GET["success"]) ? '<div class="yipee"><h2>Update success!</h2></div>' : '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Company</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Menggunakan CSS global -->
    <link rel="stylesheet" href="css/styles_js.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.6.0/css/all.css">
    <link rel="stylesheet" href="../css/styles_p.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    
</head>
<body>
<nav class="navbar" role="navigation">
            <img class="logo" src="assets/LinkInPurry-crop.png" alt="LinkInPurry Logo">
            <div class="hamburger-menu" id="hamburger-menu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="24" height="24">
                <path d="M0 96C0 78.3 14.3 64 32 64l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 128C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 288c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32L32 448c-17.7 0-32-14.3-32-32s14.3-32 32-32l384 0c17.7 0 32 14.3 32 32z"></path>
            </svg>
           </div>
            <ul class="nav-links" id="nav-links">
                <li><a class="inactive" href="/"> <img class="home" src="assets/home_grey.png" alt="Home"> Home</a></li>
                <li><a class="inactive" href="auth/logout.php"> <img class="logout" src="assets/logout-grey.png" alt="Log Out"> Log Out</a></li>
            </ul>
        </nav>

    <div class="box shadowed">
        <div class="nameplate">
        <!-- <img style="width: 10dvw; height: 10dvw; transform: translateY(90%);" src="../test.png"> -->
        </div>
        <div class="text-content" style="padding: 5%;">
            <h1 id="nama"><?php echo $profile["nama"]?></h1>
            <span class="location-span">
                <i class="fa-solid fa-location-dot"></i>
                <div id="location"><?php echo $profile["lokasi"]?></div>
                <div id="location-edit" hidden>
                    <input type="text" id="location-edit-box">
                </div>
                <i class="fa-solid fa-pencil fa-sm" onclick="EditLocationToggle()"></i>
            </span>
            

        </div>
    </div>
    <div class="box shadowed">
        <div class="text-content" style="padding: 5%;">
            <h1>About <i onclick="EditAboutToggle()" class="fa-solid fa-pencil fa-sm"></i></h1>
            <div id="about"><?php echo $profile["about"]?></div>
            <div id="about-edit" hidden>
                <!-- editor -->
                <div id="editor" style="height: 200px;"><?php echo $profile["about"]?></div>
                <!-- Include the Quill library -->
                <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
                <!-- Initialize Quill editor -->
                <script>
                    const quill = new Quill('#editor', {
                    theme: 'snow'
                    });
                </script>
                <span class="profile_editor_div">
                    <button onclick="EditAboutToggle()" id="profile_editor_button about-save" class="">Cancel</button>
                    <button onclick="updateInfo('about')" id="profile_editor_button about-save" class="">Save</button>
                </span>
            </div>
        </div>
    </div>
    <div class="deadspace" style="height: 5%;">
        <hr>
    </div>
</body>
<script src="public/profile.js"></script>
<script src="public/hamburgermenu.js"></script>
</html>