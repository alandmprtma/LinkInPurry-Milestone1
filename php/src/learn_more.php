<?php
session_start();
if (!isset($_SESSION['user_id'])|| $_SESSION['role'] !== 'jobseeker') {
    header('Location: auth/index.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Career Guidance Page</title>
  <link rel="stylesheet" href="css/styles_lm.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar">
        <img class="logo" src="assets/LinkInPurry-crop.png" alt=".">
        <!-- Hamburger menu for mobile -->
        <div class="hamburger-menu" id="hamburger-menu">
            <i class="fas fa-bars"></i>
        </div>

        <!-- Navigation Links -->
        <ul class="nav-links" id="nav-links">
            <li><a class="inactive" href="/"> <img src="assets/home_grey.png" alt="."> Home</a></li>
            <li><a class="inactive" href="/riwayat_lamaran.php"> <img class="job" src="assets/suitcase-grey.png" alt="."> My Jobs</a></li>
            <li><a class="inactive" href="auth/logout.php"> <img class="logout" src="assets/logout-grey.png" alt="."> Log Out</a></li>
        </ul>
    </nav>
    <div class="top-section">
    <h1><?php echo $_SESSION['nama']; ?>, we're here to help you land your next job</h1>
    <p>Let industry experts guide you with concrete steps you can take to land your next job</p>
    </div>
  <main style="display: flex; flex-direction: row; justify-content:center;">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
      <ul>
        <li onclick="showSection('resume')" class="active">I want to improve my resume</li>
        <li onclick="showSection('linkedin')">I want to improve my LinkedIn Profile</li>
        <li onclick="showSection('network')">I want to use LinkedIn to network</li>
      </ul>
    </aside>

    <!-- Main Content -->
    <section class="main-content" id="content">
      <?php include 'resume.php'; ?>
    </section>
</main>

  <script src="public/learnmore.js"></script>
  <script src="public/hamburgermenu.js"></script>
</body>
</html>
