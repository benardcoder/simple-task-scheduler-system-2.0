<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'] ?? 'General';
        $deadline = $_POST['deadline'];
        $points = 150; // Fixed points for task completion

        $stmt = $pdo->prepare("
            INSERT INTO tasks (user_id, title, description, category, deadline, points, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");

        $stmt->execute([
            $_SESSION['user_id'],
            $title,
            $description,
            $category,
            $deadline,
            $points
        ]);

        setMessage('success', 'Task added successfully!');
        header("Location: task_scheduler.php");
        exit();

    } catch (PDOException $e) {
        setMessage('error', 'Error adding task: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Scheduler - Task Manager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="themes.css">
</head>
<body class="theme-<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light'; ?>">
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="scheduler-header">
                <h1><i class="fas fa-calendar-plus"></i> Task Scheduler</h1>
            </div>

            <?php displayMessage(); ?>

            <div class="scheduler-form">
                <form method="POST" class="task-form">
                    <div class="form-group">
                        <label for="title">Task Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="General">General</option>
                            <option value="Work">Work</option>
                            <option value="Personal">Personal</option>
                            <option value="Health">Health</option>
                            <option value="Education">Education</option>
                            <option value="Finance">Finance</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Deadline</label>
                        <input type="datetime-local" id="deadline" name="deadline" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Task
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
    .scheduler-form {
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

    .form-group input,
    .form-group textarea,
    .form-group select {
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

    .btn-primary:hover {
        background: #0056b3;
    }

    .scheduler-header {
        margin-bottom: 20px;
        padding: 20px;
    }

    .scheduler-header h1 {
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .scheduler-header i {
        color: #007bff;
    }
    </style>
</body>
</html>