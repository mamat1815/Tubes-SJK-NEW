<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search = $_POST['search'] ?? '';
    $search = '%' . $search . '%';

    $query = "SELECT skillValue FROM skills WHERE skillValue LIKE ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$search]);
    $skills = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($skills);
}
?>
