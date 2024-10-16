<?php
session_start();

// Cek apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
    // Jika pengguna sudah login, arahkan ke halaman home sesuai role
    if ($_SESSION['role'] == 'company') {
        header('Location: home_company.php');
    } else {
        header('Location: home_jobseeker.php');
    }
} else {
    // Jika belum login, arahkan ke halaman login satu kali
    header('Location: auth/login.html');
    exit();  // Penting: Tambahkan exit setelah header untuk menghentikan script
}
?>
