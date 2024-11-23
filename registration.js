document.getElementById("registerForm").addEventListener("submit", function(event) {
    event.preventDefault();

    // Get input fields and error message elements
    const username = document.getElementById("username");
    const email = document.getElementById("email");
    const password = document.getElementById("password");
    const usernameError = document.getElementById("usernameError");
    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");

    // Reset error messages
    usernameError.style.display = "none";
    emailError.style.display = "none";
    passwordError.style.display = "none";

    let isValid = true;

    // Validate username
    if (username.value.trim() === "") {
        usernameError.textContent = "Username is required";
        usernameError.style.display = "block";
        isValid = false;
    }

    // Validate email
    if (email.value.trim() === "") {
        emailError.textContent = "Email is required";
        emailError.style.display = "block";
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        emailError.textContent = "Please enter a valid email";
        emailError.style.display = "block";
        isValid = false;
    }

    // Validate password
    if (password.value.trim() === "") {
        passwordError.textContent = "Password is required";
        passwordError.style.display = "block";
        isValid = false;
    } else if (password.value.length < 6) {
        passwordError.textContent = "Password must be at least 6 characters";
        passwordError.style.display = "block";
        isValid = false;
    }

    // Show success message and redirect to login page if valid
    if (isValid) {
        alert("Registration successful! Please log in.");
        window.location.href = "index.html"; // Redirect to the login page
    }
});