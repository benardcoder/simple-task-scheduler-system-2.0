<?php
session_start();
$conn = new mysqli("localhost", "root", "", "user_system");

// Check if the session is already set or if the Remember Me cookie exists
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user'])) {
    // If the cookie is set, extract the user data from the cookie
    list($username, $id) = explode("|", $_COOKIE['user']);

    // Validate the cookie user
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE id=? AND username=?");
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $user_username);

    if ($stmt->fetch()) {
        // Set session variables if the cookie is valid
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $user_username;
    } else {
        // Invalid cookie data, redirect to login
        header("Location: index.html");
        exit();
    }
}

// If the user is not logged in through session or cookie, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Scheduler Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Task Scheduler, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>Here you can manage your tasks.</p>
        <a href="logout.php"><button>Logout</button></a>
    </div>
</body>
</html>