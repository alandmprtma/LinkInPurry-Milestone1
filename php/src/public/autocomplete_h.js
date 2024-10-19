function searchAutocomplete() {
    const query = document.getElementById('search_keyword').value;
    const company_id = document.getElementById('company_id').value;
    console.log("searchAutocomplete triggered");
    if (query.length > 2) {  // Mulai pencarian jika panjang input > 2 karakter
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "autocomplete_search_company.php?query=" + query, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const results = JSON.parse(xhr.responseText);
                let autocompleteResults = document.getElementById('autocomplete-results');
                autocompleteResults.innerHTML = '';  // Kosongkan hasil sebelumnya
                console.log(results);
                // Tampilkan maksimal 3 hasil
                results.forEach(result => {
                    const item = document.createElement('div');
                    item.classList.add('autocomplete-item');
                    item.innerHTML = `
                        <a href="detail_lowongan_jobseeker.php?lowongan_id=${result.lowongan_id}">
                            <div class="icon">
                               <img src="assets/search-icon-removebg-preview-mirror.png" alt="Search Icon" width="15">
                            </div>
                            <div class="autocomplete-text">
                                <span class="main-text">${result.posisi}</span>
                            </div>
                        </a>`;
                    autocompleteResults.appendChild(item);
                });
                if (autocompleteResults.childElementCount > 0) {
                    const seeAllButton = document.createElement('button');
                    seeAllButton.type = 'submit';  // Setel type sebagai 'button' agar tidak mengirim form
                    seeAllButton.classList.add('see-all-results-btn');
                    seeAllButton.id = 'seeAllResultsBtn';
                    seeAllButton.innerText = 'See All Results';
                    autocompleteResults.appendChild(seeAllButton);
                }
            }
        };
        xhr.send();
    } else {
        document.getElementById('autocomplete-results').innerHTML = '';  // Kosongkan hasil jika input kurang dari 3 karakter
    }
}