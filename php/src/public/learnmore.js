function showSection(section) {
    const content = document.getElementById('content');
  
    // Load the corresponding PHP content
    fetch(`${section}.php`)
      .then(response => response.text())
      .then(html => {
        content.innerHTML = html;
  
        // Update active state on sidebar items
        document.querySelectorAll('.sidebar ul li').forEach((item) => {
          item.classList.remove('active');
        });
        document.querySelector(`li[onclick="showSection('${section}')"]`).classList.add('active');
      })
      .catch(error => console.error('Error loading content:', error));
  }
  