<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';
checkPersistentLogin($pdo);
requireRole(['admin']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = $data['order_id'] ?? null;
    $delivery_boy_id = $data['delivery_boy_id'] ?? null;

    if (!$order_id || !$delivery_boy_id) throw new Exception('Order ID and Delivery Boy ID are required');

    // Verify delivery boy exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'delivery'");
    $stmt->execute([$delivery_boy_id]);
    if (!$stmt->fetch()) throw new Exception('Invalid Delivery Boy ID');

    $stmt = $pdo->prepare('UPDATE orders SET delivery_boy_id = ? WHERE id = ?');
    $stmt->execute([$delivery_boy_id, $order_id]);

    echo json_encode(['success' => true, 'message' => 'Delivery partner assigned successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
