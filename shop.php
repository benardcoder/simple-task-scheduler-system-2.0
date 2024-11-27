<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch user data
$stmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Define shop items
$shopItems = [
    // Basic Items
    [
        'id' => 1,
        'name' => 'Extra Task Slot',
        'type' => 'item',
        'price' => 200,
        'icon' => 'fas fa-plus-circle',
        'description' => 'Add an extra slot for your daily tasks'
    ],
    [
        'id' => 2,
        'name' => 'Priority Support (30 Days)',
        'type' => 'support',
        'price' => 500,
        'description' => 'Get premium features for 30 days: • Extended task deadlines • Double points for completed tasks • Custom task categories • Email notifications • Priority task sorting • Advanced statistics',
        'duration' => 30,
        'icon' => 'fas fa-star'
    ],

    // Themes
    [
        'id' => 3,
        'name' => 'Forest Theme',
        'type' => 'theme',
        'price' => 400,
        'theme' => 'forest',
        'icon' => 'fas fa-tree',
        'preview' => 'forest-theme.jpg',
        'description' => 'Immerse yourself in a peaceful forest environment'
    ],
    [
        'id' => 4,
        'name' => 'City Lights Theme',
        'type' => 'theme',
        'price' => 400,
        'theme' => 'city',
        'icon' => 'fas fa-city',
        'preview' => 'city-theme.jpg',
        'description' => 'Experience the urban nightlife atmosphere'
    ],
    [
        'id' => 5,
        'name' => 'Desert Sands Theme',
        'type' => 'theme',
        'price' => 400,
        'theme' => 'desert',
        'icon' => 'fas fa-sun',
        'preview' => 'desert-theme.jpg',
        'description' => 'Feel the warmth of the desert landscape'
    ],
    [
        'id' => 6,
        'name' => 'Ocean Waves Theme',
        'type' => 'theme',
        'price' => 400,
        'theme' => 'ocean',
        'icon' => 'fas fa-water',
        'preview' => 'ocean-theme.jpg',
        'description' => 'Dive into a calming ocean environment'
    ],
    [
        'id' => 7,
        'name' => 'Underwater Theme',
        'type' => 'theme',
        'price' => 400,
        'theme' => 'underwater',
        'icon' => 'fas fa-fish',
        'preview' => 'underwater-theme.jpg',
        'description' => 'Explore the depths of the sea'
    ],
    [
        'id' => 8,
        'name' => 'Bubble Theme',
        'type' => 'theme',
        'price' => 400,
        'theme' => 'bubbles',
        'icon' => 'fas fa-circle',
        'preview' => 'bubbles-theme.jpg',
        'description' => 'Float in a playful bubble atmosphere'
    ],

    // Avatars
    [
        'id' => 9,
        'name' => 'Ninja Avatar',
        'type' => 'avatar',
        'price' => 300,
        'image' => 'ninja-avatar.png',
        'description' => 'Stealthy and mysterious'
    ],
    [
        'id' => 10,
        'name' => 'Wizard Avatar',
        'type' => 'avatar',
        'price' => 300,
        'image' => 'wizard-avatar.png',
        'description' => 'Master of magic'
    ],
    [
        'id' => 11,
        'name' => 'Astronaut Avatar',
        'type' => 'avatar',
        'price' => 300,
        'image' => 'astronaut-avatar.png',
        'description' => 'Space explorer'
    ],
    [
        'id' => 12,
        'name' => 'Superhero Avatar',
        'type' => 'avatar',
        'price' => 300,
        'image' => 'superhero-avatar.png',
        'description' => 'Defender of tasks'
    ],
    [
        'id' => 13,
        'name' => 'Pirate Avatar',
        'type' => 'avatar',
        'price' => 300,
        'image' => 'pirate-avatar.png',
        'description' => 'Seeker of treasures'
    ],
    [
        'id' => 14,
        'name' => 'Robot Avatar',
        'type' => 'avatar',
        'price' => 300,
        'image' => 'robot-avatar.png',
        'description' => 'Efficient task processor'
    ]
];

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_item'])) {
    $itemId = $_POST['item_id'];
    $item = array_filter($shopItems, fn($i) => $i['id'] == $itemId);
    $item = reset($item);

    if ($item && $user['points'] >= $item['price']) {
        $pdo->beginTransaction();
        try {
            // Deduct points
            $stmt = $pdo->prepare("UPDATE users SET points = points - ? WHERE id = ?");
            $stmt->execute([$item['price'], $_SESSION['user_id']]);

            // Handle specific item types
            switch($item['type']) {
                case 'avatar':
                    $stmt = $pdo->prepare("UPDATE profile SET avatar = ? WHERE user_id = ?");
                    $stmt->execute([$item['image'], $_SESSION['user_id']]);
                    break;
                    
                case 'theme':
                    $stmt = $pdo->prepare("UPDATE profile SET theme = ? WHERE user_id = ?");
                    $stmt->execute([$item['theme'], $_SESSION['user_id']]);
                    break;
                    
                case 'support':
                    $expiry_date = date('Y-m-d H:i:s', strtotime('+' . $item['duration'] . ' days'));
                    $stmt = $pdo->prepare("
                        INSERT INTO user_subscriptions (user_id, subscription_type, start_date, expiry_date) 
                        VALUES (?, 'priority', NOW(), ?) 
                        ON DUPLICATE KEY UPDATE expiry_date = GREATEST(expiry_date, ?)
                    ");
                    $stmt->execute([$_SESSION['user_id'], $expiry_date, $expiry_date]);
                    break;
                    
                case 'item':
                    // Handle special items (like extra task slots)
                    if ($item['name'] === 'Extra Task Slot') {
                        $stmt = $pdo->prepare("UPDATE profile SET task_slots = task_slots + 1 WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                    }
                    break;
            }

            $pdo->commit();
            setMessage('success', "You have successfully purchased {$item['name']}!");
        } catch (Exception $e) {
            $pdo->rollBack();
            setMessage('error', 'Purchase failed: ' . $e->getMessage());
        }
    } else {
        setMessage('error', 'Not enough points or item not found.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Task Manager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="shop-header">
                <h1><i class="fas fa-store"></i> Shop</h1>
                <div class="user-points">
                    <i class="fas fa-coins"></i>
                    <span><?php echo number_format($user['points']); ?> Points</span>
                </div>
            </div>

            <?php displayMessage(); ?>

            <div class="shop-categories">
                <button class="category-filter active" data-category="all">All Items</button>
                <button class="category-filter" data-category="theme">Themes</button>
                <button class="category-filter" data-category="avatar">Avatars</button>
                <button class="category-filter" data-category="item">Items</button>
            </div>

            <div class="shop-items">
                <?php foreach ($shopItems as $item): ?>
                    <div class="shop-item <?php echo $item['type']; ?>" data-category="<?php echo $item['type']; ?>">
                        <div class="item-info">
                            <div class="item-header">
                                <i class="<?php echo $item['icon'] ?? 'fas fa-tag'; ?>"></i>
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            </div>

                            <?php if ($item['type'] === 'theme'): ?>
                                <div class="theme-preview">
                                    <img src="images/themes/<?php echo $item['preview']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="theme-image">
                                    <p class="theme-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                </div>
                            <?php elseif ($item['type'] === 'avatar'): ?>
                                <div class="avatar-preview">
                                    <img src="images/avatars/<?php echo $item['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="avatar-image">
                                    <p class="avatar-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                </div>
                            <?php endif; ?>

                            <p class="price">
                                <i class="fas fa-coins"></i>
                                <?php echo number_format($item['price']); ?> Points
                            </p>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" name="buy_item" class="btn btn-primary" 
                                    <?php echo $user['points'] < $item['price'] ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart"></i> Buy Now
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <style>
    .shop-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .shop-header h1 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.8em;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .shop-header .user-points {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 1.2em;
        color: #f39c12;
    }

    .shop-categories {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow-x: auto;
    }

    .category-filter {
        padding: 8px 16px;
        border: none;
        border-radius: 20px;
        background: #f8f9fa;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .category-filter.active {
        background: #3498db;
        color: white;
    }

    .shop-items {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .shop-item {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .item-info {
        margin-bottom: 15px;
    }

    .item-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .item-header i {
        color: #3498db;
        font-size: 1.5em;
    }

    .item-info h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.2em;
    }

    .item-info p {
        color: #666;
        margin: 5px 0;
    }

    .theme-preview, .avatar-preview {
        position: relative;
        margin: 15px 0;
        border-radius: 10px;
        overflow: hidden;
    }

    .theme-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
    }

    .avatar-image {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        margin: 0 auto;
        display: block;
    }

    .theme-description, .avatar-description {
        margin-top: 10px;
        color: #666;
        font-size: 0.9em;
        text-align: center;
    }

    .price {
        font-size: 1.2em;
        color: #2c3e50;
        font-weight: bold;
        margin: 15px 0;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .price i {
        color: #f39c12;
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const categoryButtons = document.querySelectorAll('.category-filter');
        const shopItems = document.querySelectorAll('.shop-item');

        categoryButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                button.classList.add('active');

                const category = button.dataset.category;
                
                // Show/hide items based on category
                shopItems.forEach(item => {
                    if (category === 'all' || item.dataset.category === category) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    });
    </script>
</body>
</html>