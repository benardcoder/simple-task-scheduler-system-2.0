// Theme handling functions
function applyTheme(theme) {
    document.body.className = `theme-${theme}`;
    localStorage.setItem('theme', theme);
}

function initializeTheme() {
    // Check localStorage first
    let theme = localStorage.getItem('theme');
    
    // If no theme in localStorage, use default
    if (!theme) {
        theme = 'light';
    }
    
    // Apply theme
    applyTheme(theme);
}

// Initialize theme when page loads
document.addEventListener('DOMContentLoaded', initializeTheme);

// Listen for theme changes from other tabs/windows
window.addEventListener('storage', function(e) {
    if (e.key === 'theme') {
        applyTheme(e.newValue);
    }
});