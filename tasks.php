<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'update_points.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Refresh points from database
$points = getUserPoints($pdo, $_SESSION['user_id']);
$_SESSION['user_points'] = $points;

// Fetch tasks with sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'due_date';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$query = "SELECT * FROM tasks WHERE user_id = ?";
if ($filter === 'completed') {
    $query .= " AND completed = 1";
} elseif ($filter === 'pending') {
    $query .= " AND completed = 0";
}

$query .= " ORDER BY " . ($sort === 'due_date' ? 'due_date' : 'created_at') . " $order";

$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - Task Manager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="theme-<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light'; ?>">
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="tasks-header">
                <h1><i class="fas fa-tasks"></i> My Tasks</h1>
                <div class="points-display">
                    <i class="fas fa-star"></i>
                    Points: <span id="points-value"><?php echo $_SESSION['user_points'] ?? 0; ?></span>
                </div>
                <div class="task-controls">
                    <div class="filter-controls">
                        <select id="taskFilter" onchange="filterTasks(this.value)">
                            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Tasks</option>
                            <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        <select id="taskSort" onchange="sortTasks(this.value)">
                            <option value="due_date" <?php echo $sort === 'due_date' ? 'selected' : ''; ?>>Due Date</option>
                            <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Created Date</option>
                        </select>
                    </div>
                    <a href="add_task.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Task
                    </a>
                </div>
            </div>

            <div class="tasks-container">
                <?php if (empty($tasks)): ?>
                    <div class="no-tasks">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No tasks yet! Click "Add New Task" to get started.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-item <?php echo $task['completed'] ? 'completed' : ''; ?>" 
                             data-task-id="<?php echo $task['id']; ?>">
                            <div class="task-checkbox">
                                <input type="checkbox" 
                                       <?php echo $task['completed'] ? 'checked' : ''; ?>
                                       onchange="toggleTaskStatus(<?php echo $task['id']; ?>, this)">
                            </div>
                            <div class="task-content">
                                <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                                <p><?php echo htmlspecialchars($task['description']); ?></p>
                                <div class="task-meta">
                                    <span class="priority <?php echo $task['priority']; ?>">
                                        <?php echo ucfirst($task['priority']); ?>
                                    </span>
                                    <span class="difficulty <?php echo $task['difficulty']; ?>">
                                        <?php echo ucfirst($task['difficulty']); ?>
                                    </span>
                                    <span class="due-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="task-actions">
                                <a href="edit_task.php?id=<?php echo $task['id']; ?>" 
                                   class="btn btn-small btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteTask(<?php echo $task['id']; ?>)" 
                                        class="btn btn-small btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function toggleTaskStatus(taskId, checkbox) {
        const isCompleted = checkbox.checked;
        
        fetch('update_task_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `task_id=${taskId}&completed=${isCompleted}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update points display
                const newPoints = data.totalPoints;
                updatePointsDisplay(newPoints);
                localStorage.setItem('userPoints', newPoints);
                
                // Show points notification
                const pointsChange = data.points;
                const message = pointsChange > 0 
                    ? `+${pointsChange} points earned!` 
                    : `${pointsChange} points removed`;
                
                showNotification(message, pointsChange > 0 ? 'success' : 'info');
                
                // Update task appearance
                const taskElement = checkbox.closest('.task-item');
                if (taskElement) {
                    taskElement.classList.toggle('completed', isCompleted);
                }
            } else {
                // Revert checkbox if there was an error
                checkbox.checked = !isCompleted;
                showNotification('Error updating task: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            checkbox.checked = !isCompleted;
            showNotification('Error updating task status', 'error');
        });
    }

    function updatePointsDisplay(points) {
        const pointsDisplays = document.querySelectorAll('.points-display span');
        pointsDisplays.forEach(display => {
            if (display) {
                display.textContent = points;
            }
        });
    }

    function refreshPoints() {
        fetch('get_points.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updatePointsDisplay(data.points);
                    localStorage.setItem('userPoints', data.points);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.classList.add('show'), 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    function deleteTask(taskId) {
        if (confirm('Are you sure you want to delete this task?')) {
            fetch('delete_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `task_id=${taskId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const taskElement = document.querySelector(`[data-task-id="${taskId}"]`);
                    if (taskElement) {
                        taskElement.remove();
                        showNotification('Task deleted successfully', 'success');
                        refreshPoints(); // Refresh points after deletion
                    }
                } else {
                    showNotification('Error deleting task: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error deleting task', 'error');
            });
        }
    }

    function filterTasks(filter) {
        window.location.href = `tasks.php?filter=${filter}&sort=${document.getElementById('taskSort').value}`;
    }

    function sortTasks(sort) {
        window.location.href = `tasks.php?sort=${sort}&filter=${document.getElementById('taskFilter').value}`;
    }

    // Listen for points updates from other tabs
    window.addEventListener('storage', function(e) {
        if (e.key === 'userPoints') {
            updatePointsDisplay(e.newValue);
        }
    });

    // Refresh points periodically
    setInterval(refreshPoints, 30000);
    refreshPoints();
    </script>

    <style>
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 20px;
        border-radius: 5px;
        background: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transform: translateX(120%);
        transition: transform 0.3s ease;
        z-index: 1000;
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification.success {
        background: #4CAF50;
        color: white;
    }

    .notification.error {
        background: #f44336;
        color: white;
    }

    .notification.info {
        background: #2196F3;
        color: white;
    }

    .points-display {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 1.2em;
        color: var(--text-primary);
    }

    .points-display i {
        color: #ffd700;
    }

    .task-controls {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .filter-controls {
        display: flex;
        gap: 10px;
    }

    .filter-controls select {
        padding: 5px 10px;
        border-radius: 5px;
        border: 1px solid var(--border-color);
        background: var(--bg-secondary);
        color: var(--text-primary);
    }
    </style>
</body>
</html>