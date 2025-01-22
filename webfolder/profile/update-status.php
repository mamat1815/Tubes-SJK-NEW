<?php
require_once __DIR__ . '/../config/config.php';

// Pastikan transId diterima dengan benar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transId'])) {
    $transId = $_POST['transId'];

    try {
        // Update status transaksi menjadi 'paid'
        $query = "UPDATE purchases SET status = 'paid' WHERE transId = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Status transaksi berhasil diperbarui!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tidak ada transaksi dengan ID tersebut!']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'transId tidak ditemukan!']);
}
?>
