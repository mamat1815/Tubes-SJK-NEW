<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Midtrans\Config;
use Midtrans\Notification;

// Konfigurasi Midtrans
Config::$serverKey = 'SB-Mid-server-jGwdUeyFWGE4Kz-HQ4uhexCY';
Config::$isProduction = false; // Sesuaikan dengan mode Anda
Config::$isSanitized = true;
Config::$is3ds = true;

try {
    // Mendapatkan notifikasi dari Midtrans
    $notification = new Notification();

    $transaction = $notification->transaction_status;
    $orderId = $notification->order_id;

    // Cek status pembayaran
    if ($transaction === 'capture' || $transaction === 'settlement') {
        // Pembayaran berhasil
        $query = "UPDATE purchases SET status = 'paid' WHERE transId = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$orderId]);
    } elseif ($transaction === 'pending') {
        // Pembayaran tertunda
        $query = "UPDATE purchases SET status = 'pending' WHERE transId = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$orderId]);
    } elseif ($transaction === 'expire' || $transaction === 'cancel') {
        // Pembayaran gagal atau dibatalkan
        $query = "UPDATE purchases SET status = 'failed' WHERE transId = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$orderId]);
    }

    http_response_code(200); // Beri response sukses ke Midtrans
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500); // Beri response error jika ada masalah
}
?>
