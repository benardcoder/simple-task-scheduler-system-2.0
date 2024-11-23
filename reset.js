document.getElementById("resetForm").addEventListener("submit", function(event) {
    event.preventDefault();

    const newPassword = document.getElementById("newPassword");
    const confirmPassword = document.getElementById("confirmPassword");
    const newPasswordError = document.getElementById("newPasswordError");
    const confirmPasswordError = document.getElementById("confirmPasswordError");

    // Reset error messages
    newPasswordError.style.display = "none";
    confirmPasswordError.style.display = "none";
    let isValid = true;

    // Validate new password
    if (newPassword.value.trim() === "") {
        newPasswordError.textContent = "New password is required";
        newPasswordError.style.display = "block";
        isValid = false;
    }

    // Validate password confirmation
    if (confirmPassword.value.trim() === "") {
        confirmPasswordError.textContent = "Please confirm your password";
        confirmPasswordError.style.display = "block";
        isValid = false;
    } else if (newPassword.value !== confirmPassword.value) {
        confirmPasswordError.textContent = "Passwords do not match";
        confirmPasswordError.style.display = "block";
        isValid = false;
    }

    if (isValid) {
        // In a real application, this would make an API call to your backend
        // to update the password using a secure token
        const resetToken = localStorage.getItem("resetToken");
        
        if (resetToken) {
            // Simulate password update
            alert("Password updated successfully!");
            localStorage.removeItem("resetToken");
            window.location.href = "login.html";
        } else {
            alert("Invalid or expired reset token");
        }
    }
});