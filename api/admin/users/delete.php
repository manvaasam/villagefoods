<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) throw new Exception('User ID is required');

    // Prevent deleting self (if session tracking is implemented, but for now simple)
    // Check if user has orders before deleting
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? OR delivery_boy_id = ? LIMIT 1");
    $stmt->execute([$id, $id]);
    if ($stmt->fetch()) {
        throw new Exception('Cannot delete user with existing orders. Deactivate them instead.');
    }

    $stmt = $pdo->prepare("UPDATE users SET is_deleted = 1 WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
