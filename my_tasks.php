<?php
session_start();
require_once 'config.php';
require_once 'TaskManager.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$taskManager = new TaskManager($pdo);
$tasks = $taskManager->getUserTasks($_SESSION['user_id']);

// Group tasks by status
$tasksByStatus = [
    'pending' => [],
    'in_progress' => [],
    'completed' => []
];

foreach ($tasks as $task) {
    $status = strtolower($task['status']) ?: 'pending';
    $tasksByStatus[$status][] = $task;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="my_tasks.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="tasks-header">
            <h1><i class="fas fa-tasks"></i> My Tasks</h1>
            <div class="task-filters">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="pending">Pending</button>
                <button class="filter-btn" data-filter="in_progress">In Progress</button>
                <button class="filter-btn" data-filter="completed">Completed</button>
            </div>
        </div>

        <div class="tasks-container">
            <?php foreach ($tasksByStatus as $status => $statusTasks): ?>
                <div class="task-column" data-status="<?php echo $status; ?>">
                    <h2><?php echo ucfirst(str_replace('_', ' ', $status)); ?></h2>
                    <div class="task-list">
                        <?php foreach ($statusTasks as $task): ?>
                            <div class="task-card" data-task-id="<?php echo $task['id']; ?>">
                                <div class="task-priority <?php echo strtolower($task['priority']); ?>">
                                    <?php echo $task['priority']; ?>
                                </div>
                                <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                                <p class="task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                                <div class="task-meta">
                                    <span class="task-category">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($task['category']); ?>
                                    </span>
                                    <span class="task-due-date">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                    </span>
                                </div>
                                <div class="task-actions">
                                    <select class="status-select" onchange="updateTaskStatus(<?php echo $task['id']; ?>, this.value)">
                                        <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                    <button onclick="deleteTask(<?php echo $task['id']; ?>)" class="delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Task status update function
        async function updateTaskStatus(taskId, newStatus) {
            try {
                const response = await fetch('api/task_operations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        task_id: taskId,
                        status: newStatus,
                        action: 'update_status'
                    })
                });

                if (!response.ok) throw new Error('Failed to update status');
                
                const result = await response.json();
                if (result.success) {
                    // Move task card to appropriate column
                    const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                    const targetColumn = document.querySelector(`[data-status="${newStatus}"]`);
                    targetColumn.querySelector('.task-list').appendChild(taskCard);
                }
            } catch (error) {
                console.error('Error updating task status:', error);
                alert('Failed to update task status');
            }
        }

        // Delete task function
        async function deleteTask(taskId) {
            if (!confirm('Are you sure you want to delete this task?')) return;

            try {
                const response = await fetch(`api/task_operations.php?id=${taskId}`, {
                    method: 'DELETE'
                });

                if (!response.ok) throw new Error('Failed to delete task');

                const result = await response.json();
                if (result.success) {
                    document.querySelector(`[data-task-id="${taskId}"]`).remove();
                }
            } catch (error) {
                console.error('Error deleting task:', error);
                alert('Failed to delete task');
            }
        }

        // Filter buttons functionality
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                // Filter tasks
                const filter = button.dataset.filter;
                document.querySelectorAll('.task-column').forEach(column => {
                    if (filter === 'all' || column.dataset.status === filter) {
                        column.style.display = 'block';
                    } else {
                        column.style.display = 'none';
                    }
                });
            });
        });
    </script>

    <style>
        .tasks-container {
            display: flex;
            gap: 20px;
            padding: 20px;
        }

        .task-column {
            flex: 1;
            min-width: 300px;
            background: #f5f5f5;
            border-radius: 8px;
            padding: 15px;
        }

        .task-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .task-priority {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-bottom: 10px;
        }

        .task-priority.high { background: #ffe6e6; color: #cc0000; }
        .task-priority.medium { background: #fff3e6; color: #cc7700; }
        .task-priority.low { background: #e6ffe6; color: #007700; }

        .task-meta {
            display: flex;
            gap: 15px;
            margin: 10px 0;
            color: #666;
            font-size: 0.9em;
        }

        .task-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .status-select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .delete-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .tasks-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .task-filters {
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: #f0f0f0;
        }

        .filter-btn.active {
            background: #007bff;
            color: white;
        }

        @media (max-width: 768px) {
            .tasks-container {
                flex-direction: column;
            }

            .task-column {
                min-width: auto;
            }
        }
    </style>
</body>
</html>