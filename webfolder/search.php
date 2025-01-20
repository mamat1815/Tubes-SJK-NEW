<!-- <?php
session_start();
require_once 'config/config.php';

// Ambil parameter filter dari GET atau POST
$searchType = $_GET['type'] ?? 'all'; // all, product, service
$minPrice = $_GET['min_price'] ?? 0;
$maxPrice = $_GET['max_price'] ?? 1000000;
$minRating = $_GET['min_rating'] ?? 0;
$searchQuery = $_GET['search_query'] ?? '';

$results = [];

// Query pencarian
if ($searchType === 'product') {
    $query = "SELECT p.*, pi.imagePath 
              FROM products p
              LEFT JOIN product_images pi ON p.productId = pi.productId
              WHERE p.price BETWEEN ? AND ? AND p.rate >= ? AND p.name LIKE ?
              ORDER BY p.rate DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$minPrice, $maxPrice, $minRating, "%$searchQuery%"]);
    $results = $stmt->fetchAll();
} elseif ($searchType === 'service') {
    $query = "SELECT s.*, u.fullName AS sellerName, u.img AS sellerImage, 
                     (SELECT GROUP_CONCAT(name SEPARATOR ', ') FROM products WHERE sellerId = s.sellerId LIMIT 3) AS products
              FROM sellers s
              LEFT JOIN users u ON s.userId = u.id
              WHERE s.rate >= ? AND u.fullName LIKE ?
              ORDER BY s.rate DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$minRating, "%$searchQuery%"]);
    $results = $stmt->fetchAll();
} else {
    $query = "SELECT 'product' AS type, p.name, p.price, p.rate, pi.imagePath, NULL AS sellerName
              FROM products p
              LEFT JOIN product_images pi ON p.productId = pi.productId
              WHERE p.price BETWEEN ? AND ? AND p.rate >= ? AND p.name LIKE ?
              UNION
              SELECT 'service' AS type, NULL AS name, NULL AS price, s.rate, u.img AS imagePath, u.fullName AS sellerName
              FROM sellers s
              LEFT JOIN users u ON s.userId = u.id
              WHERE s.rate >= ? AND u.fullName LIKE ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$minPrice, $maxPrice, $minRating, "%$searchQuery%", $minRating, "%$searchQuery%"]);
    $results = $stmt->fetchAll();
}

header('Content-Type: application/json');
echo json_encode($results); -->
