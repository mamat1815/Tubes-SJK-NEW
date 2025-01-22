<?php
session_start();
require_once __DIR__ . '/../config/config.php';


if (!isset($_SESSION)) {
    echo "User not logged in.";
    exit();
}

$user_id = $_SESSION['userId'];

if (!isset($pdo)) {
    echo "Database connection not found.";
    exit();
}

require __DIR__ . '/../config/functions/fetch-seller.php';

$seller = fetchSeller($user_id);
if (!$seller) {
    echo "Seller not found.";
    exit();
}

$userQuery = "SELECT * FROM users WHERE id = ?";
$stmtUser = $pdo->prepare($userQuery);
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

// Fetch data produk berdasarkan sellerId
$productQuery = "SELECT productId, name, price, stock, rate, sold FROM products WHERE sellerId = ?";
$stmtProduct = $pdo->prepare($productQuery);
$stmtProduct->execute([$seller['sellerId']]);
$products = $stmtProduct->fetchAll();

$chatQuery = "SELECT 
                 c.userId, 
                 c.sellerId, 
                 c.message, 
                 c.timestamp, 
                 u.username AS senderName 
              FROM orders c
              JOIN users u ON c.senderId = u.id
              WHERE c.senderId = ? OR c.receiverId = ?
              ORDER BY c.timestamp DESC";
// SELECT * FROM `order_messages` WHERE orderId = 16 ORDER BY `order_messages`.`created_at` ASC

$stmtChat = $pdo->prepare($chatQuery);
$stmtChat->execute([$user_id, $user_id]);
$chats = $stmtChat->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/chat.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/charts.css/dist/charts.min.css">
 

</head>
<body>
    <div class="sidebar">
        <a href="../"><h2>Chats</h2></a>
        <a href="index.php">Dashboard</a>
        <a href="chats.php"class="active" >Chat</a>
        <a href="cart.php">Keranjang</a>
        <a href="config/functions/logout.php">Logout</a>
    </div>

    <div class="main-content">
    <div class="chat-container">
        <h2>Chats</h2>
        <div class="chat-list">
            <?php if (count($chats) > 0): ?>
                <?php foreach ($chats as $chat): ?>
                    <div class="chat-item">
                        <p><strong><?= htmlspecialchars($chat['senderName']) ?>:</strong> <?= htmlspecialchars($chat['message']) ?></p>
                        <small><?= htmlspecialchars($chat['timestamp']) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No chats available.</p>
            <?php endif; ?>
        </div>
    </div>
                
        
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
