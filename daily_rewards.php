<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user's last reward claim time and streak
$stmt = $pdo->prepare("
    SELECT points, last_reward_claim, reward_streak 
    FROM users 
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if reward is available
$lastClaim = strtotime($userData['last_reward_claim'] ?? '2000-01-01');
$now = time();
$canClaim = ($now - $lastClaim) >= 86400; // 24 hours in seconds

// Calculate streak
$streakBroken = ($now - $lastClaim) >= (48 * 3600); // 48 hours for streak break
if ($streakBroken) {
    $streak = 0;
} else {
    $streak = $userData['reward_streak'] ?? 0;
}

// Define rewards for each day
$rewards = [
    1 => ['points' => 100, 'description' => 'Daily Login Bonus'],
    2 => ['points' => 200, 'description' => 'Streak Bonus - Day 2'],
    3 => ['points' => 300, 'description' => 'Streak Bonus - Day 3'],
    4 => ['points' => 400, 'description' => 'Streak Bonus - Day 4'],
    5 => ['points' => 500, 'description' => 'Streak Bonus - Day 5'],
    6 => ['points' => 600, 'description' => 'Streak Bonus - Day 6'],
    7 => ['points' => 1000, 'description' => 'Weekly Streak Complete!'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Rewards - Task Manager</title>
    <link rel="stylesheet" href="daily_rewards.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        
        
        <div class="main-content">
            <div class="rewards-header">
                <h1><i class="fas fa-gift"></i> Daily Rewards</h1>
                <div class="user-points">
                    <i class="fas fa-coins"></i>
                    <span><?php echo number_format($userData['points'] ?? 0); ?> Points</span>
                </div>
            </div>

            <?php displayMessage(); ?>

            <div class="rewards-container">
                <div class="streak-info">
                    <div class="streak-count">
                        <i class="fas fa-fire"></i>
                        <span>Day <?php echo $streak + 1; ?></span>
                    </div>
                    <?php if ($canClaim): ?>
                        <div class="reward-status available">
                            <i class="fas fa-check-circle"></i> Reward Available!
                        </div>
                    <?php else: ?>
                        <div class="reward-status unavailable">
                            <i class="fas fa-clock"></i> 
                            Next reward in: <span id="countdown"></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="rewards-grid">
                    <?php for ($day = 1; $day <= 7; $day++): ?>
                        <div class="reward-card <?php 
                            echo $day <= $streak ? 'claimed' : '';
                            echo $day == $streak + 1 && $canClaim ? 'available' : '';
                        ?>">
                            <div class="reward-day">Day <?php echo $day; ?></div>
                            <div class="reward-icon">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div class="reward-points">
                                <i class="fas fa-coins"></i>
                                <?php echo number_format($rewards[$day]['points']); ?>
                            </div>
                            <div class="reward-status">
                                <?php if ($day < $streak + 1): ?>
                                    <i class="fas fa-check-circle"></i> Claimed
                                <?php elseif ($day == $streak + 1 && $canClaim): ?>
                                    <button onclick="claimReward()" class="claim-btn">
                                        Claim Reward
                                    </button>
                                <?php else: ?>
                                    <i class="fas fa-lock"></i> Locked
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="rewards-info">
                    <h3><i class="fas fa-info-circle"></i> How it works</h3>
                    <ul>
                        <li>Log in daily to claim your rewards</li>
                        <li>Keep your streak going for bigger rewards</li>
                        <li>Miss a day and your streak resets</li>
                        <li>Complete 7 days for a massive bonus!</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set the next claim time for countdown
        const nextClaimTime = <?php echo $lastClaim + 86400; ?> * 1000;
    </script>
    <script src="daily_rewards.js"></script>
</body>
</html>