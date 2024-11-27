<?php
require_once './includes/EmailService.php';

try {
    $emailService = new EmailService();
    
    // Replace this with the email where you want to receive the test
    $testEmail = "onyisouda@gmail.com";  // Change this to your email
    $testUsername = "Test User";
    
    $result = $emailService->sendWelcomeEmail($testEmail, $testUsername);
    
    if ($result) {
        echo "Test email sent successfully! Check your inbox.";
    } else {
        echo "Failed to send test email.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}