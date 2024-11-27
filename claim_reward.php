<?php
session_start();
require_once 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

try {
    $pdo->beginTransaction();

    // First get current user data
    $stmt = $pdo->prepare("SELECT points, reward_streak FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate new points and streak
    $pointsToAdd = 100;
    $currentStreak = (int)($user['reward_streak'] ?? 0);
    $newStreak = $currentStreak + 1;
    if ($newStreak > 7) $newStreak = 1;

    // Update user table
    $updateUser = $pdo->prepare("
        UPDATE users 
        SET 
            points = points + ?,
            last_reward_claim = NOW(),
            reward_streak = ?
        WHERE id = ?
    ");
    
    $updateUser->execute([$pointsToAdd, $newStreak, $_SESSION['user_id']]);

    // Log the reward in reward_logs table
    $logReward = $pdo->prepare("
        INSERT INTO reward_logs 
        (user_id, points) 
        VALUES (?, ?)
    ");
    
    $logReward->execute([$_SESSION['user_id'], $pointsToAdd]);

    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "You've earned {$pointsToAdd} points!",
        'newStreak' => $newStreak
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error in claim_reward.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>