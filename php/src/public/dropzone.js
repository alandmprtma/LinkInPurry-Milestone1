document.addEventListener('DOMContentLoaded', () => {
    function handleDrop(event, input) {
        event.preventDefault();
        event.stopPropagation(); // Mencegah perilaku default seperti preview

        const droppedFiles = event.dataTransfer.files;
        if (droppedFiles.length > 0) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(droppedFiles[0]); // Ambil file pertama (atau sesuaikan untuk multi-upload)
            input.files = dataTransfer.files; // Set input dengan file yang di-drop

            updateDropAreaText(event.target, droppedFiles[0].name);
        }
    }

    function addDropEvents(dropArea, input) {
        dropArea.addEventListener('dragover', (event) => {
            event.preventDefault(); // Mencegah reload halaman saat drag
            dropArea.classList.add('dragover');
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('dragover');
        });

        dropArea.addEventListener('drop', (event) => {
            dropArea.classList.remove('dragover');
            handleDrop(event, input); // Tangani drop
        });

        dropArea.addEventListener('click', () => input.click()); // Buka file dialog saat diklik

        input.addEventListener('change', () => {
            updateDropAreaText(dropArea, input.files[0]?.name || 'No file chosen');
        });
    }

    function updateDropAreaText(dropArea, text) {
        dropArea.querySelector('p').textContent = text;
    }

    // Inisialisasi elemen drop area dan input
    const cvDropArea = document.getElementById('cv-drop-area');
    const cvInput = document.getElementById('cv');
    addDropEvents(cvDropArea, cvInput);

    const videoDropArea = document.getElementById('video-drop-area');
    const videoInput = document.getElementById('video');
    addDropEvents(videoDropArea, videoInput);
});
