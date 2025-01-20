<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skill = $_POST['skill'] ?? '';

    if (!empty($skill)) {
        $query = "INSERT INTO skills (skillValue) VALUES (?)";
        $stmt = $pdo->prepare($query);

        try {
            $stmt->execute([$skill]);
            echo json_encode(['success' => true, 'message' => 'Skill added successfully.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Skill already exists.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Skill cannot be empty.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
