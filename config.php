<?php
$host = 'localhost';
$dbname = 'user_system';
$username = 'root';
$password = '';

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'benardonyango@kabarak.ac.ke');
define('SMTP_PASS', 'jtei isif rivz eeat');
define('SMTP_PORT', 587);
define('SMTP_FROM_EMAIL', 'benardonyango@kabarak.ac.ke');

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}
?>