<?php
session_start();
require_once '../config/config.php';

// Pastikan sellerId sudah ada di session atau parameter
$sellerId = $_SESSION['sellerId'] ?? null;
if (!$sellerId) {
    echo "Seller ID is missing.";
    exit;
}

try {
    // Ambil data order untuk seller yang sedang login
    $query = "
        SELECT 
            orderId, 
            name, 
            workTime, 
            price, 
            deadline, 
            status
        FROM 
            orders 
        WHERE 
            sellerId = :sellerId
        ORDER BY createdAt DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['sellerId' => $sellerId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching orders: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/0444f7d0d8.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../assets/css/seller.css">
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard</h2>
        <a href="index.php">Dashboard</a>
        <a href="products.php" >Produk</a>
        <a href="services.php" class="active">Jasa</a>
        <a href="config/functions/logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Data Order Anda</h1>

        <section class="products">
        
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nama Order</th>
                    <th>Work Time</th>
                    <th>Harga</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data order.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td>
                                <input type="number" class="form-control form-control-sm" value="<?= htmlspecialchars($order['workTime']) ?>" onchange="updateField(<?= $order['orderId'] ?>, 'workTime', this.value)">
                                jam
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" value="<?= htmlspecialchars($order['price']) ?>" onchange="updateField(<?= $order['orderId'] ?>, 'price', this.value)">
                            </td>
                            <td><?= htmlspecialchars($order['deadline']) ?></td>
                            <td>
                                <select class="form-select form-select-sm" onchange="updateField(<?= $order['orderId'] ?>, 'status', this.value)">
                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="accepted" <?= $order['status'] === 'accepted' ? 'selected' : '' ?>>Diterima</option>
                                    <option value="rejected" <?= $order['status'] === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                                </select>
                            </td>
                            <td>
                                <a href="../order-detail.php?orderId=<?= $order['orderId'] ?>" class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </section>
    </div>

    <script>
        function updateField(orderId, field, value) {
            fetch('update-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ orderId, field, value })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal memperbarui data: ' + data.message);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat memperbarui data.');
                console.error(error);
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
