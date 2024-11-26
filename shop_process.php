<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'] ?? '';
    $user_id = $_SESSION['user_id'];

    try {
        if (purchaseItem($pdo, $user_id, $item_id)) {
            echo json_encode([
                'success' => true,
                'message' => 'Item purchased successfully!',
                'newPoints' => getUserPoints($pdo, $user_id)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Not enough points or item unavailable'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while processing your purchase'
        ]);
    }
}