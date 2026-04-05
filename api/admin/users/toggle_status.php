<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) throw new Exception('User ID is required');

    // Toggle logic
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) throw new Exception('User not found');

    $newStatus = ($user['status'] === 'Active') ? 'Blocked' : 'Active';

    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);

    echo json_encode(['success' => true, 'message' => "Customer is now $newStatus", 'newStatus' => $newStatus]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
