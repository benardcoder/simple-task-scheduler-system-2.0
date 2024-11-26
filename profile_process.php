<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $newPassword = trim($_POST['new_password']);

    try {
        $pdo->beginTransaction();

        // Update username and email
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$username, $email, $userId]);

        // Update password if provided
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
        }

        $pdo->commit();
        $_SESSION['username'] = $username;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Error updating profile'
        ]);
    }
}
?>