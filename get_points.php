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

try {
    $points = getUserPoints($pdo, $_SESSION['user_id']);
    $_SESSION['user_points'] = $points;
    
    echo json_encode([
        'success' => true,
        'points' => $points
    ]);
} catch (Exception $e) {
    error_log("Error in get_points.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching points'
    ]);
}
?>