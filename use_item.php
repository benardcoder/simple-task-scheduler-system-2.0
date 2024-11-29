<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$purchaseId = $data['purchase_id'] ?? null;
$category = $data['category'] ?? null;

if (!$purchaseId || !$category) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

try {
    $pdo->beginTransaction();

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
                // Update user's task theme preference
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET task_theme = (
                        SELECT effect FROM shop_items si 
                        JOIN user_purchases up ON si.id = up.item_id 
                        WHERE up.id = ?
                    )
                    WHERE id = ?
                ");
                $stmt->execute([$purchaseId, $_SESSION['user_id']]);
                break;
                
            case 'Profile Backgrounds':
                // Update user's profile background
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET profile_background = (
                        SELECT effect FROM shop_items si 
                        JOIN user_purchases up ON si.id = up.item_id 
                        WHERE up.id = ?
                    )
                    WHERE id = ?
                ");
                $stmt->execute([$purchaseId, $_SESSION['user_id']]);
                break;
        }
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to update item status');
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error using item: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error using item']);
}
?>