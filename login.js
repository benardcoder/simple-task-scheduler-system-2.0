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

        alert("Logged in successfully!");
        window.location.href = "dashboard.html"; // Redirect to dashboard
    }
});