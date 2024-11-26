<?php
// ==========================================
// Authentication Functions
// ==========================================

function loginUser($pdo, $username, $password) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
        return false;
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function registerUser($pdo, $username, $password, $email) {
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $hashedPassword, $email]);
    } catch(PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ==========================================
// Task Management Functions
// ==========================================

function getTasks($pdo, $user_id, $status) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tasks 
                              WHERE user_id = ? AND status = ? 
                              ORDER BY due_date ASC");
        $stmt->execute([$user_id, $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting tasks: " . $e->getMessage());
        return [];
    }
}

function createTask($pdo, $user_id, $data) {
    try {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, 
                              priority, category, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $user_id,
            $data['title'],
            $data['description'],
            $data['due_date'],
            $data['priority'],
            $data['category'],
            'Pending'
        ]);
    } catch(PDOException $e) {
        error_log("Error creating task: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// Profile Functions
// ==========================================

function getUserProfile($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting profile: " . $e->getMessage());
        return null;
    }
}

function updateProfile($pdo, $user_id, $data) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        return $stmt->execute([$data['username'], $data['email'], $user_id]);
    } catch(PDOException $e) {
        error_log("Error updating profile: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// Settings Functions
// ==========================================

function getUserSettings($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting settings: " . $e->getMessage());
        return null;
    }
}

function updateSettings($pdo, $user_id, $settings) {
    try {
        $stmt = $pdo->prepare("UPDATE user_settings SET theme = ?, notifications = ? WHERE user_id = ?");
        return $stmt->execute([$settings['theme'], $settings['notifications'], $user_id]);
    } catch(PDOException $e) {
        error_log("Error updating settings: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// Shop Functions
// ==========================================

function getShopItems($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM shop_items WHERE available = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting shop items: " . $e->getMessage());
        return [];
    }
}

function purchaseItem($pdo, $user_id, $item_id) {
    try {
        $pdo->beginTransaction();
        
        // Check if user has enough points
        $stmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $userPoints = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT price FROM shop_items WHERE id = ?");
        $stmt->execute([$item_id]);
        $itemPrice = $stmt->fetchColumn();
        
        if ($userPoints >= $itemPrice) {
            // Deduct points
            $stmt = $pdo->prepare("UPDATE users SET points = points - ? WHERE id = ?");
            $stmt->execute([$itemPrice, $user_id]);
            
            // Add item to user's inventory
            $stmt = $pdo->prepare("INSERT INTO user_inventory (user_id, item_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $item_id]);
            
            $pdo->commit();
            return true;
        }
        
        $pdo->rollBack();
        return false;
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log("Error purchasing item: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// Daily Rewards Functions
// ==========================================

function canClaimDailyReward($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT last_reward_claim FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $lastClaim = $stmt->fetchColumn();
        
        if (!$lastClaim) return true;
        
        $lastClaimDate = new DateTime($lastClaim);
        $today = new DateTime();
        
        return $lastClaimDate->format('Y-m-d') < $today->format('Y-m-d');
    } catch(PDOException $e) {
        error_log("Error checking daily reward: " . $e->getMessage());
        return false;
    }
}

function claimDailyReward($pdo, $user_id) {
    try {
        $pdo->beginTransaction();
        
        // Update last claim time
        $stmt = $pdo->prepare("UPDATE users SET 
                              last_reward_claim = NOW(),
                              points = points + 100
                              WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Log the reward
        $stmt = $pdo->prepare("INSERT INTO reward_logs (user_id, points) VALUES (?, 100)");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        return true;
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log("Error claiming reward: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// Utility Functions
// ==========================================

function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        echo '<div class="message ' . $type . '">';
        echo $_SESSION['message'];
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

function setMessage($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function validateTaskData($data) {
    $errors = [];
    
    if (empty($data['title'])) {
        $errors[] = "Title is required";
    }
    
    if (empty($data['due_date'])) {
        $errors[] = "Due date is required";
    } elseif (strtotime($data['due_date']) < strtotime('today')) {
        $errors[] = "Due date cannot be in the past";
    }
    
    return $errors;
}

// ==========================================
// Statistics Functions
// ==========================================

function getTaskStats($pdo, $user_id) {
    $stats = [
        'total' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'deleted' => 0
    ];

    try {
        $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM tasks 
                              WHERE user_id = ? GROUP BY status");
        $stmt->execute([$user_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $status = strtolower(str_replace(' ', '_', $result['status']));
            $stats[$status] = $result['count'];
        }

        $stats['total'] = array_sum($stats);
        return $stats;
    } catch(PDOException $e) {
        error_log("Error getting task stats: " . $e->getMessage());
        return $stats;
    }
}

?>