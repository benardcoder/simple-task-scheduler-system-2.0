<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user profile data
$profile = getUserProfile($pdo, $_SESSION['user_id']);
$taskStats = getTaskStats($pdo, $_SESSION['user_id']);

// Get user's badges/items
$stmt = $pdo->prepare("
    SELECT si.* FROM shop_items si 
    JOIN user_inventory ui ON si.id = ui.item_id 
    WHERE ui.user_id = ? AND si.category = 'badges'
");
$stmt->execute([$_SESSION['user_id']]);
$badges = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Task Manager</title>
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
    
    <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <div class="profile-header">
                <h1><i class="fas fa-user-circle"></i> My Profile</h1>
                <button id="editProfileBtn" class="btn-primary">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>

            <?php displayMessage(); ?>

            <div class="profile-container">
                <!-- Profile Info Section -->
                <div class="profile-section">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle fa-5x"></i>
                        </div>
                        <div class="profile-details">
                            <h2><?php echo htmlspecialchars($profile['username']); ?></h2>
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($profile['email']); ?></p>
                            <p><i class="fas fa-calendar-alt"></i> Member since: <?php echo formatDate($profile['created_at']); ?></p>
                            <p><i class="fas fa-coins"></i> Points: <?php echo number_format($profile['points'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Statistics Section -->
                <div class="profile-section">
                    <h3><i class="fas fa-chart-bar"></i> Task Statistics</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $taskStats['total']; ?></div>
                            <div class="stat-label">Total Tasks</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $taskStats['completed']; ?></div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $taskStats['in_progress']; ?></div>
                            <div class="stat-label">In Progress</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">
                                <?php 
                                    echo $taskStats['total'] > 0 
                                        ? round(($taskStats['completed'] / $taskStats['total']) * 100) 
                                        : 0; 
                                ?>%
                            </div>
                            <div class="stat-label">Completion Rate</div>
                        </div>
                    </div>
                </div>

                <!-- Badges Section -->
                <div class="profile-section">
                    <h3><i class="fas fa-award"></i> Badges</h3>
                    <div class="badges-grid">
                        <?php if (!empty($badges)): ?>
                            <?php foreach ($badges as $badge): ?>
                                <div class="badge-item" title="<?php echo htmlspecialchars($badge['name']); ?>">
                                    <img src="images/shop/<?php echo htmlspecialchars($badge['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($badge['name']); ?>">
                                    <span><?php echo htmlspecialchars($badge['name']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-badges">No badges yet. Visit the shop to get some!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Profile</h2>
            <form id="editProfileForm" action="profile_process.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($profile['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <script src="profile.js"></script>
</body>
</html>