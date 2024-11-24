<?php
session_start();
$conn = new mysqli("localhost", "root", "", "user_system");

// Check the database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the Remember Me checkbox is checked
    $remember = isset($_POST['remember']) ? true : false;

    // Query the database to get the user details based on the provided username
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hashed_password);

    // Check if the user was found
    if ($stmt->fetch()) {
        // Verify the entered password against the stored hashed password
        if (password_verify($password, $hashed_password)) {
            // Password is correct, proceed with login

            // Create session for the user
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;

            // If Remember Me is checked, set a cookie for 30 days
            if ($remember) {
                $cookie_name = "user";
                $cookie_value = $username . "|" . $id;
                setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // Cookie expires in 30 days
            }

            // Redirect to the dashboard page
            header("Location: dashboard.php");
            exit();
        } else {
            // Password does not match
            echo "<script>
                    alert('Wrong username or password!');
                    window.location.href = 'index.html'; // Redirect back to the login page
                  </script>";
        }
    } else {
        // Username not found in the database
        echo "<script>
                alert('Wrong username or password!');
                window.location.href = 'index.html'; // Redirect back to the login page
              </script>";
    }
}
?>