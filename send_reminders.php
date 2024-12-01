<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add basic output
echo "Script starting...<br>";

require_once 'config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Test database connection
try {
    echo "Testing database connection...<br>";
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE reminders_enabled = true");
    $count = $stmt->fetchColumn();
    echo "Found {$count} users with reminders enabled<br>";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

// Rest of your existing code...

try {
    // Get tasks that need reminders
    $query = "
        SELECT 
            t.id as task_id,
            t.title,
            t.deadline,
            t.priority,
            t.status,
            u.email,
            u.id as user_id
        FROM tasks t
        JOIN users u ON t.user_id = u.id
        WHERE 
            u.reminders_enabled = true 
            AND t.status != 'completed'
            AND t.completed = 0
            AND t.deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
            AND t.reminder_sent = 0
    ";
    
    $stmt = $pdo->query($query);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($tasks) . " tasks needing reminders<br>";
    
    if (count($tasks) > 0) {
        foreach ($tasks as $task) {
            echo "Processing task: {$task['title']} for user: {$task['email']}<br>";
            
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->SMTPDebug = 2; // Enable verbose debug output
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USER;
                $mail->Password = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = SMTP_PORT;
                
                $mail->setFrom(SMTP_FROM_EMAIL, 'Task Manager');
                $mail->addAddress($task['email']);
                
                $mail->isHTML(true);
                $mail->Subject = 'Task Reminder: ' . $task['title'];
                
                // Enhanced email body with more task details
                $body = "
                    <h2>Task Reminder</h2>
                    <p>This is a reminder for your task: <strong>{$task['title']}</strong></p>
                    <p>Deadline: " . date('Y-m-d H:i', strtotime($task['deadline'])) . "</p>
                    <p>Priority: {$task['priority']}</p>
                    <p>Status: {$task['status']}</p>
                ";
                
                $mail->Body = $body;
                
                if($mail->send()) {
                    echo "Email sent successfully for task ID: {$task['task_id']}<br>";
                    
                    // Update reminder_sent status
                    $updateStmt = $pdo->prepare("
                        UPDATE tasks 
                        SET reminder_sent = 1 
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$task['task_id']]);
                }
                
            } catch (Exception $e) {
                echo "Failed to send email for task ID {$task['task_id']}: {$mail->ErrorInfo}<br>";
            }
        }
    } else {
        echo "No tasks need reminders at this time<br>";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

echo "Script completed.<br>";