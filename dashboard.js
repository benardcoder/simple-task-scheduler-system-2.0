document.addEventListener('DOMContentLoaded', function() {
    // Get current page URL
    const currentPage = window.location.pathname.split('/').pop();
    
    // Get all navigation links
    const navLinks = document.querySelectorAll('.sidebar-nav li a');
    
    // Remove active class from all links
    navLinks.forEach(link => {
        link.parentElement.classList.remove('active');
        
        // Add active class to current page link
        if(link.getAttribute('href') === currentPage) {
            link.parentElement.classList.add('active');
        }
    });
});