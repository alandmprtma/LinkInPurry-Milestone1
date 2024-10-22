document.addEventListener('DOMContentLoaded', function() {
    // Pastikan elemen #editor ada di halaman sebelum menginisialisasi Quill
    var editorContainer = document.querySelector('#editor');
    if (editorContainer) {
        // Inisialisasi Quill editor setelah halaman selesai dimuat
        var quill = new Quill('#editor', {
            theme: 'snow'
        });

        // Pastikan form sudah tersedia sebelum menambahkan event listener untuk submit
        var form = document.querySelector('form');
        if (form) {
            form.onsubmit = function() {
                // Ambil konten dari Quill dan simpan ke input hidden sebelum form dikirimkan
                var status_reason = document.querySelector('input[name=status_reason]');
                status_reason.value = quill.root.innerHTML;
            };
        }
    }
});
