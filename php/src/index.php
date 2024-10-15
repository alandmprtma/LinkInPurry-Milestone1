<?php
$host = 'db'; // Nama service database di docker-compose.yml
$dbname = 'linkinpurry_db';
$user = 'user';
$password = 'userpassword';

$dsn = "pgsql:host=$host;port=5432;dbname=$dbname;user=$user;password=$password";
try {
    // Create a PostgreSQL database connection
    $conn = new PDO($dsn);
    
    // Display a message if connected successfully
    if($conn) {
        echo "Connected to the PostgreSQL database successfully!";
    }
} catch (PDOException $e) {
    echo $e->getMessage();
}
?>
