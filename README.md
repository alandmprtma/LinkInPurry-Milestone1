
# LinkInPurry - IF3110 Web-Based Development

## Project Overview

![LinkInPurry](screenshots/LinkInPurry.png)

LinkInPurry is a web-based application aimed to assist secret agents, like Purry the Platypus, and other members of O.W.C.A. (Organisasi Warga Cool Abiez) in finding job opportunities. The platform provides features for both job seekers and companies to interact, post job openings, and manage job applications.

## Key Features

- **User Authentication:** Separate authentication for Job Seekers and Companies.
- **Job Management:** Companies can create, edit, and close job vacancies.
- **Job Application:** Job Seekers can search, filter, and apply for jobs.
- **Responsive Design:** The application is responsive and optimized for different screen sizes.
- **Rich Text Editor:** Implemented using quill.js for job descriptions and other rich text fields.
- **Pagination and Sorting:** Job listings are paginated and can be sorted based on various criteria.
- **File Attachments:** Companies can upload related job images, and applicants can attach CVs and videos.

## System Requirements

- **Client-Side:**
  - JavaScript, HTML, CSS (No frameworks like Bootstrap or Tailwind)
- **Server-Side:**
  - PHP (no frameworks like Laravel or CodeIgniter)
- **Database:**
  - PostgreSQL
- **Other Tools:**
  - Docker for containerization (Dockerfile and docker-compose.yml)

## Installation Instructions

1. **Clone the Repository:**
   ```
   git clone https://github.com/Labpro-21/if3110-tubes-2024-k03-03
   ```

2. **Install Dependencies:**
   No external libraries are required, but make sure to have PHP and PostgreSQL installed.

3. **Running the Application:**
   - Using Docker:
     ```
     docker-compose up --build
     ```
   
4. **Access the Application:**
   Open your browser and go to `http://localhost:8080`.

## Usage Instructions

### Company Features:
- Create, edit, and close job listings.
- View and manage applications, including accepting or rejecting job seekers.

### Job Seeker Features:
- Search, filter, and apply for job listings.
- Track job application statuses.

## Screenshots

### 1. Login Page
<div style="text-align: center;">
    <img src="screenshots/login.png" alt="Login Page" width="400" />
</div>

### 2. Register page
<div style="text-align: center;">
    <img src="screenshots/register1.png" alt="Register Page" width="400" />
</div>
<div style="text-align: center;">
    <img src="screenshots/register2.png" alt="Register Page" width="400" />
</div>

### 3. Home (Company)
<div style="text-align: center;">
    <img src="screenshots/home_company.png" alt="Home Page" width="400" />
</div>

### 4. Detail Lowongan (Company)

<div style="text-align: center;">
    <img src="screenshots/detail_lowongan_company.png" alt="Detail Lowongan (Company)" width="400" />
</div>

### 5. Tambah Lowongan (Company)
<div style="text-align: center;">
    <img src="screenshots/buat_lowongan.png" alt="Tambah Lowongan (Company)" width="400" />
</div>


### 6. Detail Pelamar
<div style="text-align: center;">
    <img src="screenshots/detail_pelamar.png" alt="Detail Pelamar" width="400" />
</div>


### 7. Home (Jobseeker)
<div style="text-align: center;">
    <img src="screenshots/home_jobseeker.png" alt="Home (Jobseeker)" width="400" />
</div>

### 8. Detail Lowongan (Jobseeker)
<div style="text-align: center;">
    <img src="screenshots/detail_lowongan_jobseeker.png" alt="Detail Lowongan (Jobseeker)" width="400" />
</div>

### 9. Apply Lowongan
<div style="text-align: center;">
    <img src="screenshots/apply.png" alt="Apply Lowongan" width="400" />
</div>

### 10. Guest Mode
<div style="text-align: center;">
    <img src="screenshots/guest.png" alt="Guest Mode" width="400" />
</div>

### 11. Edit Lowongan
<div style="text-align: center;">
    <img src="screenshots/edit_lowongan.png" alt="Edit Lowongan" width="400" />
</div>

