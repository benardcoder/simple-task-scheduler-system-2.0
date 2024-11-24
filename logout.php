<?php
session_start();

// Clear the session data
session_unset();
session_destroy();

// Clear the "Remember Me" cookie by setting it to a past time
setcookie("user", "", time() - 3600, "/");

header("Location: index.html");
exit();
?>