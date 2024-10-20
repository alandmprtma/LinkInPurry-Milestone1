<?php
// Start session
session_start();

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

if (isset($_GET['query'])) {
    $searchKeyword = $_GET['query'];

    // Check session user and role
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
        header('Location: auth/login.html');
        exit(); // Make sure to exit after redirection
    }
    
    $company_id = $_SESSION['user_id'];

    // Query untuk mencari posisi atau nama perusahaan
    $query = "SELECT L.lowongan_id, L.posisi, U.nama AS company_name 
              FROM Lowongan L 
              JOIN Users U ON L.company_id = U.user_id 
              WHERE (L.posisi ILIKE :searchKeyword OR U.nama ILIKE :searchKeyword) 
              AND L.company_id = :company_id
              LIMIT 3";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':searchKeyword' => '%' . $searchKeyword . '%',
        ':company_id' => $company_id // Bind the company_id here
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set Content-Type header
    header('Content-Type: application/json'); // Add this line before outputting JSON

    // Mengirim hasil pencarian sebagai JSON
    echo json_encode($results);
}
?>
