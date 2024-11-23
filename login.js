// Check for remembered username when page loads
window.addEventListener('load', function() {
    const rememberedUsername = localStorage.getItem("rememberedUsername");
    if (rememberedUsername) {
        document.getElementById("username").value = rememberedUsername;
        document.getElementById("rememberMe").checked = true;
    }
});

document.getElementById("authForm").addEventListener("submit", function (event) {
    event.preventDefault();

    const username = document.getElementById("username");
    const password = document.getElementById("password");
    const rememberMe = document.getElementById("rememberMe");
    const usernameError = document.getElementById("usernameError");
    const passwordError = document.getElementById("passwordError");

    // Reset error messages
    usernameError.style.display = "none";
    passwordError.style.display = "none";
    let isValid = true;

    // Validate username
    if (username.value.trim() === "") {
        usernameError.textContent = "Username is required";
        usernameError.style.display = "block";
        isValid = false;
    }

    // Validate password
    if (password.value.trim() === "") {
        passwordError.textContent = "Password is required";
        passwordError.style.display = "block";
        isValid = false;
    }

    // If form is valid
    if (isValid) {
        if (rememberMe.checked) {
            localStorage.setItem("rememberedUsername", username.value);
        } else {
            localStorage.removeItem("rememberedUsername");
        }

        // Here you would typically make an API call to verify credentials
        // For demo purposes, we're just showing an alert
        alert("Logged in successfully!");
        window.location.href = "dashboard.html"; // Redirect to dashboard
    }
});

// Add a "Forgot Password" link handler
document.getElementById("forgotPassword").addEventListener("click", function(e) {
    e.preventDefault();
    const email = prompt("Please enter your email address:");
    
    if (email) {
        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert("Please enter a valid email address");
            return;
        }

        // Show loading state
        const forgotPasswordLink = document.getElementById("forgotPassword");
        forgotPasswordLink.style.pointerEvents = "none";
        forgotPasswordLink.style.opacity = "0.5";

        // In a real application, this would make an API call to your backend
        // to send a reset email. For demo purposes, we'll simulate it
        setTimeout(() => {
            alert("If an account exists with this email, you will receive a reset link shortly.");
            
            // Simulate email link - in reality, this would be handled by your backend
            const resetToken = btoa(email); // Basic encoding (not secure - demo only)
            localStorage.setItem("resetToken", resetToken);
            
            // Reset loading state
            forgotPasswordLink.style.pointerEvents = "auto";
            forgotPasswordLink.style.opacity = "1";

            // In production, your backend would:
            // 1. Generate a secure random token
            // 2. Store the token with an expiration time
            // 3. Send an email with a link containing the token
            // 4. The link would point to reset-password.html?token=YOUR_SECURE_TOKEN
            
            // For demo purposes, we'll create the reset link
            const resetUrl = `reset-password.html?token=${resetToken}`;
            console.log("Reset link (for demo):", resetUrl);
        }, 1500); // Simulate network delay
    }
});

// Password visibility toggle
document.querySelectorAll(".toggle-password").forEach(button => {
    button.addEventListener("click", function() {
        const passwordInput = this.previousElementSibling;
        const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
        passwordInput.setAttribute("type", type);
        
        // Update button icon/text
        this.textContent = type === "password" ? "Show" : "Hide";
    });
});

// Handle Enter key in username field
document.getElementById("username").addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        e.preventDefault();
        document.getElementById("password").focus();
    }
});

// Prevent form submission on Enter key in password field
document.getElementById("password").addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        e.preventDefault();
        document.getElementById("authForm").dispatchEvent(new Event("submit"));
    }
});

// Clear error messages when user starts typing
document.getElementById("username").addEventListener("input", function() {
    document.getElementById("usernameError").style.display = "none";
});

document.getElementById("password").addEventListener("input", function() {
    document.getElementById("passwordError").style.display = "none";
});
