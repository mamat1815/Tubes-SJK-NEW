<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['sellerId'])) {
    echo "<script>alert('Anda harus login sebagai seller.'); window.location.href = '../login.php';</script>";
    exit();
}

// Proses penambahan produk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sellerId = $_SESSION['sellerId'];
    $productName = htmlspecialchars($_POST['product_name']);
    $productDescription = htmlspecialchars($_POST['product_description']);
    $productPrice = floatval($_POST['product_price']);
    $productStock = intval($_POST['product_stock']);
    $uploadDir = __DIR__ . '/../uploads/products/';
    $uploadedImages = [];

    // Pastikan folder upload ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Buat folder jika belum ada
    }

    if (isset($_FILES['product_images']['tmp_name']) && is_array($_FILES['product_images']['tmp_name'])) {
        foreach ($_FILES['product_images']['tmp_name'] as $key => $tmpName) {
            if (!empty($tmpName)) {
                $imageExtension = pathinfo($_FILES['product_images']['name'][$key], PATHINFO_EXTENSION);
                $uniqueName = uniqid('product_', true) . '.' . $imageExtension; // Nama file unik
                $productImagePath = $uploadDir . $uniqueName;
    
                if (move_uploaded_file($tmpName, $productImagePath)) {
                    $uploadedImages[] = 'uploads/products/' . $uniqueName; // Simpan path ke array
                }
            }
        }
    }
    

    // Simpan produk ke database
    $query = "INSERT INTO products (sellerId, name, description, price, stock, rate, sold) VALUES (?, ?, ?, ?, ?, 0, 0)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$sellerId, $productName, $productDescription, $productPrice, $productStock]);
    $productId = $pdo->lastInsertId();

    // Simpan gambar ke tabel product_images
    foreach ($uploadedImages as $imagePath) {
        $imageQuery = "INSERT INTO product_images (productId, imagePath) VALUES (?, ?)";
        $stmtImage = $pdo->prepare($imageQuery);
        $stmtImage->execute([$productId, $imagePath]);
    }

    echo "<script>alert('Produk berhasil ditambahkan!');window.location.href = 'products.php';</script>";
    
    exit();

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/seller.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/charts.css/dist/charts.min.css">
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
        <h2 class="text-center mb-4">Tambah Produk Baru</h2>
        <form method="POST" action="" enctype="multipart/form-data" id="productForm">
            <div class="mb-3">
                <label for="product_name" class="form-label">Nama Produk</label>
                <input type="text" class="form-control" id="product_name" name="product_name" required>
            </div>
            <div class="mb-3">
                <label for="product_description" class="form-label">Deskripsi Produk</label>
                <textarea class="form-control" id="product_description" name="product_description" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label for="product_price" class="form-label">Harga Produk</label>
                <input type="number" step="0.01" class="form-control" id="product_price" name="product_price" required>
            </div>
            <div class="mb-3">
                <label for="product_stock" class="form-label">Stok Produk</label>
                <input type="number" class="form-control" id="product_stock" name="product_stock" required>
            </div>
            <div class="mb-3">
                <label for="product_images" class="form-label">Gambar Produk (Multiple)</label>
                <input type="file" class="form-control" id="product_images" name="product_images[]" multiple accept="image/*">
            </div>
            <div class="preview-container" id="imagePreviewContainer">
                <!-- Preview images will be displayed here -->
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-4">Tambahkan Produk</button>
        </form>
    </div>

    <script>
        const productImagesInput = document.getElementById('product_images');
        const previewContainer = document.getElementById('imagePreviewContainer');

        // Store files to upload
        let filesToUpload = [];

        productImagesInput.addEventListener('change', function(event) {
            const files = Array.from(event.target.files);

            // Append new files to filesToUpload
            filesToUpload = [...filesToUpload, ...files];

            // Update preview
            updatePreviews();
        });

        function updatePreviews() {
            // Clear existing previews
            previewContainer.innerHTML = '';

            filesToUpload.forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const card = document.createElement('div');
                    card.classList.add('preview-card');

                    const img = document.createElement('img');
                    img.src = e.target.result;

                    const removeBtn = document.createElement('button');
                    removeBtn.classList.add('remove-btn');
                    removeBtn.innerHTML = '&times;';
                    removeBtn.onclick = function() {
                        // Remove file from filesToUpload
                        filesToUpload.splice(index, 1);
                        updatePreviews(); // Refresh previews
                    };

                    card.appendChild(img);
                    card.appendChild(removeBtn);
                    previewContainer.appendChild(card);
                };

                reader.readAsDataURL(file);
            });
        }
    </script>
</body>
</html>
