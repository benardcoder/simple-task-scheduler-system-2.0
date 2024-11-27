<?php
require_once 'config.php';

try {
    // Add last_claimed column to users table
    $sql1 = "ALTER TABLE users ADD COLUMN last_claimed DATE DEFAULT NULL";
    $pdo->exec($sql1);
    echo "Successfully added last_claimed column to users table<br>";

    // Add theme column to profile table
    $sql2 = "ALTER TABLE profile ADD COLUMN theme VARCHAR(50) DEFAULT 'default'";
    $pdo->exec($sql2);
    echo "Successfully added theme column to profile table<br>";

    // Add task_slots column to profile table
    $sql3 = "ALTER TABLE profile ADD COLUMN task_slots INT DEFAULT 3";
    $pdo->exec($sql3);
    echo "Successfully added task_slots column to profile table<br>";

    // Add join_date column to profile table
    $sql4 = "ALTER TABLE profile ADD COLUMN join_date DATETIME DEFAULT CURRENT_TIMESTAMP";
    $pdo->exec($sql4);
    echo "Successfully added join_date column to profile table<br>";

    // Update existing profiles with join_date
    $sql5 = "UPDATE profile SET join_date = CURRENT_TIMESTAMP WHERE join_date IS NULL";
    $pdo->exec($sql5);
    echo "Successfully updated join_date for existing profiles<br>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    // If the column already exists, it will show an error but won't break the script
}

echo "<br>Database update completed. You can now delete this file.";
?>