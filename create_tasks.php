<!-- create_task.php -->
<h2>Create New Task</h2>
<form method="POST" action="create_task_handler.php">
    <label for="task_name">Task Name:</label>
    <input type="text" name="task_name" required><br>

    <label for="task_description">Description:</label>
    <textarea name="task_description" required></textarea><br>

    <label for="due_date">Due Date:</label>
    <input type="datetime-local" name="due_date" required><br>

    <button type="submit">Create Task</button>
</form>