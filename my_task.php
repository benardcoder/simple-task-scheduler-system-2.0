<!-- my_tasks.php -->
<h2>Your Tasks</h2>
<ul>
    <li><a href="?page=my_tasks&status=in_progress">In Progress</a></li>
    <li><a href="?page=my_tasks&status=completed">Completed</a></li>
    <li><a href="?page=my_tasks&status=deleted">Deleted</a></li>
</ul>

<?php
$status = isset($_GET['status']) ? $_GET['status'] : 'in_progress';  // Default to 'In Progress'

$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id=? AND status=?");
$stmt->bind_param("is", $_SESSION['user_id'], $status);
$stmt->execute();
$result = $stmt->get_result();

while ($task = $result->fetch_assoc()) {
    echo "<div>";
    echo "<h3>" . htmlspecialchars($task['task_name']) . "</h3>";
    echo "<p>" . htmlspecialchars($task['task_description']) . "</p>";
    echo "<p>Due Date: " . htmlspecialchars($task['due_date']) . "</p>";
    echo "</div>";
}
?>

<link rel="stylesheet" href="themes.css">