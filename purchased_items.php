<?php
session_start();
header('X-Content-Type-Options: nosniff');
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT 
        up.id as purchase_id,
        up.used,
        up.used_date,
        up.purchase_date,
        si.name as item_name,
        si.description,
        si.category,
        si.effect
    FROM user_purchases up
    JOIN shop_items si ON up.item_id = si.id
    WHERE up.user_id = ?
    ORDER BY up.purchase_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Purchased Items</title>
    <style>
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .item-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.2s ease;
        }
        .item-card:hover {
            transform: translateY(-5px);
        }
        .item-card button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .item-card button:hover {
            background-color: #45a049;
        }
        .used-status {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
    <main>
        <h1>My Purchased Items</h1>
        
        <?php if (empty($purchases)): ?>
            <p role="alert" style="text-align: center;">You haven't purchased any items yet.</p>
        <?php else: ?>
            <div class="items-grid" role="list">
                <?php foreach ($purchases as $item): ?>
                    <div class="item-card" role="listitem">
                        <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <p>Category: <?php echo htmlspecialchars($item['category']); ?></p>
                        <p>Purchased: <?php echo date('M j, Y', strtotime($item['purchase_date'])); ?></p>
                        
                        <?php if (!$item['used']): ?>
                            <button 
                                onclick="useItem(<?php echo $item['purchase_id']; ?>, '<?php echo htmlspecialchars($item['category']); ?>')"
                                class="use-item-btn"
                            >
                                Use Item
                            </button>
                        <?php else: ?>
                            <p class="used-status">
                                Used on: <?php echo date('M j, Y', strtotime($item['used_date'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
    function useItem(purchaseId, category) {
        if (confirm('Are you sure you want to use this item?')) {
            fetch('handle_use_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    purchase_id: purchaseId,
                    category: category
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Error using item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while using the item');
            });
        }
    }
    </script>
</body>
</html>