<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Define available categories
$availableCategories = [
    'All' => 'fas fa-list-ul',
    'General' => 'fas fa-inbox',
    'Work' => 'fas fa-briefcase',
    'Personal' => 'fas fa-user',
    'Health' => 'fas fa-heart',
    'Education' => 'fas fa-graduation-cap',
    'Finance' => 'fas fa-dollar-sign'
];

// Get selected category from URL parameter
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'All';

// Prepare the WHERE clause based on selected category
$whereClause = "WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if ($selectedCategory !== 'All') {
    $whereClause .= " AND category = ?";
    $params[] = $selectedCategory;
}

// Get tasks with filtering
$taskStmt = $pdo->prepare("
    SELECT * FROM tasks 
    $whereClause
    ORDER BY deadline ASC
");
$taskStmt->execute($params);
$tasks = $taskStmt->fetchAll();

// Get task statistics
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        category,
        COUNT(*) as category_count
    FROM tasks 
    WHERE user_id = ?
    GROUP BY category
");
$statsStmt->execute([$_SESSION['user_id']]);
$taskStats = $statsStmt->fetchAll();
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
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="tasks-header">
                <h1><i class="fas fa-tasks"></i> My Tasks</h1>
            </div>

            <?php displayMessage(); ?>

            <!-- Category Filter Buttons -->
            <div class="category-filters">
                <?php foreach ($availableCategories as $category => $icon): ?>
                    <a href="?category=<?php echo urlencode($category); ?>" 
                       class="category-btn <?php echo $selectedCategory === $category ? 'active' : ''; ?>">
                        <i class="<?php echo $icon; ?>"></i>
                        <?php echo $category; ?>
                        <?php
                        if ($category !== 'All') {
                            $count = array_reduce($taskStats, function($carry, $item) use ($category) {
                                return $carry + ($item['category'] === $category ? $item['category_count'] : 0);
                            }, 0);
                            echo "<span class='count'>$count</span>";
                        }
                        ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Tasks Grid -->
            <div class="tasks-grid">
                <?php if (empty($tasks)): ?>
                    <div class="no-tasks">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No tasks found in this category</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-card <?php echo $task['status']; ?>">
                            <div class="task-header">
                                <span class="task-category">
                                    <i class="<?php echo $availableCategories[$task['category']] ?? 'fas fa-tag'; ?>"></i>
                                    <?php echo htmlspecialchars($task['category']); ?>
                                </span>
                                <span class="task-points">
                                    <i class="fas fa-coins"></i> 150 points
                                </span>
                            </div>
                            
                            <h3 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p class="task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                            
                            <div class="task-meta">
                                <span class="task-deadline">
                                    <i class="fas fa-clock"></i>
                                    <?php 
                                        $deadline = new DateTime($task['deadline']);
                                        echo $deadline->format('M d, Y H:i'); 
                                    ?>
                                </span>
                                <span class="task-status <?php echo $task['status']; ?>">
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                            </div>
                            
                            <div class="task-actions">
                                <?php if ($task['status'] === 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" name="complete_task" class="btn btn-success">
                                            <i class="fas fa-check"></i> Complete
                                        </button>
                                    </form>
                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Previous PHP code remains the same -->

<style>
/* Modern Dashboard Layout */
.main-content {
    background: #f8f9fa;
    padding: 20px;
}

.tasks-header {
    background: white;
    padding: 20px 30px;
    border-radius: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.tasks-header h1 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.8em;
    display: flex;
    align-items: center;
    gap: 10px;
}

.tasks-header h1 i {
    color: #3498db;
}

/* Category Filters */
.category-filters {
    display: flex;
    gap: 12px;
    padding: 20px;
    overflow-x: auto;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.category-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #f8f9fa;
    border: none;
    border-radius: 25px;
    color: #666;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
}

.category-btn:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.category-btn.active {
    background: #3498db;
    color: white;
}

.category-btn .count {
    background: rgba(0,0,0,0.1);
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8em;
}

/* Tasks Grid */
.tasks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 10px;
}

.task-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #eee;
    position: relative;
    overflow: hidden;
}

.task-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.task-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: #3498db;
    border-radius: 15px 15px 0 0;
}

.task-card.completed::before {
    background: #2ecc71;
}

.task-card.overdue::before {
    background: #e74c3c;
}

.task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.task-category {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    background: #f8f9fa;
    border-radius: 20px;
    font-size: 0.9em;
    color: #666;
}

.task-points {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    background: #fff3cd;
    border-radius: 20px;
    font-size: 0.9em;
    color: #856404;
}

.task-title {
    font-size: 1.2em;
    color: #2c3e50;
    margin: 10px 0;
    font-weight: 600;
}

.task-description {
    color: #666;
    font-size: 0.95em;
    line-height: 1.5;
    margin-bottom: 15px;
    max-height: 3em;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.task-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 15px 0;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.task-deadline {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #666;
    font-size: 0.9em;
}

.task-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 500;
}

.task-status.pending {
    background: #fff3cd;
    color: #856404;
}

.task-status.completed {
    background: #d4edda;
    color: #155724;
}

.task-status.overdue {
    background: #f8d7da;
    color: #721c24;
}

.task-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 16px;
    border-radius: 20px;
    border: none;
    cursor: pointer;
    font-size: 0.9em;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-success {
    background: #2ecc71;
    color: white;
}

.btn-success:hover {
    background: #27ae60;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

/* No Tasks Message */
.no-tasks {
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 15px;
    grid-column: 1 / -1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.no-tasks i {
    font-size: 4em;
    color: #ddd;
    margin-bottom: 15px;
}

.no-tasks p {
    color: #666;
    font-size: 1.1em;
}

/* Responsive Design */
@media (max-width: 768px) {
    .tasks-grid {
        grid-template-columns: 1fr;
    }
    
    .category-filters {
        padding: 15px;
    }
    
    .category-btn {
        padding: 8px 16px;
    }
}

/* Hover Effects */
.task-card .task-actions {
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.task-card:hover .task-actions {
    opacity: 1;
}

/* Custom Scrollbar */
.category-filters::-webkit-scrollbar {
    height: 6px;
}

.category-filters::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.category-filters::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 10px;
}

.category-filters::-webkit-scrollbar-thumb:hover {
    background: #999;
}
</style>
</body>
</html>
    