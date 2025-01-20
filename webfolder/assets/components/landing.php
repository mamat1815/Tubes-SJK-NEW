<?php

// Fetch data produk dan gambar dari database
$querys = "SELECT p.productId, p.name, p.price, p.stock, p.rate, p.sold, MIN(pi.imagePath) AS imagePath 
FROM products p 
LEFT JOIN product_images pi ON p.productId = pi.productId 
GROUP BY p.productId, p.name, p.price, p.stock, p.rate, p.sold 
ORDER BY p.productId DESC
";
$stmt = $pdo->prepare($querys);
$stmt->execute();
$products = $stmt->fetchAll();

$sellerQuery = "SELECT s.sellerId, u.fullName AS userName, s.bio, s.img AS imagePath, s.rate AS rating 
                FROM sellers s 
                JOIN users u ON s.userId = u.id 
                ORDER BY s.rate DESC 
                LIMIT 5";


$sellerStmt = $pdo->prepare($sellerQuery);
$sellerStmt->execute();
$topSellers = $sellerStmt->fetchAll();
?>
<div class="row ms-2 me-2 mt-3" id="top-sellers">
    <div class="col-12">
        <h2>Top Sellers</h2>
        <div class="scrolling-wrapper row row-cols-1 row-cols-md-3 g-4">
            <!-- Loop through top sellers -->
            <?php foreach ($topSellers as $seller): ?>
                <div class="col-md-3">
                    <a class="text-decoration-none" href="seller-detail.php?id=<?php echo $seller['sellerId']; ?>">
                        <div class="card h-100 text-center">
                            <img src="<?php echo htmlspecialchars($seller['imagePath'] ?? 'https://via.placeholder.com/150'); ?>" 
                                class="card-img-top" alt="Seller Image">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($seller['userName']); ?></h5>
                                <p class="card-text">
                                    <strong>Bio:</strong> <?php echo htmlspecialchars(substr($seller['bio'], 0, 50)) . (strlen($seller['bio']) > 50 ? '...' : ''); ?><br>
                                    <strong>Rating:</strong> <?php echo number_format($seller['rating'], 1); ?> ⭐
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


        <!-- Products -->
        <div class="row ms-4 me-4" id="products">
            <div class="col-12">
                <h2>Our Best Products</h2>
                <div class="scrolling-wrapper row row-cols-1 row-cols-md-3 g-4">
                    <!-- Loop through products -->
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-3">
                            <a class="text-decoration-none" href="product-detail.php?id=<?php echo $product['productId']; ?>">
                                <div class="card h-100 ">
                                    <img src="<?php echo htmlspecialchars($product['imagePath'] ?? 'https://via.placeholder.com/150'); ?>" 
                                        class="card-img-top" alt="Product Image">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <p class="card-text">
                                            <strong>Price:</strong> Rp<?php echo number_format($product['price'], 0, ',', '.'); ?><br>
                                            <strong>Stock:</strong> <?php echo $product['stock']; ?><br>
                                            <strong>Sold:</strong> <?php echo $product['sold']; ?><br>
                                            <strong>Rate:</strong> <?php echo $product['rate']; ?> ⭐
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>