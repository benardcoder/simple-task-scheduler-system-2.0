<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['user_login'])) {
    setcookie('user_login', '', time()-3600, '/');
}

// Redirect to login page
header("Location: index.php");
exit();
?>