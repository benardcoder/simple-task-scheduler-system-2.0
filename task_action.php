<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $task_id = $_POST['task_id'] ?? '';
    $user_id = $_SESSION['user_id'];

    try {
        switch ($action) {
            case 'update_status':
                $status = $_POST['status'] ?? '';
                $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
                $success = $stmt->execute([$status, $task_id, $user_id]);
                break;

            case 'delete':
                $stmt = $pdo->prepare("UPDATE tasks SET status = 'Deleted' WHERE id = ? AND user_id = ?");
                $success = $stmt->execute([$task_id, $user_id]);
                break;

            case 'restore':
                $stmt = $pdo->prepare("UPDATE tasks SET status = 'In Progress' WHERE id = ? AND user_id = ?");
                $success = $stmt->execute([$task_id, $user_id]);
                break;

            case 'permanent_delete':
                $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ? AND status = 'Deleted'");
                $success = $stmt->execute([$task_id, $user_id]);
                break;

            default:
                throw new Exception('Invalid action');
        }

        echo json_encode(['success' => $success]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>