<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch user data
$stmt = $pdo->prepare("SELECT last_claimed, points FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Check if the user can claim today's reward
$canClaim = false;
$now = time();
$lastClaimTime = strtotime($user['last_claimed']);

// Check if 24 hours have passed since last claim
if (!$user['last_claimed'] || ($now - $lastClaimTime) >= 86400) {  // 86400 seconds = 24 hours
    $canClaim = true;
}

// Handle reward claim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_reward'])) {
    if ($canClaim) {
        $rewardPoints = 50; // Set the reward points
        $currentDateTime = date('Y-m-d H:i:s'); // Store full timestamp instead of just date
        $stmt = $pdo->prepare("UPDATE users SET points = points + ?, last_claimed = ? WHERE id = ?");
        $stmt->execute([$rewardPoints, $currentDateTime, $_SESSION['user_id']]);
        setMessage('success', "You have successfully claimed your daily reward of $rewardPoints points!");
        header("Location: daily_rewards.php");
        exit();
    } else {
        setMessage('error', 'You can claim your next reward in ' . ceil(((86400 - ($now - $lastClaimTime)) / 3600)) . ' hours.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Rewards - Task Manager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="themes.css">
</head>
<body class="theme-<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light'; ?>">
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="rewards-header">
                <h1><i class="fas fa-gift"></i> Daily Rewards</h1>
                <div class="user-points">
                    <i class="fas fa-coins"></i>
                    <span><?php echo number_format($user['points']); ?> Points</span>
                </div>
            </div>

            <?php displayMessage(); ?>

            <div class="rewards-content">
                <p>Claim your daily reward to earn extra points!</p>
                <form method="POST">
                    <button type="submit" name="claim_reward" class="btn btn-primary" <?php echo !$canClaim ? 'disabled' : ''; ?>>
                        <i class="fas fa-hand-holding-usd"></i> Claim Reward
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
    .rewards-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .rewards-header h1 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.8em;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .rewards-header .user-points {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 1.2em;
        color: #f39c12;
    }

    .rewards-content {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-align: center;
    }

    .rewards-content p {
        font-size: 1.2em;
        color: #666;
        margin-bottom: 20px;
    }

    .btn-primary {
        background: #3498db;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        font-size: 1em;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: background 0.3s ease;
    }

    .btn-primary:disabled {
        background: #bdc3c7;
        cursor: not-allowed;
    }

    .btn-primary:hover:not(:disabled) {
        background: #2980b9;
    }
    </style>
</body>
</html>