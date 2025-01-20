<?php
session_start();
require_once 'config/config.php';

// Pastikan user sudah login
if (!isset($_SESSION['userId'])) {
    echo "<script>alert('Silakan login untuk melihat daftar pembelian.'); window.location.href = 'login.php';</script>";
    exit();
}

// Ambil userId dari sesi
$userId = $_SESSION['userId'];

// Ambil daftar pembelian pengguna dari database
$query = "SELECT p.productId, p.name AS productName, pu.quantity, pu.amountTotal, pu.purchaseDate, pu.status 
          FROM purchases pu
          LEFT JOIN products p ON pu.productId = p.productId
          WHERE pu.userId = ?
          ORDER BY pu.purchaseDate DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$purchases = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pembelian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require 'assets/components/navbar.php'; ?>

    <div class="container mt-5">
        <h1>Daftar Pembelian</h1>
        <?php if (count($purchases) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Total Harga</th>
                        <th>Tanggal Pembelian</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $index => $purchase): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($purchase['productName']); ?></td>
                            <td><?php echo $purchase['quantity']; ?></td>
                            <td>Rp<?php echo number_format($purchase['amountTotal'], 0, ',', '.'); ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($purchase['purchaseDate'])); ?></td>
                            <td><?php echo ucfirst($purchase['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">Belum ada pembelian.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
