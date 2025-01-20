<?php
include_once __DIR__ . '/../config.php';

function fetchSeller($user_id) {
    global $pdo; 
    $sellerQuery = "SELECT * FROM sellers WHERE userId = ?";
    $stmt = $pdo->prepare($sellerQuery);
    $stmt->execute([$user_id]);
    $seller = $stmt->fetch();
    return $seller;
}

function fetchUser($user_id) {
    global $pdo; 
    $sellerQuery = "SELECT * FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sellerQuery);
    $stmt->execute([$user_id]);
    $seller = $stmt->fetch();
    return $seller;
}
?>
