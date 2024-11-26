<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            if (isset($_POST['remember'])) {
                // Set cookie for 30 days
                setcookie('user_login', $user['username'], time() + (86400 * 30), "/");
            }
            
            $_SESSION['message'] = "Welcome back, " . htmlspecialchars($username) . "!";
            $_SESSION['message_type'] = "success";
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['message'] = "Invalid username or password";
            $_SESSION['message_type'] = "error";
            header("Location: index.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: index.php");
        exit();
    }
}
?>