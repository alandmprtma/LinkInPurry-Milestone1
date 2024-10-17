<?php
session_start();

// Cek apakah pengguna sudah login dan apakah role adalah 'company'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: auth/login.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Lowongan Baru</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Menggunakan CSS global -->
</head>
<body>
    
<div class="container lowongan-container">
    <h1 class="lowongan-heading">Buat Lowongan Baru</h1>
    <form class="lowongan-form" action="simpan_lowongan.php" method="POST">
        <div class="lowongan-form-group">
            <label for="posisi" class="lowongan-label">Posisi Pekerjaan:</label>
            <input type="text" id="posisi" name="posisi" class="lowongan-input" required>
        </div>
        
        <div class="lowongan-form-group">
            <label for="deskripsi" class="lowongan-label">Deskripsi Pekerjaan:</label>
            <textarea id="deskripsi" name="deskripsi" class="lowongan-textarea" required></textarea>
        </div>

        <div class="lowongan-form-group">
            <label for="jenis_pekerjaan" class="lowongan-label">Jenis Pekerjaan:</label>
            <select id="jenis_pekerjaan" name="jenis_pekerjaan" class="lowongan-select" required>
                <option value="full-time">Full-time</option>
                <option value="part-time">Part-time</option>
                <option value="internship">Internship</option>
            </select>
        </div>

        <div class="lowongan-form-group">
            <label for="jenis_lokasi" class="lowongan-label">Jenis Lokasi:</label>
            <select id="jenis_lokasi" name="jenis_lokasi" class="lowongan-select" required>
                <option value="on-site">On-site</option>
                <option value="remote">Remote</option>
                <option value="hybrid">Hybrid</option>
            </select>
        </div>

        <button type="submit" class="btn">Simpan</button>
    </form>
    <a href="home_company.php" class="btn">Kembali</a>
</div>



</body>
</html>
