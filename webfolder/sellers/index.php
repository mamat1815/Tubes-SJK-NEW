<?php
session_start();
require_once '../config/config.php';


if (!isset($_SESSION)) {
    echo "User not logged in.";
    exit();
}

if (!isset($_SESSION['sellerId']) || $_SESSION['role'] !== 'seller') {
    echo "<script>alert('Kamu bukan penjual'); window.location.href = '../index.php';</script>";
    exit;
}

$user_id = $_SESSION['userId'];

// Pastikan variabel $pdo tersedia
if (!isset($pdo)) {
    echo "Database connection not found.";
    exit();
}

// Fetch data seller
// $sellerQuery = "SELECT * FROM sellers WHERE userId = ?";
// $stmt = $pdo->prepare($sellerQuery);
// $stmt->execute([$user_id]);
// $seller = $stmt->fetch();
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
        <h2>Dashboard</h2>
        <a href="index.php"class="active">Dashboard</a>
        <a href="products.php" >Produk</a>
        <a href="services.php">Jasa</a>
        <a href="config/functions/logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($user['fullName']); ?></h1>
        
        <!-- <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <a href="http://">
                        <div class="card-body">
                            <p>Design Request</p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <a href="http://">
                        <div class="card-body">
                            <p>Total Penjualan: <?php echo $stats['total_sales']; ?></p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <a href="http://">
                        <div class="card-body">
                            <p>Total Pendapatan: Rp<?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <a href="http://">
                        <div class="card-body">
                            <p>Transaksi Terbaru: <?php echo count($transactions); ?></p>
                        </div>
                    </a>
                </div>
            </div>

           
        </div> -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4>Statistik Produk</h4>
                        <p>Total Produk: <?php echo $productStats['total_products']; ?></p>
                        <p>Total Item Terjual: <?php echo $productStats['total_items_sold']; ?></p>
                        <p>Total Pendapatan: Rp<?php echo number_format($productStats['total_revenue'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4>Jasa</h4>
                        <p>Total Pesanan Jasa: <?php echo $serviceStats['total_service_orders']; ?></p>
                        <p>Total Pendapatan Jasa: Rp<?php echo number_format($serviceStats['total_service_revenue'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12 card">
                <div class="card-body">
                    <h2>Statistik Pendapatan</h2>
                    <table class="charts-css column show-labels">
                        <caption>Pendapatan Produk dan Jasa</caption>
                        <tbody>
                            <tr>
                                <td style="--size: <?php echo $productStats['total_revenue'] / max(1, $productStats['total_revenue'] + $serviceStats['total_service_revenue']); ?>;">
                                    Produk (Rp<?php echo number_format($productStats['total_revenue'], 0, ',', '.'); ?>)
                                </td>
                            </tr>
                            <tr>
                                <td style="--size: <?php echo $serviceStats['total_service_revenue'] / max(1, $productStats['total_revenue'] + $serviceStats['total_service_revenue']); ?>;">
                                    Jasa (Rp<?php echo number_format($serviceStats['total_service_revenue'], 0, ',', '.'); ?>)
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>


            </div>
        </div>


        <section class="transactions">
            <h2>Riwayat Transaksi</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Status</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                            <td>Rp<?php echo number_format($transaction['amount'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
