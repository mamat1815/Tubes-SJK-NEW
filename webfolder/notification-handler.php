<?php
require_once 'config/config.php';
require_once 'vendor/autoload.php';

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-jGwdUeyFWGE4Kz-HQ4uhexCY';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Ambil data notifikasi dari Midtrans
$json = file_get_contents('php://input');
$notification = json_decode($json);

$transactionStatus = $notification->transaction_status;
$orderId = $notification->order_id;

// Update status di tabel purchases
$status = 'pending'; // Default status
if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
    $status = 'success';
} elseif ($transactionStatus == 'deny' || $transactionStatus == 'cancel' || $transactionStatus == 'expire') {
    $status = 'failed';
}

$updateQuery = "UPDATE purchases SET status = ? WHERE order_id = ?";
$stmtUpdate = $pdo->prepare($updateQuery);
$stmtUpdate->execute([$status, $orderId]);

http_response_code(200); // Beri respons ke Midtrans
?>
