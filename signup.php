<?php
session_start();
$conn = new mysqli("localhost", "root", "", "user_system");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (strlen($password) < 4) {
        echo "<script>alert('Password must be at least 4 characters long.'); window.location.href='index.html';</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>
                alert('Signup successful! Please login.');
                window.location.href = 'index.html';
              </script>";
    } else {
        echo "<script>alert('Signup failed: Email or username already exists.'); window.location.href='index.html';</script>";
    }
}
?>