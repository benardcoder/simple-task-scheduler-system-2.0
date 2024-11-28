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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id']) && isset($_POST['completed'])) {
    try {
        $taskId = $_POST['task_id'];
        $completed = $_POST['completed'] === 'true' ? 1 : 0;
        
        $pdo->beginTransaction();
        
        // Update task status
        $stmt = $pdo->prepare("UPDATE tasks SET completed = ?, completed_date = ? WHERE id = ? AND user_id = ?");
        $completedDate = $completed ? date('Y-m-d H:i:s') : null;
        $success = $stmt->execute([$completed, $completedDate, $taskId, $_SESSION['user_id']]);
        
        if ($success) {
            // Get task details for points calculation
            $stmt = $pdo->prepare("SELECT priority, difficulty FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate points for this task
            $taskPoints = calculateTaskPoints($task['priority'], $task['difficulty']);
            
            // Update total user points
            $totalPoints = updateUserPoints($pdo, $_SESSION['user_id']);
            
            if ($totalPoints !== false) {
                // Update session with new points total
                $_SESSION['user_points'] = $totalPoints;
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'points' => $completed ? $taskPoints : -$taskPoints,
                    'totalPoints' => $totalPoints,
                    'message' => $completed ? 'Task completed and points added' : 'Task uncompleted and points removed'
                ]);
            } else {
                throw new Exception('Failed to update points');
            }
        } else {
            throw new Exception('Failed to update task');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating task: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error updating task: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>