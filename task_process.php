<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    
    switch ($action) {
        case 'save':
            try {
                $taskId = $_POST['task_id'] ?? null;
                $taskData = [
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'category' => $_POST['category'],
                    'priority' => $_POST['priority'],
                    'due_date' => $_POST['due_date'],
                    'user_id' => $_SESSION['user_id']
                ];
                
                if ($taskId) {
                    // Update existing task
                    $stmt = $pdo->prepare("
                        UPDATE tasks 
                        SET title = ?, description = ?, category = ?, 
                            priority = ?, due_date = ?
                        WHERE id = ? AND user_id = ?
                    ");
                    $stmt->execute([
                        $taskData['title'],
                        $taskData['description'],
                        $taskData['category'],
                        $taskData['priority'],
                        $taskData['due_date'],
                        $taskId,
                        $taskData['user_id']
                    ]);
                } else {
                    // Create new task
                    $stmt = $pdo->prepare("
                        INSERT INTO tasks (title, description, category, priority, 
                                         due_date, user_id, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'pending')
                    ");
                    $stmt->execute([
                        $taskData['title'],
                        $taskData['description'],
                        $taskData['category'],
                        $taskData['priority'],
                        $taskData['due_date'],
                        $taskData['user_id']
                    ]);
                }
                
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error saving task'
                ]);
            }
            break;
            
        case 'delete':
            try {
                $taskId = $_POST['task_id'];
                $stmt = $pdo->prepare("
                    DELETE FROM tasks 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$taskId, $_SESSION['user_id']]);
                
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error deleting task'
                ]);
            }
            break;
            
        case 'update_status':
            try {
                $taskId = $_POST['task_id'];
                $status = $_POST['status'];
                
                $stmt = $pdo->prepare("
                    UPDATE tasks 
                    SET status = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$status, $taskId, $_SESSION['user_id']]);
                
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error updating task status'
                ]);
            }
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'get_task') {
        try {
            $taskId = $_GET['task_id'];
            $stmt = $pdo->prepare("
                SELECT * FROM tasks 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$taskId, $_SESSION['user_id']]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($task) {
                echo json_encode([
                    'success' => true,
                    'task' => $task
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Task not found'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching task'
            ]);
        }
    }
}
?>