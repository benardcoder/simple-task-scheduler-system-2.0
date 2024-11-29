<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'update_points.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    try {
        $pdo->beginTransaction();
        
        // Get item details with category and effect
        $stmt = $pdo->prepare("
            SELECT * FROM shop_items 
            WHERE id = ? AND available = TRUE
        ");
        $stmt->execute([$_POST['item_id']]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$item) {
            throw new Exception('Item not found or not available');
        }
        
        // Check if user already owns the item
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM user_purchases 
            WHERE user_id = ? AND item_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $_POST['item_id']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('You already own this item');
        }
        
        // Check if user has enough points
        $currentPoints = getUserPoints($pdo, $_SESSION['user_id']);
        if ($currentPoints < $item['cost']) {
            throw new Exception('Not enough points');
        }
        
        // Create purchase record with timestamp
        $stmt = $pdo->prepare("
            INSERT INTO user_purchases 
            (user_id, item_id, purchase_date, used) 
            VALUES (?, ?, NOW(), FALSE)
        ");
        $stmt->execute([
            $_SESSION['user_id'], 
            $_POST['item_id']
        ]);
        
        // Deduct points
        $newPoints = $currentPoints - $item['cost'];
        $stmt = $pdo->prepare("UPDATE users SET points = ? WHERE id = ?");
        $stmt->execute([$newPoints, $_SESSION['user_id']]);
        
        $_SESSION['user_points'] = $newPoints;
        
        // If it's an instant-use item, apply effects immediately
        if ($item['instant_use']) {
            switch($item['category']) {
                case 'Task Themes':
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET task_theme = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$item['effect'], $_SESSION['user_id']]);
                    break;
                    
                case 'Profile Backgrounds':
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET profile_background = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$item['effect'], $_SESSION['user_id']]);
                    break;
                    
                case 'Point Multipliers':
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET points_multiplier = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$item['effect'], $_SESSION['user_id']]);
                    break;
            }
            
            // Mark item as used if it's instant-use
            $stmt = $pdo->prepare("
                UPDATE user_purchases 
                SET used = TRUE, used_date = NOW() 
                WHERE user_id = ? AND item_id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $_POST['item_id']]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Item purchased successfully!',
            'remainingPoints' => $newPoints,
            'category' => $item['category'],
            'instant_use' => $item['instant_use'],
            'redirect' => $item['instant_use'] ? getRedirectUrl($item['category']) : 'purchased_items.php'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

function getRedirectUrl($category) {
    switch($category) {
        case 'Task Themes':
            return 'my_tasks.php';
        case 'Profile Backgrounds':
            return 'profile.php';
        default:
            return 'purchased_items.php';
    }
}
?>