<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'claim_reward') {
        try {
            $pdo->beginTransaction();

            // Get user's current streak and last claim time
            $stmt = $pdo->prepare("
                SELECT last_reward_claim, reward_streak 
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $userData = $stmt->fetch();

            $lastClaim = strtotime($userData['last_reward_claim'] ?? '2000-01-01');
            $now = time();

            // Check if enough time has passed (24 hours)
            if (($now - $lastClaim) < 86400) {
                throw new Exception('You must wait 24 hours between claims');
            }

            // Check if streak is broken (48 hours)
            $streak = ($now - $lastClaim) >= (48 * 3600) ? 0 : ($userData['reward_streak'] ?? 0);
            $newStreak = $streak + 1;
            if ($newStreak > 7) $newStreak = 1; // Reset after week completion

            // Calculate reward points based on streak
            $rewards = [
                1 => 100,
                2 => 200,
                3 => 300,
                4 => 400,
                5 => 500,
                6 => 600,
                7 => 1000
            ];
            $points = $rewards[$newStreak];

            // Update user's points and streak
            $stmt = $pdo->prepare("
                UPDATE users 
                SET points = points + ?,
                    last_reward_claim = NOW(),
                    reward_streak = ?
                WHERE id = ?
            ");
            $stmt->execute([$points, $newStreak, $_SESSION['user_id']]);

            // Log the reward
            $stmt = $pdo->prepare("
                INSERT INTO reward_logs (user_id, points, type) 
                VALUES (?, ?, 'daily')
            ");
            $stmt->execute([$_SESSION['user_id'], $points]);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'points' => $points,
                'newStreak' => $newStreak
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
?>