<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $category = $_POST['category'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, priority, category, status) 
                              VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$user_id, $title, $description, $due_date, $priority, $category]);
        
        $_SESSION['message'] = "Task added successfully!";
        $_SESSION['message_type'] = "success";
        header("Location: task_scheduler.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['message'] = "Error adding task: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Scheduler</title>
    <link rel="stylesheet" href="task_scheduler.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
    <div class="dashboard-container">
        
        <div class="sidebar">
            <!-- Copy sidebar content from dashboard.php -->
        </div>

        <div class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-calendar-alt"></i> Task Scheduler</h1>
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message <?php echo $_SESSION['message_type']; ?>">
                        <?php 
                            echo $_SESSION['message'];
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="task-form-container">
                <h2>Add New Task</h2>
                <form action="task_scheduler.php" method="POST" class="task-form">
                    <div class="form-group">
                        <label for="title">Task Title</label>
                        <input type="text" id="title" name="title" required 
                               placeholder="Enter task title">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" 
                                  placeholder="Enter task description"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="datetime-local" id="due_date" name="due_date" required>
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
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="Work">Work</option>
                            <option value="Personal">Personal</option>
                            <option value="Study">Study</option>
                            <option value="Health">Health</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-plus"></i> Add Task
                        </button>
                        <button type="reset" class="btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="task_scheduler.js"></script>
</body>
</html>