<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json; charset=utf-8');

// Log the raw input
error_log('Raw input: ' . file_get_contents('php://input'));

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
// Log the decoded data
error_log('Decoded data: ' . print_r($data, true));

$purchaseId = $data['purchase_id'] ?? null;
$category = $data['category'] ?? null;

// Log the extracted values
error_log('Purchase ID: ' . $purchaseId);
error_log('Category: ' . $category);

if (!$purchaseId || !$category) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request',
        'debug' => [
            'purchase_id' => $purchaseId,
            'category' => $category,
            'raw_data' => $data
        ]
    ]);
    exit();
}

try {
    $pdo->beginTransaction();

    // First, get the item details to ensure it exists and is unused
    $stmt = $pdo->prepare("
        SELECT up.*, si.effect, si.category, si.name 
        FROM user_purchases up
        JOIN shop_items si ON up.item_id = si.id
        WHERE up.id = ? AND up.user_id = ? AND up.used = 0
    ");
    $stmt->execute([$purchaseId, $_SESSION['user_id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception('Item not found or already used');
    }

    // Mark item as used
    $stmt = $pdo->prepare("
        UPDATE user_purchases 
        SET used = 1, used_date = NOW() 
        WHERE id = ? AND user_id = ? AND used = 0
    ");
    
    if ($stmt->execute([$purchaseId, $_SESSION['user_id']])) {
        // Apply item effect based on category
        switch($category) {
            case 'Task Themes':
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET task_theme = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$item['effect'], $_SESSION['user_id']]);
                $_SESSION['task_theme'] = $item['effect'];
                $message = "Task theme updated to " . $item['name'];
                break;
                
            case 'Profile Backgrounds':
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET profile_background = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$item['effect'], $_SESSION['user_id']]);
                $_SESSION['profile_background'] = $item['effect'];
                $message = "Profile background updated to " . $item['name'];
                break;

            case 'Point Multipliers':
                // Set expiration time (24 hours from now for multipliers)
                $expiryTime = date('Y-m-d H:i:s', strtotime('+24 hours'));
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET points_multiplier = ?,
                        multiplier_expiry = ?
                    WHERE id = ?
                ");
                $stmt->execute([$item['effect'], $expiryTime, $_SESSION['user_id']]);
                $_SESSION['points_multiplier'] = $item['effect'];
                $_SESSION['multiplier_expiry'] = $expiryTime;
                $message = "Points multiplier of {$item['effect']}x activated for 24 hours!";
                break;

            default:
                throw new Exception('Invalid item category');
        }
        
        $pdo->commit();
        $redirect = getRedirectUrl($category);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'category' => $category,
            'effect' => $item['effect'],
            'redirect' => $redirect,
            'debug_path' => $_SERVER['DOCUMENT_ROOT'] . $redirect
        ]);
    } else {
        throw new Exception('Failed to update item status');
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error using item: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getRedirectUrl($category) {
    $basePath = '/your-project-directory';
    
    switch($category) {
        case 'Task Themes':
            return $basePath . '/my_tasks.php';
        case 'Profile Backgrounds':
            return $basePath . '/profile.php';
        case 'Point Multipliers':
            return $basePath . '/my_tasks.php';
        default:
            return $basePath . '/purchased_items.php';
    }
}
?>