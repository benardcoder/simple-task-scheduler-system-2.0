<?php
function calculateTaskPoints($priority, $difficulty) {
    $basePoints = 10;
    
    $priorityMultiplier = [
        'low' => 1,
        'medium' => 1.5,
        'high' => 2
    ];
    
    $difficultyMultiplier = [
        'easy' => 1,
        'medium' => 1.5,
        'hard' => 2
    ];
    
    $points = $basePoints;
    $points *= isset($priorityMultiplier[$priority]) ? $priorityMultiplier[$priority] : 1;
    $points *= isset($difficultyMultiplier[$difficulty]) ? $difficultyMultiplier[$difficulty] : 1;
    
    return round($points);
}

function updateUserPoints($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT SUM(
                CASE 
                    WHEN priority = 'high' THEN 20
                    WHEN priority = 'medium' THEN 15
                    ELSE 10
                END *
                CASE 
                    WHEN difficulty = 'hard' THEN 2
                    WHEN difficulty = 'medium' THEN 1.5
                    ELSE 1
                END
            ) as total_points
            FROM tasks 
            WHERE user_id = ? AND completed = 1
        ");
        $stmt->execute([$userId]);
        $totalPoints = $stmt->fetchColumn() ?: 0;

        // Subtract points spent in shop
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(si.cost), 0) as spent_points
            FROM user_purchases up
            JOIN shop_items si ON up.item_id = si.id
            WHERE up.user_id = ?
        ");
        $stmt->execute([$userId]);
        $spentPoints = $stmt->fetchColumn();

        $finalPoints = max(0, $totalPoints - $spentPoints);

        $stmt = $pdo->prepare("UPDATE users SET points = ? WHERE id = ?");
        $stmt->execute([$finalPoints, $userId]);

        return $finalPoints;
    } catch (PDOException $e) {
        error_log("Error updating points: " . $e->getMessage());
        return false;
    }
}

function getUserPurchases($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT si.* 
            FROM shop_items si
            JOIN user_purchases up ON si.id = up.item_id
            WHERE up.user_id = ?
            ORDER BY up.purchase_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting user purchases: " . $e->getMessage());
        return [];
    }
}
?>