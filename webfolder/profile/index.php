<?php
session_start();
require_once '../config/config.php';


if (!isset($_SESSION)) {
    echo "User not logged in.";
    exit();
}

$user_id = $_SESSION['userId'];

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

// Fetch statistik penjualan dan transaksi
$statsQuery = "SELECT COUNT(*) as total_sales, SUM(amount) as total_revenue FROM transactions WHERE sellerId = ?";
$stmt = $pdo->prepare($statsQuery);
$stmt->execute([$seller['sellerId']]);
$stats = $stmt->fetch();

// Fetch riwayat transaksi
$transactionsQuery = "SELECT * FROM transactions WHERE sellerId = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($transactionsQuery);
$stmt->execute([$seller['sellerId']]);
$transactions = $stmt->fetchAll();


// Statistik produk berdasarkan jumlah terjual
$productStatsQuery = "
    SELECT 
        COUNT(*) AS total_products, 
        SUM(sold) AS total_items_sold, 
        SUM(price * sold) AS total_revenue 
    FROM products 
    WHERE sellerId = ?";
$stmtProductStats = $pdo->prepare($productStatsQuery);
$stmtProductStats->execute([$seller['sellerId']]);
$productStats = $stmtProductStats->fetch();



// Statistik penjualan jasa
$serviceStatsQuery = "
    SELECT SUM(price) AS total_service_revenue, COUNT(*) AS total_service_orders 
    FROM orders 
    WHERE sellerId = ?";
$stmtServiceStats = $pdo->prepare($serviceStatsQuery);
$stmtServiceStats->execute([$seller['sellerId']]);
$serviceStats = $stmtServiceStats->fetch();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/seller.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/charts.css/dist/charts.min.css">
 

</head>
<body>
    <div class="sidebar">
        <a href="../"><h2>Dashboard Profile</h2></a>
        <a href="index.php"class="active">Dashboard</a>
        <a href="chats.php" >Chat</a>
        <a href="cart.php">Keranjang</a>
        <a href="config/functions/logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($user['fullName']); ?></h1>

        
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
