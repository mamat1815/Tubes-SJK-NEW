<?php  
session_start();
require_once __DIR__ . '/../config/config.php';


if (!isset($_SESSION)) {
    echo "User not logged in.";
    exit();
}

if (!isset($_SESSION['sellerId']) || $_SESSION['role'] !== 'seller') {
    echo "<script>alert('Kamu bukan penjual'); window.location.href = '../index.php';</script>";
    exit;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/seller.css">
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard</h2>
        <a href="index.php">Dashboard</a>
        <a href="products.php" class="active">Produk</a>
        <a href="services.php">Jasa</a>
        <a href="config/functions/logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Data Produk Anda</h1>
        <div class="col-xl-3 mt-3 mb-3">
            <a href="add-product.php" class="btn btn-primary">Tambah Produk</a>
        </div>

        <section class="products">
            <h2>Daftar Produk</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Rate</th>
                        <th>Terjual</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>Rp<?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                <td><?php echo htmlspecialchars($product['rate']); ?></td>
                                <td><?php echo htmlspecialchars($product['sold']); ?></td>
                                <td>
                                    <a href="edit-product.php?id=<?php echo $product['productId']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete-product.php?id=<?php echo $product['productId']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus produk ini?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada produk.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
