<?php
session_start();
require_once 'config.php';
require_once 'TaskManager.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Initialize TaskManager
$taskManager = new TaskManager($pdo);

// Get user's tasks and categories
$tasks = $taskManager->getUserTasks($_SESSION['user_id']);
$categories = $taskManager->getUserCategories($_SESSION['user_id']);
// Debug information
echo "<!-- Debug Info: -->";
echo "<!-- User ID: " . $_SESSION['user_id'] . " -->";
echo "<!-- Number of tasks: " . count($tasks) . " -->";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="tasks.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1>Task Manager</h1>
            
            <!-- Task Form -->
            <form id="taskForm">
                <input type="hidden" id="taskId" name="task_id">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="priority">Priority:</label>
                    <select id="priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dueDate">Due Date:</label>
                    <input type="date" id="dueDate" name="due_date" required>
                </div>
                <button type="submit">Save Task</button>
            </form>

            <!-- Task List -->
            <div id="taskList">
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item" data-task-id="<?php echo htmlspecialchars($task['id']); ?>">
                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                        <p><?php echo htmlspecialchars($task['description']); ?></p>
                        <div class="task-details">
                            <span class="category">Category: <?php echo htmlspecialchars($task['category']); ?></span>
                            <span class="priority">Priority: <?php echo htmlspecialchars($task['priority']); ?></span>
                            <span class="due-date">Due: <?php echo htmlspecialchars($task['due_date']); ?></span>
                        </div>
                        <div class="task-actions">
                            <button onclick="editTask(<?php echo $task['id']; ?>)">Edit</button>
                            <button onclick="deleteTask(<?php echo $task['id']; ?>)">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="tasks.js"></script>
</body>
</html>