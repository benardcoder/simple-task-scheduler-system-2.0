<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require  __DIR__'/..vendor/autoload.php';

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;                      
            $this->mailer->isSMTP();                                            
            $this->mailer->Host       = 'smtp.gmail.com';                     
            $this->mailer->SMTPAuth   = true;                                   
            $this->mailer->Username   = 'your-actual-gmail@gmail.com';    // Replace with your Gmail     
            $this->mailer->Password   = 'abcd efgh ijkl mnop';           // Replace with your 16-digit app password                               
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
            $this->mailer->Port       = 465;                                    

            $this->mailer->setFrom('your-actual-gmail@gmail.com', 'Task Manager');
            
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function sendWelcomeEmail($userEmail, $username) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Welcome to Task Manager!';
            
            $body = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>
                            Welcome to Task Manager!
                        </h2>
                        <p>Dear {$username},</p>
                        <p>Thank you for joining Task Manager. We're excited to have you on board!</p>
                        <p>With Task Manager, you can:</p>
                        <ul style='list-style-type: none; padding: 0;'>
                            <li style='margin: 10px 0; padding-left: 20px; border-left: 3px solid #3498db;'>
                                ✓ Create and manage tasks
                            </li>
                            <li style='margin: 10px 0; padding-left: 20px; border-left: 3px solid #3498db;'>
                                ✓ Set priorities and due dates
                            </li>
                            <li style='margin: 10px 0; padding-left: 20px; border-left: 3px solid #3498db;'>
                                ✓ Track your progress
                            </li>
                            <li style='margin: 10px 0; padding-left: 20px; border-left: 3px solid #3498db;'>
                                ✓ Collaborate with team members
                            </li>
                        </ul>
                        <p>If you have any questions, feel free to reach out to our support team.</p>
                        <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                            Best regards,<br>
                            The Task Manager Team
                        </p>
                    </div>
                </body>
                </html>
            ";
            
            $this->mailer->Body = $body;
            $this->mailer->send();
            error_log("Welcome email sent successfully to: $userEmail");
            return true;
        } catch (Exception $e) {
            error_log("Failed to send welcome email: " . $e->getMessage());
            return false;
        }
    }

    public function sendPasswordResetLink($userEmail, $resetToken) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Request - Task Manager';
            
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $resetToken;
            
            $body = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>
                            Password Reset Request
                        </h2>
                        <p>We received a request to reset your password.</p>
                        <p>Click the button below to reset your password:</p>
                        <p style='margin: 25px 0; text-align: center;'>
                            <a href='{$resetLink}' 
                               style='background-color: #3498db; 
                                      color: white; 
                                      padding: 12px 25px; 
                                      text-decoration: none; 
                                      border-radius: 3px;
                                      display: inline-block;'>
                                Reset Password
                            </a>
                        </p>
                        <p style='color: #777; font-size: 0.9em;'>
                            This link will expire in 1 hour for security reasons.
                        </p>
                        <p style='color: #777; font-size: 0.9em;'>
                            If you didn't request this reset, please ignore this email or contact support.
                        </p>
                        <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                            Best regards,<br>
                            The Task Manager Team
                        </p>
                    </div>
                </body>
                </html>
            ";
            
            $this->mailer->Body = $body;
            $this->mailer->send();
            error_log("Password reset email sent successfully to: $userEmail");
            return true;
        } catch (Exception $e) {
            error_log("Failed to send password reset email: " . $e->getMessage());
            return false;
        }
    }

    public function sendTaskNotification($userEmail, $taskTitle, $dueDate) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Task Due Soon - ' . $taskTitle;
            
            $body = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>
                            Task Reminder
                        </h2>
                        <p>Your task is due soon:</p>
                        <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0;'>
                            <h3 style='margin: 0; color: #2c3e50;'>{$taskTitle}</h3>
                            <p style='margin: 10px 0 0 0; color: #666;'>Due: {$dueDate}</p>
                        </div>
                        <p>Please make sure to complete it before the deadline.</p>
                        <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                            Best regards,<br>
                            The Task Manager Team
                        </p>
                    </div>
                </body>
                </html>
            ";
            
            $this->mailer->Body = $body;
            $this->mailer->send();
            error_log("Task notification email sent successfully to: $userEmail");
            return true;
        } catch (Exception $e) {
            error_log("Failed to send task notification email: " . $e->getMessage());
            return false;
        }
    }
}