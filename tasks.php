<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$tasks = getUserTasks($pdo, $_SESSION['user_id']);
echo "<pre>";
print_r($tasks);
echo "</pre>";

// Get user's tasks
$stmt = $pdo->prepare("
    SELECT * FROM tasks 
    WHERE user_id = ? 
    ORDER BY due_date ASC
");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get task categories
$stmt = $pdo->prepare("
    SELECT * FROM task_categories 
    WHERE user_id = ? 
    ORDER BY name ASC
");
$stmt->execute([$_SESSION['user_id']]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - Task Manager</title>
    <link rel="stylesheet" href="tasks.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        
        
        <div class="main-content">
            <div class="tasks-header">
                <h1><i class="fas fa-tasks"></i> My Tasks</h1>
                <button id="addTaskBtn" class="btn-primary">
                    <i class="fas fa-plus"></i> New Task
                </button>
            </div>

            <!-- Task Filters -->
            <div class="task-filters">
                <div class="filter-group">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="in_progress">In Progress</button>
                    <button class="filter-btn" data-filter="completed">Completed</button>
                </div>
                <div class="search-group">
                    <input type="text" id="taskSearch" placeholder="Search tasks...">
                </div>
            </div>

            <!-- Tasks Grid -->
            <div class="tasks-grid">
                <?php if (!empty($tasks)): ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-card" data-status="<?php echo htmlspecialchars($task['status']); ?>">
                            <div class="task-priority <?php echo strtolower($task['priority']); ?>">
                                <?php echo htmlspecialchars($task['priority']); ?>
                            </div>
                            <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p><?php echo htmlspecialchars($task['description']); ?></p>
                            <div class="task-meta">
                                <span class="task-category">
                                    <i class="fas fa-tag"></i>
                                    <?php echo htmlspecialchars($task['category']); ?>
                                </span>
                                <span class="task-due">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                                </span>
                            </div>
                            <div class="task-actions">
                                <button onclick="editTask(<?php echo $task['id']; ?>)" 
                                        class="btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'completed')" 
                                        class="btn-icon" title="Complete"
                                        <?php echo $task['status'] === 'completed' ? 'disabled' : ''; ?>>
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="deleteTask(<?php echo $task['id']; ?>)" 
                                        class="btn-icon" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No tasks yet. Click "New Task" to get started!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add/Edit Task Modal -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">New Task</h2>
            <form id="taskForm">
                <input type="hidden" id="taskId" name="task_id">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" required>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="datetime-local" id="due_date" name="due_date" required>
                </div>
                <button type="submit" class="btn-primary">Save Task</button>
            </form>
        </div>
    </div>

    <script src="tasks.js"></script>
</body>
</html>