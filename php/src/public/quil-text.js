document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Quill editor setelah halaman selesai dimuat
    var quill = new Quill('#editor', {
        theme: 'snow'
    });

    // Pastikan form sudah tersedia sebelum menambahkan event listener untuk submit
    var form = document.querySelector('form');
    if (form) {
        form.onsubmit = function() {
            // Ambil konten dari Quill dan simpan ke input hidden sebelum form dikirimkan
            var deskripsi = document.querySelector('input[name=deskripsi]');
            deskripsi.value = quill.root.innerHTML;
        };
    } else {
        console.error('Form not found');
    }
});
