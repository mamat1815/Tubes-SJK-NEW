<?php
session_start();
require_once 'config/config.php';

// Ambil orderId dari URL
$orderId = $_GET['orderId'] ?? null;
if (!$orderId) {
    echo "Order ID is missing.";
    exit;
}

try {
    // Ambil data order
    $orderQuery = "
        SELECT 
    o.orderId, 
    o.name AS orderName, 
    o.description, 
    o.price, 
    o.workTime, 
    o.deadline, 
    o.status, 
    s.sellerId, 
    s.bio, 
    s.hourRate, 
    s.availability, 
    s.img AS sellerImage,
    u.fullName AS sellerName
    FROM 
        orders o
    JOIN 
        sellers s 
        ON o.sellerId = s.sellerId
    JOIN 
        users u 
        ON s.userId = u.id
    WHERE 
        o.orderId = :orderId

        ";
    $stmtOrder = $pdo->prepare($orderQuery);
    $stmtOrder->execute(['orderId' => $orderId]);
    $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found");
    }

    // Ambil foto order
    $photosQuery = "
        SELECT 
    imagePath 
    FROM 
    order_images 
    WHERE 
    orderId = :orderId

    ";
    $stmtPhotos = $pdo->prepare($photosQuery);
    $stmtPhotos->execute(['orderId' => $orderId]);
    $photos = $stmtPhotos->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    echo "Error fetching order details: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Detail Order</h1>

        <!-- Informasi Order -->
        <div class="card mb-4">
            <div class="card-body">
                <h3><?= htmlspecialchars($order['orderName']) ?></h3>
                <p><strong>Deskripsi:</strong> <?= htmlspecialchars($order['description']) ?></p>
                <p><strong>Harga:</strong> Rp<?= number_format($order['price'], 0, ',', '.') ?></p>
                <p><strong>Waktu Pengerjaan:</strong> <?= htmlspecialchars($order['workTime']) ?> jam</p>
                <p><strong>Deadline:</strong> <?= htmlspecialchars($order['deadline']) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
            </div>
        </div>

        <!-- Informasi Seller -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>Informasi Seller</h5>
                <p><strong>Nama:</strong> <?= htmlspecialchars($order['sellerName']) ?></p>
            </div>
        </div>

        <!-- Foto Order -->
        <?php if (!empty($photos)): ?>
            <div class="card">
                <div class="card-body">
                    <h5>Foto Order</h5>
                    <div id="orderImagesCarousel" class="carousel slide" data-bs-ride="carousel" style="max-width: 600px; margin: 0 auto;">
                    <div class="carousel-inner">
                     
                        <?php 
                        foreach ($photos as $index => $image): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img 
                                    src="<?= htmlspecialchars($image) ?>" 
                                    class="d-block w-100" 
                                    alt="Order Image" 
                                    style="height: 300px; object-fit: cover;">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#orderImagesCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#orderImagesCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>

                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Tidak ada foto untuk order ini.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
