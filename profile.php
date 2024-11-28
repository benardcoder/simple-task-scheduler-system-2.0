<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'update_points.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Refresh points from database
$points = getUserPoints($pdo, $_SESSION['user_id']);
$_SESSION['user_points'] = $points;

// Fetch user data
$stmt = $pdo->prepare("
    SELECT 
        u.*,
        COUNT(t.id) as total_tasks,
        SUM(t.completed = 1) as completed_tasks
    FROM users u
    LEFT JOIN tasks t ON u.id = t.user_id
    WHERE u.id = ?
    GROUP BY u.id
");
$stmt->execute([$_SESSION['user_id']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate completion rate
$completionRate = $userData['total_tasks'] > 0 
    ? round(($userData['completed_tasks'] / $userData['total_tasks']) * 100) 
    : 0;

// Get recent activity
$stmt = $pdo->prepare("
    SELECT 
        title,
        completed,
        completed_date,
        priority,
        difficulty
    FROM tasks 
    WHERE user_id = ? 
    ORDER BY 
        CASE 
            WHEN completed_date IS NOT NULL THEN completed_date 
            ELSE created_at 
        END DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
<body class="theme-<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light'; ?>">
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="profile-header">
                <h1><i class="fas fa-user"></i> My Profile</h1>
                <div class="points-display">
                    <i class="fas fa-star"></i>
                    Points: <span id="points-value"><?php echo $_SESSION['user_points'] ?? 0; ?></span>
                </div>
            </div>

            <div class="profile-container">
                <div class="profile-section user-info">
                    <h2>User Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Username</label>
                            <p><?php echo htmlspecialchars($userData['username']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Email</label>
                            <p><?php echo htmlspecialchars($userData['email']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Total Points</label>
                            <p><?php echo $userData['points']; ?></p>
                        </div>
                        <div class="info-item">
                            <label>Tasks Completed</label>
                            <p><?php echo $userData['completed_tasks']; ?> / <?php echo $userData['total_tasks']; ?></p>
                        </div>
                        <div class="info-item">
                            <label>Completion Rate</label>
                            <p><?php echo $completionRate; ?>%</p>
                        </div>
                    </div>
                </div>

                <div class="profile-section recent-activity">
                    <h2>Recent Activity</h2>
                    <div class="activity-list">
                        <?php if (empty($recentActivity)): ?>
                            <p class="no-activity">No recent activity</p>
                        <?php else: ?>
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas <?php echo $activity['completed'] ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                                    </div>
                                    <div class="activity-details">
                                        <h3><?php echo htmlspecialchars($activity['title']); ?></h3>
                                        <p>
                                            <?php if ($activity['completed']): ?>
                                                Completed on <?php echo date('M d, Y', strtotime($activity['completed_date'])); ?>
                                            <?php else: ?>
                                                In progress
                                            <?php endif; ?>
                                        </p>
                                        <div class="task-meta">
                                            <span class="priority <?php echo $activity['priority']; ?>">
                                                <?php echo ucfirst($activity['priority']); ?>
                                            </span>
                                            <span class="difficulty <?php echo $activity['difficulty']; ?>">
                                                <?php echo ucfirst($activity['difficulty']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updatePointsDisplay(points) {
        const pointsDisplays = document.querySelectorAll('.points-display span');
        pointsDisplays.forEach(display => {
            if (display) {
                display.textContent = points;
            }
        });
    }

    function refreshPoints() {
        fetch('get_points.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updatePointsDisplay(data.points);
                    localStorage.setItem('userPoints', data.points);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    window.addEventListener('storage', function(e) {
        if (e.key === 'userPoints') {
            updatePointsDisplay(e.newValue);
        }
    });

    setInterval(refreshPoints, 30000);
    refreshPoints();
    </script>

    <style>
    .profile-container {
        padding: 20px;
        display: grid;
        gap: 20px;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }

    .profile-section {
        background: var(--bg-secondary);
        border-radius: 10px;
        padding: 20px;
    }

    .info-grid {
        display: grid;
        gap: 15px;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }

    .info-item {
        padding: 10px;
        background: var(--bg-primary);
        border-radius: 5px;
    }

    .info-item label {
        color: var(--text-secondary);
        font-size: 0.9em;
    }

    .info-item p {
        margin: 5px 0 0 0;
        color: var(--text-primary);
        font-size: 1.1em;
    }

    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .activity-item {
        display: flex;
        gap: 15px;
        padding: 15px;
        background: var(--bg-primary);
        border-radius: 5px;
    }

    .activity-icon {
        font-size: 1.5em;
        color: var(--text-primary);
    }

    .activity-details h3 {
        margin: 0;
        color: var(--text-primary);
    }

    .activity-details p {
        margin: 5px 0;
        color: var(--text-secondary);
    }

    .task-meta {
        display: flex;
        gap: 10px;
        margin-top: 5px;
    }

    .priority, .difficulty {
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 0.8em;
    }

    .priority.high { background: #ff4444; color: white; }
    .priority.medium { background: #ffbb33; color: black; }
    .priority.low { background: #00C851; color: white; }

    .difficulty.hard { background: #ff4444; color: white; }
    .difficulty.medium { background: #ffbb33; color: black; }
    .difficulty.easy { background: #00C851; color: white; }
    </style>
</body>
</html>