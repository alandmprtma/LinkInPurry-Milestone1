<?php
session_start();

// Cek apakah pengguna sudah login dan apakah role adalah 'company'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: auth/index.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Lowongan Baru</title>
    <link rel="stylesheet" href="css/styles_bl.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/quill/2.0.2/quill.snow.css" integrity="sha512-ggYQiYwuFFyThzEv6Eo6g/uPLis4oUynsE88ovEde5b2swycOh9SlAI8FL/cL2AkGGNnWADPXcX2UnPIJS2ozw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<script src="public/dropzone-image.js"></script>
<script src="public/quil-text.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/2.0.2/quill.min.js" ></script>


<body>

<nav class="navbar">
    <img class="logo" src="assets/LinkInPurry-crop.png">
    <div class="hamburger-menu" id="hamburger-menu">
        <i class="fas fa-bars"></i>
    </div>
    <ul class="nav-links" id="nav-links">
        <li><a class="inactive" href="/"> <img class="home" src="assets/home_grey.png"> Home</a></li>
        <li><a class="inactive" href="auth/logout.php"> <img class="logout" src="assets/logout-grey.png"> Log Out</a></li>
    </ul>
</nav>
<main style='align-content: center;'>
<aside class='left-aside-lowongan'>
    <div class="profile-card" >
        <div class="header">
        <div class="avatar">
            <img src="assets/company.jpg" alt="Avatar">
        </div>
        </div>
        <div class="body">
            <h3><?php echo $_SESSION['nama']; ?></h3>
            <p><?php echo $_SESSION['email']; ?></p>
            <p class="location">Tangerang, Banten</p>
        </div>
        <div class="footer">
            <span>Company</span>
        </div>
    </div>
</aside>

<section class="job-vacancy">
    <div class="container">
        <h1 class="form-heading">Post a New Job</h1>
        <form class="job-form" action="simpan_lowongan.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="posisi" class="form-label">Job Position:</label>
                <input type="text" id="posisi" name="posisi" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="deskripsi" class="form-label">Job Description:</label>
                <div id="editor" style="height: 200px;"></div> <!-- Div untuk Quill -->
                <input type="hidden" name="deskripsi" id="deskripsi"> <!-- Hidden input untuk menyimpan konten dari Quill -->
            </div>


            <div class="form-group">
                <label for="jenis_pekerjaan" class="form-label">Job Type:</label>
                <select id="jenis_pekerjaan" name="jenis_pekerjaan" class="form-select" required>
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="internship">Internship</option>
                </select>
            </div>

            <div class="form-group">
                <label for="jenis_lokasi" class="form-label">Location Type:</label>
                <select id="jenis_lokasi" name="jenis_lokasi" class="form-select" required>
                    <option value="on-site">On-site</option>
                    <option value="remote">Remote</option>
                    <option value="hybrid">Hybrid</option>
                </select>
            </div>

            <div class="form-group">
                <label for="image" class="form-label">Upload Image:</label>
                <div id="image-drop-area" class="drop-area">
                    <p>Drag & Drop your Image here or click to upload</p>
                    <input type="file" name="attachments[]" id="image" accept=".jpeg, .jpg, .png" multiple hidden>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</section>

<aside class="job-seeker-guidance">
<div class="guidance-card">
        <h3>Target your job to the right people</h3>
        <p class="recommendation">Reach top talent effortlessly</p>
        <div class="guidance-content">
            <div class="guidance-text">
                <div class="guidance-headline">
                    <strong style="margin-top:5px;">Define Your Job Clearly</strong>
                    <div class="guidance-image">
                        <img class="" src="assets/clarity.png" alt="Clarity">
                    </div>
                </div>
                <p style="margin-top:15px;">Clearly outline the job title, responsibilities, and expectations. A well-defined job description not only attracts suitable candidates but also helps them understand the role they are applying for. Use straightforward language and avoid jargon to ensure your message is clear.</p>
            </div>
        </div>
        
        <div class="guidance-content">
            <div class="guidance-text">
                <div class="guidance-headline">
                    <strong style="margin-top:5px;">Highlight Company Culture</strong>
                    <div class="guidance-image">
                        <img class="" src="assets/corporate-culture.png" alt="Company Culture">
                    </div>
                </div>
                <p style="margin-top:15px;">Showcase your company culture and values in the job listing. Candidates today are looking for workplaces that align with their personal values. Include details about your work environment, team dynamics, and any unique perks or benefits that your company offers.</p>
            </div>
        </div>

        <div class="guidance-content">
            <div class="guidance-text">
                <div class="guidance-headline">
                    <strong style="margin-top:5px;">Use Keywords Strategically</strong>
                    <div class="guidance-image">
                        <img class="" src="assets/keywords.png" alt="Keywords">
                    </div>
                </div>
                <p style="margin-top:15px;">Incorporate relevant keywords related to the job role in your posting. This enhances visibility on job boards and search engines, making it easier for potential candidates to find your listing. Research industry-specific terms and include them naturally in your description.</p>
            </div>
        </div>

        <div class="guidance-content">
            <div class="guidance-text">
                <div class="guidance-headline">
                    <strong style="margin-top:5px;">Be Transparent About Compensation</strong>
                    <div class="guidance-image">
                        <img class="" src="assets/compensation.png" alt="Compensation">
                    </div>
                </div>
                <p style="margin-top:15px;">Consider including salary ranges and other compensation details in your job listing. Transparency in compensation builds trust and attracts candidates who are comfortable with the offered pay. It also saves time for both parties by reducing mismatched expectations.</p>
            </div>
        </div>

        <div class="guidance-content">
            <div class="guidance-text">
                <div class="guidance-headline">
                    <strong style="margin-top:5px;">Encourage Diversity and Inclusion</strong>
                    <div class="guidance-image">
                        <img class="" src="assets/diversity.png" alt="Diversity">
                    </div>
                </div>
                <p style="margin-top:15px;">Emphasize your commitment to diversity and inclusion in your job postings. Encourage candidates from all backgrounds to apply, and outline any initiatives your company has in place to support a diverse workforce. This can broaden your talent pool and enhance company innovation.</p>
            </div>
        </div>

        <div class="guidance-content">
            <div class="guidance-text">
                <div class="guidance-headline">
                    <strong style="margin-top:5px;">Craft a Compelling Call to Action</strong>
                    <div class="guidance-image">
                        <img class="" src="assets/call-to-action.png" alt="Call to Action">
                    </div>
                </div>
                <p style="margin-top:15px;">End your job posting with a strong call to action. Encourage candidates to apply or reach out for more information. A compelling call to action can significantly increase the number of applications you receive.</p>
            </div>
        </div>
    </div>
</div>
            <div class="footer-section" style="margin-top: 20px; text-align: center;">
                <img src="assets/LinkInPurry-crop.png" alt="LinkedInPurry Logo" style="height: 25px; vertical-align: middle;">
                <span style="font-size: 14px; margin-left: 8px;">
                    LinkedInPurry Corporation © 2024
                </span>
            </div>
        </aside>
<main>
</body>
<script src=public/hamburgermenu.js></script>
</html>