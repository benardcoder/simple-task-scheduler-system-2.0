<?php
session_start();
require_once 'config.php';
require_once 'includes/EmailService.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        // Validate password
        if (strlen($password) < 4) {
            throw new Exception("Password must be at least 4 characters long");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        if ($hashed_password === false) {
            throw new Exception("Password hashing failed");
        }

        // Check if username or email already exists
        $check = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        
        if ($check->rowCount() > 0) {
            throw new Exception("Username or email already exists");
        }

        // Begin transaction
        $pdo->beginTransaction();

        try {
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            
            // Send welcome email
            $emailService = new EmailService();
            if ($emailService->sendWelcomeEmail($email, $username)) {
                $pdo->commit();
                $_SESSION['message'] = "Registration successful! Welcome email sent. Please login.";
                $_SESSION['message_type'] = "success";
            } else {
                // If email fails, still register user but notify them
                $pdo->commit();
                $_SESSION['message'] = "Registration successful but welcome email could not be sent. Please login.";
                $_SESSION['message_type'] = "warning";
            }

            header("Location: index.php");
            exit();

        } catch (Exception $e) {
            // Rollback transaction if something fails
            $pdo->rollBack();
            throw new Exception("Registration failed: " . $e->getMessage());
        }

    } catch(Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Create Account</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" required 
                       minlength="3" maxlength="50"
                       pattern="[a-zA-Z0-9_-]+" 
                       title="Username can only contain letters, numbers, underscores and hyphens">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" required minlength="4">
                <small>Password must be at least 4 characters long</small>
            </div>

            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>

        <div class="links">
            <p>Already have an account? <a href="index.php">Login here</a></p>
        </div>
    </div>
</body>
</html>