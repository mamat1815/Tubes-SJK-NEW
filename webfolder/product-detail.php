<?php
session_start();
require_once 'config/config.php';

// Ambil ID produk dari URL
$productId = $_GET['id'] ?? null;

if (!$productId) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location.href = 'index.php';</script>";
    exit();
}

// Pastikan userId sudah tersedia di sesi
if (!isset($_SESSION['userId'])) {
    echo "<script>alert('Silakan login untuk melanjutkan.'); window.location.href = 'login.php';</script>";
    exit();
}

// Fetch detail pengguna dari database
$userId = $_SESSION['userId'];
$userQuery = "SELECT fullName, email, number, address FROM users WHERE id = ?";
$stmtUser = $pdo->prepare($userQuery);
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

if (!$user) {
    echo "<script>alert('Pengguna tidak ditemukan!'); window.location.href = 'logout.php';</script>";
    exit();
}

// Fetch detail produk
$query = "SELECT p.*, s.sellerId, s.bio, u.fullName AS sellerName 
          FROM products p
          LEFT JOIN sellers s ON p.sellerId = s.sellerId
          LEFT JOIN users u ON s.userId = u.id
          WHERE p.productId = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location.href = 'index.php';</script>";
    exit();
}

// Fetch images terkait produk
$imagesQuery = "SELECT imagePath FROM product_images WHERE productId = ?";
$stmtImages = $pdo->prepare($imagesQuery);
$stmtImages->execute([$productId]);
$productImages = $stmtImages->fetchAll();

// Proses pembayaran jika tombol "Beli Sekarang" diklik
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    $quantity = (int)$_POST['quantity'];
    if ($quantity > $product['stock']) {
        echo "<script>alert('Jumlah melebihi stok yang tersedia!'); window.location.href = 'product-detail.php?id=$productId';</script>";
        exit();
    }

    $totalAmount = $product['price'] * $quantity;
    $transId = uniqid('Product-');

    // Simpan data transaksi ke tabel purchases
    try {
        $purchaseQuery = "INSERT INTO purchases (transId, userId, productId, quantity, amountTotal, status, purchaseDate) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmtPurchase = $pdo->prepare($purchaseQuery);
        $stmtPurchase->execute([
            $transId,
            $userId,
            $product['productId'],
            $quantity,
            $totalAmount,
            'pending'
        ]);

        // Kurangi stok produk dan tambahkan jumlah terjual
        $updateStockQuery = "UPDATE products SET stock = stock - ?, sold = sold + ? WHERE productId = ?";
        $stmtUpdateStock = $pdo->prepare($updateStockQuery);
        $stmtUpdateStock->execute([$quantity, $quantity, $productId]);
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - <?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require 'assets/components/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <div class="mb-3">
                    <?php if (!empty($productImages)): ?>
                        <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($productImages as $index => $image): ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <img src="<?php echo htmlspecialchars($image['imagePath']); ?>" class="d-block w-100" alt="Product Image">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <img src="https://via.placeholder.com/600" class="img-fluid rounded" alt="Product Image">
                    <?php endif; ?>
                </div>
                <p><strong>Harga:</strong> Rp<?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                <p><strong>Stok:</strong> <?php echo $product['stock']; ?></p>
            </div>

            <div class="col-md-4">
                <!-- Form Pembelian -->
                <div class="card">
                    <div class="card-header">Beli Produk</div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Jumlah:</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
                            </div>
                            <button type="submit" name="pay_now" class="btn btn-success w-100">Beli Sekarang</button>
                        </form>
                    </div>
                </div>

                <!-- Informasi Penjual -->
                <div class="card mt-3">
                    <div class="card-header">Informasi Penjual</div>
                    <div class="card-body">
                        <h5 class="card-title">Penjual: <?php echo htmlspecialchars($product['sellerName']); ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
