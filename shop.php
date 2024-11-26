<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user's points
$stmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userPoints = $stmt->fetchColumn();

// Get shop items
$shopItems = getShopItems($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Task Manager</title>
    <link rel="stylesheet" href="shop.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
    <?php include 'sidebar.php'; ?> 
        
        <div class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-store"></i> Shop</h1>
                <div class="user-points">
                    <i class="fas fa-coins"></i>
                    <span><?php echo number_format($userPoints); ?> Points</span>
                </div>
            </div>

            <?php displayMessage(); ?>

            <div class="shop-categories">
                <button class="category-btn active" data-category="all">All Items</button>
                <button class="category-btn" data-category="themes">Themes</button>
                <button class="category-btn" data-category="badges">Badges</button>
                <button class="category-btn" data-category="backgrounds">Backgrounds</button>
            </div>

            <div class="shop-items">
                <?php if (!empty($shopItems)): ?>
                    <?php foreach ($shopItems as $item): ?>
                        <div class="shop-item" data-category="<?php echo htmlspecialchars($item['category']); ?>">
                            <div class="item-image">
                                <img src="images/shop/<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                <div class="item-price">
                                    <i class="fas fa-coins"></i>
                                    <span><?php echo number_format($item['price']); ?></span>
                                </div>
                                <button onclick="purchaseItem(<?php echo $item['id']; ?>, <?php echo $item['price']; ?>)"
                                        class="btn-purchase <?php echo $userPoints < $item['price'] ? 'disabled' : ''; ?>"
                                        <?php echo $userPoints < $item['price'] ? 'disabled' : ''; ?>>
                                    <?php echo $userPoints < $item['price'] ? 'Not Enough Points' : 'Purchase'; ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-store-slash"></i>
                        <p>No items available in the shop right now.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="shop.js"></script>
</body>
</html>