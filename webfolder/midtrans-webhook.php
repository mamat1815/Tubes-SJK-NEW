<?php
require_once 'config/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Set konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-jGwdUeyFWGE4Kz-HQ4uhexCY';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;

// Baca body request
$input = file_get_contents("php://input");
$notif = json_decode($input, true);

if (!$notif) {
    http_response_code(400);
    echo "Invalid request";
    exit();
}

// Handle notifikasi
$orderId = $notif['order_id'] ?? null;
$status = $notif['transaction_status'] ?? null;

if ($orderId && $status) {
    $statusMap = [
        'capture' => 'completed',
        'settlement' => 'completed',
        'pending' => 'pending',
        'deny' => 'failed',
        'expire' => 'failed',
        'cancel' => 'canceled',
    ];

    $mappedStatus = $statusMap[$status] ?? 'unknown';

    try {
        $updatePurchaseQuery = "UPDATE purchases SET status = ? WHERE transId = ?";
        $stmt = $pdo->prepare($updatePurchaseQuery);
        $stmt->execute([$mappedStatus, $orderId]);

        http_response_code(200);
        echo "Transaction status updated successfully.";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error updating transaction: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    echo "Missing order ID or status.";
}
