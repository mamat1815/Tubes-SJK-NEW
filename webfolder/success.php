<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/config.php';
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('File vendor/autoload.php tidak ditemukan. Pastikan Composer sudah dijalankan.');
}
require_once __DIR__ . '/vendor/autoload.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['userId'])) {
    echo "<script>alert('Silakan login untuk melanjutkan.'); window.location.href = 'login.php';</script>";
    exit();
}

$userId = $_SESSION['userId'];
$orderId = $_GET['order_id'] ?? null;

// Cek apakah order_id tersedia
if (!$orderId) {
    echo "<script>alert('ID pesanan tidak valid.'); window.location.href = 'index.php';</script>";
    exit();
}


$query = "SELECT p.name AS productName, pu.quantity, pu.totalAmount, pu.status 
          FROM purchases pu
          LEFT JOIN products p ON pu.productId = p.productId
          WHERE pu.transId = ? AND pu.userId = ?";
$stmt = $pdo->prepare($query);

try {
    $stmt->execute([$orderId, $userId]);
} catch (PDOException $e) {
    die('Error Query: ' . $e->getMessage());
}

$transaction = $stmt->fetch();
if (!$transaction) {
    die('Transaksi tidak ditemukan.');
}

// // Ambil detail transaksi dari database
// $query = "SELECT p.name AS productName, pu.quantity, pu.totalAmount, pu.status 
//           FROM purchases pu
//           LEFT JOIN products p ON pu.productId = p.productId
//           WHERE pu.transId = ? AND pu.userId = ?";
// $stmt = $pdo->prepare($query);
// $stmt->execute([$orderId, $userId]);
// $transaction = $stmt->fetch();

if (!$transaction) {
    echo "<script>alert('Pesanan tidak ditemukan.'); window.location.href = 'index.php';</script>";
    exit();
}

// Ambil status transaksi
$status = $transaction['status'];
$statusMessage = '';
switch ($status) {
    case 'capture':
    case 'settlement':
        $statusMessage = 'Pembayaran berhasil! Terima kasih telah berbelanja.';
        break;
    case 'pending':
        $statusMessage = 'Menunggu pembayaran. Mohon selesaikan pembayaran Anda.';
        break;
    case 'deny':
    case 'expire':
    case 'cancel':
        $statusMessage = 'Pembayaran gagal atau dibatalkan.';
        break;
    default:
        $statusMessage = 'Status pembayaran tidak diketahui.';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require 'assets/components/navbar.php'; ?>
    <div class="container mt-5">
        <div class="text-center">
            <h1>Status Pesanan</h1>
            <p class="lead"><?php echo htmlspecialchars($statusMessage); ?></p>
        </div>
        <div class="card mx-auto mt-4" style="max-width: 500px;">
            <div class="card-body">
                <h5 class="card-title">Detail Pesanan</h5>
                <p><strong>Produk:</strong> <?php echo htmlspecialchars($transaction['productName']); ?></p>
                <p><strong>Jumlah:</strong> <?php echo htmlspecialchars($transaction['quantity']); ?></p>
                <p><strong>Total Pembayaran:</strong> Rp<?php echo number_format($transaction['totalAmount'], 0, ',', '.'); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($status); ?></p>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
