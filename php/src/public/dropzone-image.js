document.addEventListener('DOMContentLoaded', () => {
    function handleImageDrop(event, input) {
        event.preventDefault();
        event.stopPropagation(); // Mencegah perilaku default seperti preview

        const droppedFiles = event.dataTransfer.files;
        if (droppedFiles.length > 0) {
            const dataTransfer = new DataTransfer();
            for (let i = 0; i < droppedFiles.length; i++) {
                dataTransfer.items.add(droppedFiles[i]); // Tambahkan semua file yang di-drop
            }
            input.files = dataTransfer.files; // Set input dengan file yang di-drop

            updateImageDropAreaText(event.target, Array.from(droppedFiles).map(file => file.name).join(', '));
        }
    }

    function addImageDropEvents(dropArea, input) {
        dropArea.addEventListener('dragover', (event) => {
            event.preventDefault(); // Mencegah reload halaman saat drag
            dropArea.classList.add('dragover');
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('dragover');
        });

        dropArea.addEventListener('drop', (event) => {
            dropArea.classList.remove('dragover');
            handleImageDrop(event, input); // Tangani drop
        });

        dropArea.addEventListener('click', () => input.click()); // Buka file dialog saat diklik

        input.addEventListener('change', () => {
            updateImageDropAreaText(dropArea, Array.from(input.files).map(file => file.name).join(', ') || 'No file chosen');
        });
    }

    function updateImageDropAreaText(dropArea, text) {
        dropArea.querySelector('p').textContent = text;
    }

    // Inisialisasi elemen drop area dan input khusus untuk image
    const imageDropArea = document.getElementById('image-drop-area');
    const imageInput = document.getElementById('image');
    addImageDropEvents(imageDropArea, imageInput);
});
