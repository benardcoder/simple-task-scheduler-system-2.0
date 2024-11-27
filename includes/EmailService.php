<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require dirname(__DIR__) . '/vendor/autoload.php';

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mailer->SMTPDebug = 0;                      
            $this->mailer->isSMTP();                                            
            $this->mailer->Host       = 'smtp.gmail.com';                     
            $this->mailer->SMTPAuth   = true;                                   
            $this->mailer->Username   = 'benardonyango@kabarak.ac.ke';         // Your Gmail address    
            $this->mailer->Password   = 'jtei isif rivz eeat';        // Your App Password                               
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
            $this->mailer->Port       = 465;                                    

            $this->mailer->setFrom('benardonyango@kabarak.ac.ke', 'Task Manager');
            
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function setDebugMode($enabled = true) {
        $this->mailer->SMTPDebug = $enabled ? SMTP::DEBUG_SERVER : 0;
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

    public function sendPasswordResetEmail($userEmail, $username, $newPassword) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset - Task Manager';
            
            $body = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; border-radius: 10px;'>
                        <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; text-align: center;'>
                            Password Reset
                        </h2>
                        <p>Dear {$username},</p>
                        <p>We received a request to reset your password. Here is your new temporary password:</p>
                        <div style='background-color: #ffffff; 
                                  padding: 15px; 
                                  border-left: 4px solid #3498db; 
                                  margin: 20px 0;
                                  text-align: center;
                                  border-radius: 5px;
                                  box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                            <code style='font-size: 1.2em; color: #2c3e50; font-weight: bold;'>{$newPassword}</code>
                        </div>
                        <div style='background-color: #fff4f4; 
                                  padding: 15px; 
                                  border-radius: 5px; 
                                  margin: 20px 0;'>
                            <p style='color: #e74c3c; margin: 0;'>
                                <strong>⚠️ Important Security Notes:</strong>
                            </p>
                            <ul style='color: #e74c3c; margin: 10px 0;'>
                                <li>Please change this password immediately after logging in</li>
                                <li>Never share this password with anyone</li>
                                <li>Our team will never ask for your password</li>
                            </ul>
                        </div>
                        <p>To use this password:</p>
                        <ol style='margin: 15px 0;'>
                            <li>Go to the login page</li>
                            <li>Enter your email address</li>
                            <li>Enter this temporary password</li>
                            <li>Update your password in account settings</li>
                        </ol>
                        <p>If you didn't request this password reset, please contact our support team immediately.</p>
                        <p style='margin-top: 30px; 
                                 padding-top: 20px; 
                                 border-top: 1px solid #eee; 
                                 text-align: center; 
                                 color: #666;'>
                            Best regards,<br>
                            <strong>The Task Manager Team</strong>
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