### 12. Riwayat Apply
<div style="text-align: center;">
    <img src="screenshots/.png" alt="Riwayat Apply" width="400" />
</div>

### 13. Profil Company
<div style="text-align: center;">
    <img src="screenshots/.png" alt="Profil Company" width="400" />
</div>

## Lighthouse

### 1. Login Page
![Login Page](screenshots/login.png)

### 2. Home Page (Job Seeker)
![Job Seeker Home](screenshots/jobseeker-home.png)

### 3. Job Listing Page (Company)
![Company Job Listing](screenshots/company-jobs.png)

### 4. Buat Lowongan (Company)
<div style="text-align: center;">
    <img src="lighthouse/buat_lowongan_before.png" alt="Buat Lowongan Lighthouse After" width="400" />
    <p>Before Enhancement</p>
</div>
<div style="text-align: center;">
    <img src="lighthouse/buat_lowongan_after.png" alt="Buat Lowongan Lighthouse Before" width="400" />
    <p>After Enhancement</p>
</div>

### 5. Detail Lamaran (Company)
<div style="text-align: center;">
    <img src="lighthouse/detail_lamaran_before.png" alt="Detail Lamaran Lighthouse After" width="400" />
    <p>Before Enhancement</p>
</div>
<div style="text-align: center;">
    <img src="lighthouse/detail_lamaran_after.png" alt="Detail Lamaran Lighthouse Before" width="400" />
    <p>After Enhancement</p>
</div>

### 6. Edit Lowongan (Company)
<div style="text-align: center;">
    <img src="lighthouse/edit_job_before.png" alt="Edit Job Lighthouse After" width="400" />
    <p>Before Enhancement</p>
</div>
<div style="text-align: center;">
    <img src="lighthouse/edit_job_after.png" alt="Edit Job Lighthouse Before" width="400" />
    <p>After Enhancement</p>
</div>

### 7. Home Page (Company)
<div style="text-align: center;">
    <img src="lighthouse/home_company_before.png" alt="Home Company Lighthouse After" width="400" />
    <p>Before Enhancement</p>
</div>
<div style="text-align: center;">
    <img src="lighthouse/home_company_after.png" alt="Home Company Lighthouse Before" width="400" />
    <p>After Enhancement</p>
</div>

### 8. Lowongan Detail (Company)
<div style="text-align: center;">
    <img src="lighthouse/lowongan_detail_before.png" alt="Lowongan Detail Company Lighthouse After" width="400" />
    <p>Before Enhancement</p>
</div>
<div style="text-align: center;">
    <img src="lighthouse/home_company_after.png" alt="Lowongan Detail Lighthouse Before" width="400" />
    <p>After Enhancement</p>
</div>

## Task Allocation

| Feature                               | Server-Side (PHP)       | Client-Side (HTML, CSS, JS) |
|---------------------------------------|--------------------------|------------------------------|
| Login/Logout                          | 13522146                 | 13522146                     |
| Register                              | 13522146, 13522130      | 13522146, 13522130           |
| Halaman Home (JobSeeker)             | 13522146, 13522124      | 13522146, 13522124           |
| Halaman Home (Company)                | 13522146, 13522124      | 13522146, 13522124           |
| Halaman Tambah Lowongan (Company)    | 13522146                 | 13522124, 13522146           |
| Halaman Detail Lowongan (Company)     | 13522146                 | 13522124                     |
| Halaman Detail Lamaran (Company)      | 13522146                 | 13522124                     |
| Halaman Edit Lowongan (Company)       | 13522124                 | 13522124, 13522146           |
| Halaman Detail Lowongan (JobSeeker)   | 13522146                 | 13522146                     |
| Halaman Lamaran (JobSeeker)           | 13522146                 | 13522146                     |
| Halaman Riwayat (JobSeeker)           | 13522130                 | 13522130                     |
| Halaman Profil (Company)              | 13522130                 | 13522130                     |


## Deliverables

- **Final Submission:** 24th October 2024, 21.00 WIB
- **Milestones:**
  - Milestone 1 focuses on completing the core features and ensuring a functional web application.
  - Further milestones will expand upon this foundation, adding advanced features and optimizations.



