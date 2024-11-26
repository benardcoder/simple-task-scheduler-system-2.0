<?php
session_start();
require_once 'config.php';
require_once 'TaskManager.php';

// Set headers and error reporting
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$taskManager = new TaskManager($pdo);

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Handle GET requests
            if (!isset($_GET['action'])) {
                throw new Exception('Action parameter is required');
            }

            switch ($_GET['action']) {
                case 'list':
                    $tasks = $taskManager->getUserTasks($_SESSION['user_id']);
                    echo json_encode(['success' => true, 'tasks' => $tasks]);
                    break;

                case 'single':
                    if (!isset($_GET['id'])) {
                        throw new Exception('Task ID is required');
                    }
                    $task = $taskManager->getTask($_GET['id'], $_SESSION['user_id']);
                    if ($task) {
                        echo json_encode(['success' => true, 'task' => $task]);
                    } else {
                        throw new Exception('Task not found');
                    }
                    break;

                default:
                    throw new Exception('Invalid action');
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                throw new Exception('Invalid JSON data');
            }

            // Log incoming data for debugging
            error_log('Received POST data: ' . print_r($data, true));

            if (isset($data['action'])) {
                switch ($data['action']) {
                    case 'update_status':
                        if (!isset($data['task_id']) || !isset($data['status'])) {
                            throw new Exception('Task ID and status are required');
                        }

                        $validStatuses = ['pending', 'in_progress', 'completed'];
                        if (!in_array($data['status'], $validStatuses)) {
                            throw new Exception('Invalid status value. Must be one of: ' . implode(', ', $validStatuses));
                        }

                        $result = $taskManager->updateTaskStatus(
                            (int)$data['task_id'],
                            $_SESSION['user_id'],
                            $data['status']
                        );

                        if ($result) {
                            echo json_encode(['success' => true]);
                        } else {
                            throw new Exception('Failed to update task status');
                        }
                        break;

                    case 'create':
                    case 'update':
                        $requiredFields = ['title', 'due_date', 'priority'];
                        foreach ($requiredFields as $field) {
                            if (!isset($data[$field]) || empty($data[$field])) {
                                throw new Exception("Field '$field' is required");
                            }
                        }

                        if ($taskManager->saveTask($data, $_SESSION['user_id'])) {
                            echo json_encode(['success' => true]);
                        } else {
                            throw new Exception('Failed to save task');
                        }
                        break;

                    default:
                        throw new Exception('Invalid action');
                }
            } else {
                throw new Exception('Action is required');
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                throw new Exception('Task ID is required');
            }

            // Validate task ID is numeric
            if (!is_numeric($_GET['id'])) {
                throw new Exception('Invalid task ID format');
            }

            if ($taskManager->deleteTask((int)$_GET['id'], $_SESSION['user_id'])) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to delete task');
            }
            break;

        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    error_log('Error in task_operations.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'details' => error_get_last()
    ]);
} catch (PDOException $e) {
    error_log('Database error in task_operations.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred',
        'details' => $e->getMessage()
    ]);
}