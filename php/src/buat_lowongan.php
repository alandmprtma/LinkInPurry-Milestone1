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

<div class="container">
    <h1>Buat Lowongan Baru</h1>
    <form action="simpan_lowongan.php" method="POST">
        <label for="posisi">Posisi Pekerjaan:</label>
        <input type="text" id="posisi" name="posisi" required>

        <label for="deskripsi">Deskripsi Pekerjaan:</label>
        <textarea id="deskripsi" name="deskripsi" required></textarea>

        <label for="jenis_pekerjaan">Jenis Pekerjaan:</label>
        <select id="jenis_pekerjaan" name="jenis_pekerjaan" required>
            <option value="full-time">Full-time</option>
            <option value="part-time">Part-time</option>
            <option value="internship">Internship</option>
        </select>

        <label for="jenis_lokasi">Jenis Lokasi:</label>
        <select id="jenis_lokasi" name="jenis_lokasi" required>
            <option value="on-site">On-site</option>
            <option value="remote">Remote</option>
            <option value="hybrid">Hybrid</option>
        </select>

        <button type="submit" class="btn">Simpan Lowongan</button>
    </form>
    <a href="home_company.php" class="btn">Kembali ke Home</a>
</div>

</body>
</html>
