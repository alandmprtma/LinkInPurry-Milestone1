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

// Gatekeep for jobseekers only
if ($_SESSION['role'] == "company") {
    header('Location: home_company.php');
    exit();
}
else if ($_SESSION['role'] != "jobseeker") {
    header('Location: auth/login.html');
    exit();
}

// ada kemungkinan lowongan udah ilang, jadi harus left join [yang gak mungkin hilang] left join [yang mungkin hilang]
// NOTE INI OVERENGINEERED TERNYATA
/*
$query ="WITH deskripsilowongan AS (SELECT lowongan_id, company_id, posisi FROM lowongan),
              namelist AS (SELECT user_id, nama FROM users)
         SELECT n.nama AS company_name, ll.posisi, ll.status, ll.created_at
         FROM (lamaran AS la LEFT JOIN deskripsilowongan AS dl ON la.lowongan_id = dl.lowongan_id) AS ll
	        LEFT JOIN namelist as n ON ll.company_id = n.user_id
         WHERE ll.user_id = :active_user";
*/


// Query untuk mendapatkan detail lamaran

$query ="WITH deskripsilowongan AS (SELECT lowongan_id, company_id, posisi FROM lowongan),
              namelist AS (SELECT user_id, nama FROM users)
         SELECT n.nama AS company_name, ll.posisi, ll.status, ll.created_at
         FROM (lamaran AS la INNER JOIN deskripsilowongan AS dl ON la.lowongan_id = dl.lowongan_id) AS ll
	        INNER JOIN namelist as n ON ll.company_id = n.user_id
         WHERE ll.user_id = :active_user";
$stmt = $pdo->prepare($query);
$stmt->execute(['active_user' => $_SESSION['user_id']]);
$lamaran_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
