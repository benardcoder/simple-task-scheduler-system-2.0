<?php
class TaskManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserTasks($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, c.name as category_name 
                FROM tasks t 
                LEFT JOIN task_categories c ON t.category = c.id 
                WHERE t.user_id = ? 
                ORDER BY t.due_date ASC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching tasks: " . $e->getMessage());
            return [];
        }
    }

    public function getTask($taskId, $userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, c.name as category_name 
                FROM tasks t 
                LEFT JOIN task_categories c ON t.category = c.id 
                WHERE t.id = ? AND t.user_id = ?
            ");
            $stmt->execute([$taskId, $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching task: " . $e->getMessage());
            return null;
        }
    }

    public function saveTask($data, $userId) {
        try {
            if (isset($data['task_id']) && !empty($data['task_id'])) {
                // Update existing task
                $stmt = $this->pdo->prepare("
                    UPDATE tasks 
                    SET title = ?, 
                        description = ?, 
                        category = ?, 
                        priority = ?, 
                        due_date = ?,
                        status = ?
                    WHERE id = ? AND user_id = ?
                ");
                return $stmt->execute([
                    $data['title'],
                    $data['description'],
                    $data['category'],
                    $data['priority'],
                    $data['due_date'],
                    $data['status'] ?? 'pending',
                    $data['task_id'],
                    $userId
                ]);
            } else {
                // Create new task
                $stmt = $this->pdo->prepare("
                    INSERT INTO tasks (
                        title, description, category, priority, 
                        due_date, status, user_id
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                return $stmt->execute([
                    $data['title'],
                    $data['description'],
                    $data['category'],
                    $data['priority'],
                    $data['due_date'],
                    'pending',
                    $userId
                ]);
            }
        } catch (PDOException $e) {
            error_log("Error saving task: " . $e->getMessage());
            return false;
        }
    }

    public function updateTaskStatus($taskId, $userId, $status) {
        try {
            // Enable PDO error mode
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Verify task exists and belongs to user
            $checkStmt = $this->pdo->prepare("
                SELECT id FROM tasks 
                WHERE id = ? AND user_id = ?
            ");
            $checkStmt->execute([$taskId, $userId]);
            
            if (!$checkStmt->fetch()) {
                error_log("Task not found or doesn't belong to user. TaskID: $taskId, UserID: $userId");
                return false;
            }

            // Validate status
            $validStatuses = ['pending', 'in_progress', 'completed'];
            if (!in_array($status, $validStatuses)) {
                error_log("Invalid status value: $status");
                return false;
            }

            $stmt = $this->pdo->prepare("
                UPDATE tasks 
                SET status = ? 
                WHERE id = ? AND user_id = ?
            ");
            
            // Log the update attempt
            error_log("Attempting to update task $taskId to status $status");
            
            $result = $stmt->execute([$status, $taskId, $userId]);
            
            if (!$result) {
                error_log("Update failed. PDO Error Info: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            $rowCount = $stmt->rowCount();
            error_log("Rows affected by update: $rowCount");
            
            return $rowCount > 0;

        } catch (PDOException $e) {
            error_log("Database error in updateTaskStatus: " . $e->getMessage());
            return false;
        }
    }

    public function deleteTask($taskId, $userId) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM tasks 
                WHERE id = ? AND user_id = ?
            ");
            $result = $stmt->execute([$taskId, $userId]);
            
            if (!$result) {
                error_log("Delete failed. Error: " . print_r($stmt->errorInfo(), true));
                return false;
            }
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error deleting task: " . $e->getMessage());
            return false;
        }
    }

    public function getUserCategories($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM task_categories 
                WHERE user_id = ? OR user_id IS NULL 
                ORDER BY name ASC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return [];
        }
    }

    public function getTasksByStatus($userId, $status) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, c.name as category_name 
                FROM tasks t 
                LEFT JOIN task_categories c ON t.category = c.id 
                WHERE t.user_id = ? AND t.status = ?
                ORDER BY t.due_date ASC
            ");
            $stmt->execute([$userId, $status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching tasks by status: " . $e->getMessage());
            return [];
        }
    }
}