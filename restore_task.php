<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE tasks SET status = 'In Progress' WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$task_id, $_SESSION['user_id']]);
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>