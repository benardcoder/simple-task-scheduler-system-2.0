<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'update_points.php';  // Now safe to include

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch fresh points from database
$stmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$points = $stmt->fetchColumn();
$_SESSION['user_points'] = $points; // Update session with current points

// Fetch shop items
$stmt = $pdo->prepare("SELECT * FROM shop_items WHERE available = TRUE ORDER BY cost ASC");
$stmt->execute();
$shopItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's purchased items
$stmt = $pdo->prepare("SELECT item_id FROM user_purchases WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$purchasedItems = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Group items by category
$stmt = $pdo->prepare("
    SELECT * FROM shop_items 
    WHERE available = TRUE 
    ORDER BY category, cost
");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$itemsByCategory = [];
foreach ($items as $item) {
    $itemsByCategory[$item['category']][] = $item;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Task Manager</title>
    <link rel="stylesheet" href="shop.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="themes.css">
</head>
<body class="theme-<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light'; ?>">
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="shop-header">
                <h1><i class="fas fa-store"></i> Shop</h1>
                <div class="points-display">
                    <i class="fas fa-star"></i>
                    Points: <span id="points-value"><?php echo $_SESSION['user_points'] ?? 0; ?></span>
                </div>
            </div>

            <div class="shop-container">
                <?php if (empty($shopItems)): ?>
                    <div class="no-items">
                        <i class="fas fa-store-slash"></i>
                        <p>No items available in the shop yet!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($shopItems as $item): ?>
                        <div class="shop-item <?php echo in_array($item['id'], $purchasedItems) ? 'purchased' : ''; ?>">
                            <div class="item-image">
                                <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                <div class="item-cost">
                                    <i class="fas fa-star"></i>
                                    <?php echo $item['cost']; ?> points
                                </div>
                            </div>
                            <?php if (in_array($item['id'], $purchasedItems)): ?>
                                <button class="btn btn-success" disabled>
                                    <i class="fas fa-check"></i> Purchased
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary purchase-btn" 
                                        onclick="purchaseItem(<?php echo $item['id']; ?>, <?php echo $item['cost']; ?>)"
                                        <?php echo ($_SESSION['user_points'] < $item['cost']) ? 'disabled' : ''; ?>>
                                    Purchase
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
                    const pointsValue = parseInt(data.points);
                    updatePointsDisplay(pointsValue);
                    
                    // Update purchase buttons based on new points
                    document.querySelectorAll('.purchase-btn').forEach(button => {
                        const cost = parseInt(button.closest('.shop-item').querySelector('.item-cost').textContent);
                        button.disabled = pointsValue < cost;
                    });
                    
                    localStorage.setItem('userPoints', pointsValue);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function purchaseItem(itemId, itemCost) {
        if (confirm('Are you sure you want to purchase this item?')) {
            fetch('purchase_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update points
                    updatePointsDisplay(data.remainingPoints);
                    localStorage.setItem('userPoints', data.remainingPoints);
                    
                    // Update UI
                    const itemElement = document.querySelector(`[onclick="purchaseItem(${itemId}, ${itemCost})"]`)
                        .closest('.shop-item');
                    itemElement.classList.add('purchased');
                    itemElement.querySelector('button').outerHTML = `
                        <button class="btn btn-success" disabled>
                            <i class="fas fa-check"></i> Purchased
                        </button>
                    `;
                    
                    showNotification('Item purchased successfully!', 'success');
                } else {
                    showNotification(data.message || 'Error purchasing item', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error purchasing item', 'error');
            });
        }
    }

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.classList.add('show'), 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    window.addEventListener('storage', function(e) {
        if (e.key === 'userPoints') {
            updatePointsDisplay(e.newValue);
        }
    });

    document.addEventListener('DOMContentLoaded', refreshPoints);
    setInterval(refreshPoints, 5000);
    </script>

    <style>
    .shop-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .shop-item {
        background: var(--bg-secondary);
        border-radius: 10px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        transition: transform 0.2s;
    }

    .shop-item:hover {
        transform: translateY(-5px);
    }

    .shop-item.purchased {
        opacity: 0.7;
    }

    .item-image {
        font-size: 2em;
        color: var(--text-primary);
        text-align: center;
    }

    .item-details h3 {
        margin: 0;
        color: var(--text-primary);
    }

    .item-details p {
        color: var(--text-secondary);
        margin: 10px 0;
    }

    .item-cost {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #ffd700;
        font-weight: bold;
    }

    .btn-success {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: not-allowed;
        opacity: 0.8;
    }

    .purchase-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 20px;
        border-radius: 5px;
        background: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transform: translateX(120%);
        transition: transform 0.3s ease;
        z-index: 1000;
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification.success {
        background: #4CAF50;
        color: white;
    }

    .notification.error {
        background: #f44336;
        color: white;
    }

    .points-display {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 1.2em;
        color: var(--text-primary);
    }

    .points-display i {
        color: #ffd700;
    }
    </style>
</body>
</html>