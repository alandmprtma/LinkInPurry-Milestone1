<?php
session_start();

// Cek apakah pengguna sudah login dan role-nya adalah 'jobseeker'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
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

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proses upload file
    $cv_path = '';
    $video_path = '';

    // Cek apakah file CV diunggah
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $cv_ext = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
        $cv_filename = uniqid() . '.' . $cv_ext;
        $cv_path = 'uploads/cv/' . $cv_filename;
        move_uploaded_file($_FILES['cv']['tmp_name'], $cv_path);
    }

    // Cek apakah file video perkenalan diunggah (opsional)
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $video_ext = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
        $video_filename = uniqid() . '.' . $video_ext;
        $video_path = 'uploads/videos/' . $video_filename;
        move_uploaded_file($_FILES['video']['tmp_name'], $video_path);
    }

    // Simpan lamaran ke database, status diset otomatis menjadi 'waiting' melalui ENUM application_status
    $query = "INSERT INTO Lamaran (user_id, lowongan_id, cv_path, video_path, created_at)
              VALUES (:user_id, :lowongan_id, :cv_path, :video_path, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'lowongan_id' => $lowongan_id,
        'cv_path' => $cv_path,
        'video_path' => $video_path
    ]);

    // Redirect ke halaman detail lowongan setelah berhasil melamar
    header("Location: detail_lowongan_jobseeker.php?lowongan_id=$lowongan_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job</title>
    <link rel="stylesheet" href="css/styles_a.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<script src="public/dropzone.js"></script>
<body>

<nav class="navbar">
    <img class="logo" src="assets/LinkInPurry-crop.png">
    <ul class="nav-links">
        <li><a class="inactive" href="/"> <img src="assets/home_black.png"> Home</a></li>
        <li><a class="inactive" href="/riwayat_lamaran.php"> <img class="job" src="assets/suitcase-grey.png"> My Jobs</a></li>
        <li><a class="inactive" href="auth/logout.php"> <img class="logout" src="assets/logout-grey.png"> Log Out</a></li>
    </ul>
</nav>

<div class="container">
    <div class="apply-job-js">
    <h1>Apply for Job</h1>
    <form class="form-apply" action="apply.php?lowongan_id=<?= htmlspecialchars($lowongan_id); ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="cv">Upload CV (PDF only):</label>
            <div id="cv-drop-area" class="drop-area">
                <p>Drag & Drop your CV here or click to upload</p>
                <input type="file" name="cv" id="cv" accept=".pdf" required hidden>
            </div>
        </div>

        <div class="form-group">
            <label for="video">Upload Introduction Video (Optional, MP4 only):</label>
            <div id="video-drop-area" class="drop-area">
                <p>Drag & Drop your video here or click to upload</p>
                <input type="file" name="video" id="video" accept="video/mp4" hidden>
            </div>
        </div>
        <button class="apply-button" type="submit" class="btn">Submit Application</button>
    </form>

    
</div>

</body>
</html>
