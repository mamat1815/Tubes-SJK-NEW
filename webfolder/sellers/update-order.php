<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

// Cek metode permintaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Ambil data dari request body
$input = json_decode(file_get_contents('php://input'), true);
$orderId = $input['orderId'] ?? null;
$field = $input['field'] ?? null;
$value = $input['value'] ?? null;

if (!$orderId || !$field || $value === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Validasi kolom yang diperbolehkan untuk diupdate
$allowedFields = ['workTime', 'price', 'status'];
if (!in_array($field, $allowedFields)) {
    echo json_encode(['success' => false, 'message' => 'Invalid field.']);
    exit;
}

try {
    // Update data di tabel orders
    $query = "UPDATE orders SET $field = :value WHERE orderId = :orderId";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['value' => $value, 'orderId' => $orderId]);


} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
