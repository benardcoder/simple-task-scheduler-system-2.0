<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch user data and profile data
$stmt = $pdo->prepare("
    SELECT u.username, u.email, u.points, p.avatar, p.theme, p.task_slots, p.join_date 
    FROM users u 
    LEFT JOIN profile p ON u.id = p.user_id 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$userData = $stmt->fetch();

// Set default avatar if none is set
$avatarImage = $userData['avatar'] ? "images/avatars/{$userData['avatar']}" : "images/avatars/default-avatar.png";

// Calculate user statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks
    FROM tasks 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Calculate completion rate
$completionRate = $stats['total_tasks'] > 0 
    ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) 
    : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Task Manager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="profile-header">
                <h1><i class="fas fa-user-circle"></i> My Profile</h1>
            </div>

            <?php displayMessage(); ?>

            <div class="profile-grid">
                <!-- Avatar and Basic Info Section -->
                <div class="profile-card avatar-card">
                    <div class="avatar-section">
                        <img src="<?php echo htmlspecialchars($avatarImage); ?>" alt="User Avatar" class="avatar-image">
                        <h2><?php echo htmlspecialchars($userData['username']); ?></h2>
                        <p class="member-since">Member since <?php echo date('F Y', strtotime($userData['join_date'])); ?></p>
                    </div>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <i class="fas fa-coins"></i>
                            <span class="stat-value"><?php echo number_format($userData['points']); ?></span>
                            <span class="stat-label">Points</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-tasks"></i>
                            <span class="stat-value"><?php echo $userData['task_slots']; ?></span>
                            <span class="stat-label">Task Slots</span>
                        </div>
                    </div>
                </div>

                <!-- Task Statistics Section -->
                <div class="profile-card stats-card">
                    <h3><i class="fas fa-chart-bar"></i> Task Statistics</h3>
                    <div class="stats-grid">
                        <div class="stat-box">
                            <span class="stat-number"><?php echo $stats['total_tasks']; ?></span>
                            <span class="stat-label">Total Tasks</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-number"><?php echo $stats['completed_tasks']; ?></span>
                            <span class="stat-label">Completed</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-number"><?php echo $completionRate; ?>%</span>
                            <span class="stat-label">Completion Rate</span>
                        </div>
                    </div>
                </div>

                <!-- Account Details Section -->
                <div class="profile-card details-card">
                    <h3><i class="fas fa-info-circle"></i> Account Details</h3>
                    <div class="account-details">
                        <div class="detail-item">
                            <span class="detail-label">Username:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($userData['username']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($userData['email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Current Theme:</span>
                            <span class="detail-value"><?php echo ucfirst($userData['theme'] ?? 'Default'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .profile-header {
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .profile-header h1 {
        margin: 0;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .profile-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .avatar-card {
        text-align: center;
    }

    .avatar-section {
        margin-bottom: 20px;
    }

    .avatar-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 15px;
        border: 4px solid #3498db;
    }

    .avatar-section h2 {
        margin: 10px 0;
        color: #2c3e50;
    }

    .member-since {
        color: #7f8c8d;
        font-size: 0.9em;
    }

    .profile-stats {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-item i {
        font-size: 1.5em;
        color: #3498db;
        margin-bottom: 5px;
    }

    .stat-value {
        display: block;
        font-size: 1.2em;
        font-weight: bold;
        color: #2c3e50;
    }

    .stat-label {
        color: #7f8c8d;
        font-size: 0.9em;
    }

    .stats-card h3, .details-card h3 {
        margin-top: 0;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .stat-box {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .stat-number {
        display: block;
        font-size: 1.5em;
        font-weight: bold;
        color: #2c3e50;
    }

    .account-details {
        margin-top: 20px;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: #7f8c8d;
    }

    .detail-value {
        color: #2c3e50;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        }
    }
    </style>
</body>
</html>