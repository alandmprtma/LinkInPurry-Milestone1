-- Buat ENUM untuk role user
CREATE TYPE user_role AS ENUM ('jobseeker', 'company');

-- Buat ENUM untuk jenis pekerjaan
CREATE TYPE job_type AS ENUM ('internship', 'part-time', 'full-time');

-- Buat ENUM untuk jenis lokasi pekerjaan
CREATE TYPE location_type AS ENUM ('on-site', 'hybrid', 'remote');

-- Buat ENUM untuk status lamaran
CREATE TYPE application_status AS ENUM ('accepted', 'rejected', 'waiting');

-- Tabel Users (menggunakan ENUM user_role)
CREATE TABLE Users (
    user_id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role user_role NOT NULL,  -- Gunakan ENUM kustom
    nama VARCHAR(255) NOT NULL
);

-- Tabel CompanyDetail
CREATE TABLE CompanyDetail (
    user_id INT REFERENCES Users(user_id) ON DELETE CASCADE,
    lokasi VARCHAR(255),
    about TEXT,
    PRIMARY KEY (user_id)
);

-- Tabel Lowongan (menggunakan ENUM job_type dan location_type)
CREATE TABLE Lowongan (
    lowongan_id SERIAL PRIMARY KEY,
    company_id INT REFERENCES Users(user_id) ON DELETE CASCADE,
    posisi VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    jenis_pekerjaan job_type NOT NULL,  -- Gunakan ENUM kustom
    jenis_lokasi location_type NOT NULL,  -- Gunakan ENUM kustom
    is_open BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel AttachmentLowongan
CREATE TABLE AttachmentLowongan (
    attachment_id SERIAL PRIMARY KEY,
    lowongan_id INT REFERENCES Lowongan(lowongan_id) ON DELETE CASCADE,
    file_path VARCHAR(255) NOT NULL
);

-- Tabel Lamaran (menggunakan ENUM application_status)
CREATE TABLE Lamaran (
    lamaran_id SERIAL PRIMARY KEY,
    user_id INT REFERENCES Users(user_id) ON DELETE CASCADE,
    lowongan_id INT REFERENCES Lowongan(lowongan_id) ON DELETE CASCADE,
    cv_path VARCHAR(255) NOT NULL,
    video_path VARCHAR(255),
    status application_status DEFAULT 'waiting',  -- Gunakan ENUM kustom
    status_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
