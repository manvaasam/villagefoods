<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'] ?? null;
$newName = trim($data['name'] ?? '');

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if (empty($newName)) {
    echo json_encode(['status' => 'error', 'message' => 'Name cannot be empty']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
    $stmt->execute([$newName, $userId]);

    $_SESSION['user_name'] = $newName;

    echo json_encode([
        'status' => 'success',
        'message' => 'Profile updated successfully!',
        'name' => $newName
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
