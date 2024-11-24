// Function to show the signup form and hide others
function showSignup() {
    document.getElementById("signupForm").classList.remove("hidden");
    document.getElementById("loginForm").classList.add("hidden");
    document.getElementById("resetForm").classList.add("hidden");
}

// Function to show the login form and hide others
function showLogin() {
    document.getElementById("signupForm").classList.add("hidden");
    document.getElementById("loginForm").classList.remove("hidden");
    document.getElementById("resetForm").classList.add("hidden");
}

// Function to show the reset password form and hide others
function showResetPassword() {
    document.getElementById("signupForm").classList.add("hidden");
    document.getElementById("loginForm").classList.add("hidden");
    document.getElementById("resetForm").classList.remove("hidden");
}

// Default to show the signup form on page load
showSignup();