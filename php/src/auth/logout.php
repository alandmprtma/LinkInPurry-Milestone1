<?php
session_start();
session_unset();  // Menghapus semua session
session_destroy(); // Menghancurkan session

// Redirect ke halaman login
header('Location: login.html');
exit();
?>
