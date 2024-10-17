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
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">
    <h1>Apply for Job</h1>
    <form action="apply.php?lowongan_id=<?= htmlspecialchars($lowongan_id); ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="cv">Upload CV (PDF only):</label>
            <input type="file" name="cv" id="cv" accept=".pdf" required>
        </div>

        <div class="form-group">
            <label for="video">Upload Introduction Video (Optional, MP4 only):</label>
            <input type="file" name="video" id="video" accept="video/mp4">
        </div>

        <button type="submit" class="btn">Submit Application</button>
    </form>

    <a href="detail_lowongan_jobseeker.php?lowongan_id=<?= htmlspecialchars($lowongan_id); ?>" class="btn">Back to Job Details</a>
</div>

</body>
</html>
