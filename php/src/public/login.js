// Tangkap elemen form
const loginForm = document.getElementById('loginForm');
const errorMessage = document.getElementById('error-message');

// Tambahkan event listener untuk submit form
loginForm.addEventListener('submit', function(event) {
    event.preventDefault(); // Mencegah form submit secara default

    // Ambil data input dari form
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Cek apakah email dan password tidak kosong
    if (!email || !password) {
        errorMessage.textContent = 'Email dan password harus diisi.';
        return;
    }

    // Buat objek XMLHttpRequest
    const xhr = new XMLHttpRequest();

    // Konfigurasi request POST ke file login.php
    xhr.open('POST', 'login.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Tambahkan event listener untuk menangani respon dari server
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Parsing respon dari server (misalnya dalam format JSON)
            const response = JSON.parse(xhr.responseText);

            if (response.success) {
                // Jika login berhasil, cek role untuk mengarahkan ke halaman yang tepat
                if (response.role === 'company') {
                    window.location.href = '../home_company.php'; // Halaman untuk company
                } else if (response.role === 'jobseeker') {
                    window.location.href = '../home_jobseeker.php'; // Halaman untuk job seeker
                }
            } else {
                // Jika login gagal, tampilkan pesan error
                errorMessage.textContent = response.message;
            }
        }
    };

    // Kirim data login ke server
    const data = `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`;
    xhr.send(data);
});
