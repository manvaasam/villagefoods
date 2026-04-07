<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';
require_once '../../includes/rapid_helper.php';

// Delivery boy role required
requireRole(['delivery']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = !empty($data['id']) ? (int)$data['id'] : null;
    $delivery_boy_user_id = $_SESSION['user_id'];

    if (!$order_id) throw new Exception('Order ID is required.');

    // Get the partner ID from users table join or similar
    $stmt = $pdo->prepare("SELECT id FROM delivery_partners WHERE user_id = ?");
    $stmt->execute([$delivery_boy_user_id]);
    $partner = $stmt->fetch();
    if (!$partner) throw new Exception('Delivery partner record not found.');
    $partner_id = $partner['id'];

    $pdo->beginTransaction();

    // Validate the order assignment
    $stmt = $pdo->prepare("SELECT status FROM rapid_orders WHERE id = ? AND delivery_boy_id = ? FOR UPDATE");
    $stmt->execute([$order_id, $delivery_boy_user_id]);
    $order = $stmt->fetch();

    if (!$order) throw new Exception('Order assignment not found or already changed.');
    if ($order['status'] !== 'assigned') {
        throw new Exception('Order cannot be accepted. Current status: ' . $order['status']);
    }

    // Update status to accepted
    $stmt = $pdo->prepare("UPDATE rapid_orders SET status = 'accepted', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$order_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Order accepted!']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
