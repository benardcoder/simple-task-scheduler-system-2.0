<?php
$conn = new mysqli("localhost", "root", "", "user_system");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $new_password = bin2hex(random_bytes(4));
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
    $stmt->bind_param("ss", $hashed_password, $email);

    if ($stmt->execute()) {
        mail($email, "Password Reset", "Your new password is: $new_password");
        echo "<script>
                alert('Password reset successful. Check your email for the new password!');
                window.location.href = 'index.html';
              </script>";
    } else {
        echo "<script>alert('Error resetting password. Please try again!');</script>";
    }
}
?>