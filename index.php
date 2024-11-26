<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type']; ?>">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Signup Form -->
        <div id="signupForm" class="form-container">
            <h2>Sign Up</h2>
            <form action="signup.php" method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password (min 4 characters)" minlength="4" required>
                <button type="submit" class="btn">Sign Up</button>
            </form>
            <p class="text-center">Already have an account? <a href="#" onclick="showLogin()">Login</a></p>
        </div>

        <!-- Login Form -->
        <div id="loginForm" class="form-container hidden">
            <h2>Login</h2>
            <form action="login.php" method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <div class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Remember Me</label>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
            <p class="text-center"><a href="#" onclick="showResetPassword()">Forgot Password?</a></p>
            <p class="text-center">Don't have an account? <a href="#" onclick="showSignup()">Sign Up</a></p>
        </div>

        <!-- Reset Password Form -->
        <div id="resetForm" class="form-container hidden">
            <h2>Reset Password</h2>
            <form action="reset.php" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <button type="submit" class="btn">Reset Password</button>
            </form>
            <p class="text-center">Remember your password? <a href="#" onclick="showLogin()">Login</a></p>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>