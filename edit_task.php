<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$task_id = isset($_GET['id']) ? $_GET['id'] : null;
$task = null;

if ($task_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $_SESSION['user_id']]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
}

if (!$task) {
    header("Location: my_tasks.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $category = $_POST['category'];

    try {
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ?, 
                              priority = ?, category = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $description, $due_date, $priority, $category, $task_id, $_SESSION['user_id']]);
        
        $_SESSION['message'] = "Task updated successfully!";
        $_SESSION['message_type'] = "success";
        header("Location: my_tasks.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['message'] = "Error updating task: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="task_scheduler.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar (same as dashboard.php) -->
        <div class="sidebar">
            <!-- Copy sidebar content from dashboard.php -->
        </div>

        <div class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-edit"></i> Edit Task</h1>
            </div>

            <div class="task-form-container">
                <form action="edit_task.php?id=<?php echo $task_id; ?>" method="POST" class="task-form">
                    <div class="form-group">
                        <label for="title">Task Title</label>
                        <input type="text" id="title" name="title" required 
                               value="<?php echo htmlspecialchars($task['title']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($task['description']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="datetime-local" id="due_date" name="due_date" required 
                                   value="<?php echo date('Y-m-d\TH:i', strtotime($task['due_date'])); ?>">
                        </div>

                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select id="priority" name="priority" required>
                                <option value="Low" <?php echo $task['priority'] == 'Low' ? 'selected' : ''; ?>>Low</option>
                                <option value="Medium" <?php echo $task['priority'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="High" <?php echo $task['priority'] == 'High' ? 'selected' : ''; ?>>High</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="Work" <?php echo $task['category'] == 'Work' ? 'selected' : ''; ?>>Work</option>
                            <option value="Personal" <?php echo $task['category'] == 'Personal' ? 'selected' : ''; ?>>Personal</option>
                            <option value="Study" <?php echo $task['category'] == 'Study' ? 'selected' : ''; ?>>Study</option>
                            <option value="Health" <?php echo $task['category'] == 'Health' ? 'selected' : ''; ?>>Health</option>
                            <option value="Other" <?php echo $task['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="my_tasks.php" class="btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="task_scheduler.js"></script>
</body>
</html>