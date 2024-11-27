<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if task ID is provided
if (!isset($_GET['id'])) {
    header("Location: my_tasks.php");
    exit();
}

$task_id = $_GET['id'];

// Fetch task details
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch();

if (!$task) {
    setMessage('error', 'Task not found.');
    header("Location: my_tasks.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $deadline = $_POST['deadline'];
    $points = $_POST['points'];

    // Update task
    $updateStmt = $pdo->prepare("
        UPDATE tasks 
        SET title = ?, description = ?, category = ?, deadline = ?, points = ?
        WHERE id = ? AND user_id = ?
    ");
    $updateStmt->execute([$title, $description, $category, $deadline, $points, $task_id, $_SESSION['user_id']]);

    setMessage('success', 'Task updated successfully.');
    header("Location: my_tasks.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - Task Manager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="edit-task-header">
                <h1><i class="fas fa-edit"></i> Edit Task</h1>
            </div>

            <?php displayMessage(); ?>

            <form method="POST" class="edit-task-form">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($task['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($task['category']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="deadline">Deadline</label>
                    <input type="datetime-local" id="deadline" name="deadline" value="<?php echo date('Y-m-d\TH:i', strtotime($task['deadline'])); ?>" required>
                </div>
                <div class="form-group">
                    <label for="points">Points</label>
                    <input type="number" id="points" name="points" value="<?php echo htmlspecialchars($task['points']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </form>
        </div>
    </div>

    <style>
    .edit-task-form {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-width: 600px;
        margin: 20px auto;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #666;
    }

    .form-group input, .form-group textarea {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ddd;
        font-size: 1em;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .btn-primary {
        background: #007bff;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        font-size: 1em;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    </style>
</body>
</html>