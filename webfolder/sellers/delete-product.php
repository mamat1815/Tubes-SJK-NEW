<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['sellerId']) || $_SESSION['role'] !== 'seller') {
    echo "<script>alert('Kamu bukan penjual.'); window.location.href = '../index.php';</script>";
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID produk tidak ditemukan.'); window.location.href = 'products.php';</script>";
    exit();
}

$productId = intval($_GET['id']);
$sellerId = $_SESSION['sellerId'];

// Verifikasi produk milik seller
$query = "SELECT * FROM products WHERE productId = ? AND sellerId = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$productId, $sellerId]);
$product = $stmt->fetch();

if (!$product) {
    echo "<script>alert('Produk tidak ditemukan atau Anda tidak memiliki akses.'); window.location.href = 'products.php';</script>";
    exit();
}

try {
    // Hapus data gambar terkait di tabel product_images
    $deleteImagesQuery = "DELETE FROM product_images WHERE productId = ?";
    $stmtDeleteImages = $pdo->prepare($deleteImagesQuery);
    $stmtDeleteImages->execute([$productId]);

    // Hapus produk di tabel products
    $deleteProductQuery = "DELETE FROM products WHERE productId = ? AND sellerId = ?";
    $stmtDeleteProduct = $pdo->prepare($deleteProductQuery);
    $stmtDeleteProduct->execute([$productId, $sellerId]);

    echo "<script>alert('Produk berhasil dihapus!'); window.location.href = 'products.php';</script>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
