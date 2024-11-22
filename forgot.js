document.getElementById("forgotPasswordForm").addEventListener("submit", function (event) {
    event.preventDefault();

    const email = document.getElementById("email");
    const emailError = document.getElementById("emailError");

    // Reset error message
    emailError.style.display = "none";

    // Validate email
    if (email.value.trim() === "") {
        emailError.textContent = "Email is required";
        emailError.style.display = "block";
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        emailError.textContent = "Please enter a valid email address";
        emailError.style.display = "block";
    } else {
        // Simulate sending reset link
        alert("A password reset link has been sent to your email.");
        email.value = ""; // Clear the input field
    }
});