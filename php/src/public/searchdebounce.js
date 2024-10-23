let timeout;

function handleSearchInput(event) {

    searchAutocomplete(); // Jalankan autocomplete tanpa delay
    timeout = setTimeout(() => {
        document.getElementById('search-form').submit();
    }, 2500);
    if (event.key === 'Enter') {
        event.preventDefault();
        document.getElementById('search-form').submit();
    }
}