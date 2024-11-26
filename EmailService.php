<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure you have PHPMailer installed via composer

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Configure SMTP settings
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com'; // Or your SMTP host
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'your-email@gmail.com'; // Your email
        $this->mailer->Password = 'your-app-password'; // Your email password or app-specific password
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        
        // Set default sender
        $this->mailer->setFrom('your-email@gmail.com', 'Task Manager');
    }

    public function sendWelcomeEmail($userEmail, $username) {
        try {
            $this->mailer->addAddress($userEmail);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Welcome to Task Manager!';
            
            // Email body
            $body = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2>Welcome to Task Manager!</h2>
                    <p>Dear {$username},</p>
                    <p>Thank you for joining Task Manager. We're excited to have you on board!</p>
                    <p>With Task Manager, you can:</p>
                    <ul>
                        <li>Create and manage tasks</li>
                        <li>Set priorities and due dates</li>
                        <li>Track your progress</li>
                        <li>Collaborate with team members</li>
                    </ul>
                    <p>If you have any questions, feel free to reach out to our support team.</p>
                    <p>Best regards,<br>The Task Manager Team</p>
                </body>
                </html>
            ";
            
            $this->mailer->Body = $body;
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    public function sendPasswordResetEmail($userEmail, $newPassword) {
        try {
            $this->mailer->addAddress($userEmail);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset - Task Manager';
            
            // Email body
            $body = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2>Password Reset</h2>
                    <p>Your password has been reset.</p>
                    <p>Your new password is: <strong>{$newPassword}</strong></p>
                    <p>Please login with this password and change it immediately for security purposes.</p>
                    <p>If you didn't request this password reset, please contact our support team immediately.</p>
                    <p>Best regards,<br>The Task Manager Team</p>
                </body>
                </html>
            ";
            
            $this->mailer->Body = $body;
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
}