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

    // Get current user data
    $stmt = $pdo->prepare("
        SELECT u.id, u.points, u.last_reward_claim, u.reward_streak,
               p.points as profile_points
        FROM users u
        LEFT JOIN profile p ON u.id = p.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if enough time has passed
    $lastClaim = strtotime($user['last_reward_claim'] ?? '2000-01-01');
    $now = time();
    if (($now - $lastClaim) < 86400) {
        $timeLeft = 86400 - ($now - $lastClaim);
        $hoursLeft = floor($timeLeft / 3600);
        $minutesLeft = floor(($timeLeft % 3600) / 60);
        throw new Exception("Please wait {$hoursLeft}h {$minutesLeft}m before your next claim");
    }

    $oldPoints = (int)$user['points'];
    $currentStreak = (int)$user['reward_streak'];
    $streakBroken = ($now - $lastClaim) >= (48 * 3600);
    
    if ($streakBroken) {
        $newStreak = 1;
    } else {
        $newStreak = $currentStreak + 1;
        if ($newStreak > 7) $newStreak = 1;
    }

    // Calculate points based on streak
    $pointsToAdd = match($newStreak) {
        2 => 200,
        3 => 300,
        4 => 400,
        5 => 500,
        6 => 600,
        7 => 1000,
        default => 100
    };

    // Update both tables in a transaction
    $pdo->beginTransaction();

    try {
        // Update users table (shop points)
        $updateStmt = $pdo->prepare("
            UPDATE users 
            SET points = points + ?,
                last_reward_claim = NOW(),
                reward_streak = ?
            WHERE id = ?
        ");
        $result = $updateStmt->execute([$pointsToAdd, $newStreak, $_SESSION['user_id']]);

        // Update or insert into profile table (profile points)
        $profileStmt = $pdo->prepare("
            INSERT INTO profile (user_id, points) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE points = points + ?
        ");
        $profileStmt->execute([$_SESSION['user_id'], $pointsToAdd, $pointsToAdd]);

        $pdo->commit();

        // Get updated totals
        $stmt = $pdo->prepare("
            SELECT u.points as shop_points, COALESCE(p.points, 0) as profile_points
            FROM users u
            LEFT JOIN profile p ON u.id = p.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $updatedPoints = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => "Reward claimed successfully! You earned {$pointsToAdd} points!",
            'newPoints' => (int)$updatedPoints['shop_points'],
            'profilePoints' => (int)$updatedPoints['profile_points'],
            'pointsAdded' => $pointsToAdd,
            'newStreak' => $newStreak
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Test reward error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>