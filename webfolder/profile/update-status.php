<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transId = $_POST['transId'];
    $status = $_POST['status'];

    try {
        $query = "UPDATE purchases SET status = ? WHERE transId = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$status, $transId]);

        echo json_encode(['success' => true, 'message' => 'Status transaksi diperbarui']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
