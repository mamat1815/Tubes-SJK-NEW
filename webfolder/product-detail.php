<?php
session_start();
require_once 'config/config.php';
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('File vendor/autoload.php tidak ditemukan. Pastikan Composer sudah dijalankan.');
}
require_once __DIR__ . '/vendor/autoload.php';

// require_once 'vendor/autoload.php'; // Midtrans library

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-jGwdUeyFWGE4Kz-HQ4uhexCY';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

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

    // Data transaksi untuk Midtrans
    $transactionDetails = [
        'order_id' => uniqid('ORDER-'),
        'gross_amount' => $totalAmount,
    ];

    $itemDetails = [
        [
            'id' => $product['productId'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'name' => $product['name'],
        ]
    ];

    $customerDetails = [
        'first_name' => $user['fullName'],
        'email' => $user['email'],
        'phone' => $user['number'],
        'billing_address' => [
            'first_name' => $user['fullName'],
            'address' => $user['address'],
            'city' => 'Kota Anda',
            'postal_code' => '12345',
            'phone' => $user['number'],
            'country_code' => 'IDN',
        ],
    ];

    // Generate Snap Token
    $snapToken = \Midtrans\Snap::getSnapToken([
        'transaction_details' => $transactionDetails,
        'item_details' => $itemDetails,
        'customer_details' => $customerDetails,
    ]);

    // Masukkan data transaksi ke tabel purchases
    try {
        $purchaseQuery = "INSERT INTO purchases (userId, productId, quantity, amountTotal, status, purchaseDate, transId) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
        $stmtPurchase = $pdo->prepare($purchaseQuery);
        $stmtPurchase->execute([
            $userId,
            $product['productId'],
            $quantity,
            $totalAmount,
            'pending',
            $transactionDetails['order_id']
        ]);
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
        exit();
    }
    
    

    // Kurangi stok produk dan tambahkan jumlah terjual
    $updateStockQuery = "UPDATE products SET stock = stock - ?, sold = sold + ? WHERE productId = ?";
    $stmtUpdateStock = $pdo->prepare($updateStockQuery);
    $stmtUpdateStock->execute([$quantity, $quantity, $productId]);

    // Masukkan data transaksi ke tabel transactions
    $transactionQuery = "INSERT INTO transactions (sellerId, status, amount, created_at) VALUES (?, ?, ?, NOW())";
    $stmtTransaction = $pdo->prepare($transactionQuery);
    $stmtTransaction->execute([
        $product['sellerId'], // sellerId dari produk
        'initialized',        // Status awal transaksi
        $totalAmount          // Jumlah total transaksi
    ]);

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - <?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-SpDoGrdSAGp4NAiK"></script>
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

    <?php if (isset($snapToken)): ?>
        <script>
            snap.pay('<?php echo $snapToken; ?>', {
                onSuccess: function(result) {
                    alert('Pembayaran berhasil!');
                    window.location.href = 'success.php?order_id=<?php echo $transactionDetails['order_id']; ?>';
                },
                onPending: function(result) {
                    alert('Menunggu pembayaran.');
                },
                onError: function(result) {
                    alert('Pembayaran gagal!');
                }
            });
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
