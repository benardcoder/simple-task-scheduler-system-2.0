<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get tasks for each status
$tasks = [
    'in_progress' => getTasks($pdo, $_SESSION['user_id'], 'In Progress'),
    'completed' => getTasks($pdo, $_SESSION['user_id'], 'Completed'),
    'deleted' => getTasks($pdo, $_SESSION['user_id'], 'Deleted')
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks</title>
    <!-- CSS -->
    <link rel="stylesheet" href="tasks.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-tasks"></i> My Tasks</h1>
                <a href="task_scheduler.php" class="btn-primary">
                    <i class="fas fa-plus"></i> New Task
                </a>
            </div>

            <?php displayMessage(); ?>

            <!-- Task Navigation -->
            <div class="task-navigation">
                <button class="task-nav-btn active" data-target="in_progress">
                    <i class="fas fa-spinner"></i> In Progress
                    <span class="task-count"><?php echo count($tasks['in_progress']); ?></span>
                </button>
                <button class="task-nav-btn" data-target="completed">
                    <i class="fas fa-check-circle"></i> Completed
                    <span class="task-count"><?php echo count($tasks['completed']); ?></span>
                </button>
                <button class="task-nav-btn" data-target="deleted">
                    <i class="fas fa-trash"></i> Deleted
                    <span class="task-count"><?php echo count($tasks['deleted']); ?></span>
                </button>
            </div>

            <!-- Task Sections -->
            <?php foreach ($tasks as $status => $task_list): ?>
                <div class="task-section <?php echo $status === 'in_progress' ? 'active' : ''; ?>" 
                     id="<?php echo $status; ?>">
                    <?php if (!empty($task_list)): ?>
                        <?php foreach ($task_list as $task): ?>
                            <div class="task-card" data-task-id="<?php echo $task['id']; ?>">
                                <div class="task-header">
                                    <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                                    <span class="priority-badge priority-<?php echo strtolower($task['priority']); ?>">
                                        <?php echo $task['priority']; ?>
                                    </span>
                                </div>
                                <p class="task-description">
                                    <?php echo htmlspecialchars($task['description']); ?>
                                </p>
                                <div class="task-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i> 
                                        Due: <?php echo formatDate($task['due_date']); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-tag"></i> 
                                        <?php echo $task['category']; ?>
                                    </span>
                                </div>
                                <div class="task-actions">
                                    <?php if ($status === 'in_progress'): ?>
                                        <button onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'Completed')" 
                                                class="btn-action complete">
                                            <i class="fas fa-check"></i> Complete
                                        </button>
                                        <button onclick="editTask(<?php echo $task['id']; ?>)" 
                                                class="btn-action edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button onclick="deleteTask(<?php echo $task['id']; ?>)" 
                                                class="btn-action delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    <?php elseif ($status === 'completed'): ?>
                                        <button onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'In Progress')" 
                                                class="btn-action reopen">
                                            <i class="fas fa-redo"></i> Reopen
                                        </button>
                                        <button onclick="deleteTask(<?php echo $task['id']; ?>)" 
                                                class="btn-action delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    <?php else: ?>
                                        <button onclick="restoreTask(<?php echo $task['id']; ?>)" 
                                                class="btn-action restore">
                                            <i class="fas fa-trash-restore"></i> Restore
                                        </button>
                                        <button onclick="permanentlyDeleteTask(<?php echo $task['id']; ?>)" 
                                                class="btn-action delete">
                                            <i class="fas fa-trash"></i> Delete Permanently
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <p>No <?php echo str_replace('_', ' ', $status); ?> tasks found.</p>
                            <?php if ($status === 'in_progress'): ?>
                                <a href="task_scheduler.php" class="btn-primary">Create Task</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="tasks.js"></script>
</body>
</html>