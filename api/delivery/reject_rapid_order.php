<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';
require_once '../../includes/rapid_helper.php';

requireRole(['delivery']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = !empty($data['id']) ? (int)$data['id'] : null;
    $delivery_boy_user_id = $_SESSION['user_id'];

    if (!$order_id) throw new Exception('Order ID is required.');

    $stmt = $pdo->prepare("SELECT id FROM delivery_partners WHERE user_id = ?");
    $stmt->execute([$delivery_boy_user_id]);
    $partner = $stmt->fetch();
    if (!$partner) throw new Exception('Delivery partner record not found.');
    $partner_id = $partner['id'];

    $pdo->beginTransaction();

    // Only allow rejection if currently 'assigned'
    $stmt = $pdo->prepare("SELECT status FROM rapid_orders WHERE id = ? AND delivery_boy_id = ? FOR UPDATE");
    $stmt->execute([$order_id, $partner_id]);
    $order = $stmt->fetch();

    if (!$order) throw new Exception('Order assignment not found.');
    if ($order['status'] !== 'assigned') {
        throw new Exception('Cannot reject after accepting or picking up.');
    }

    // Update order status to rejected and reset assignment
    $stmt = $pdo->prepare("UPDATE rapid_orders SET status = 'rejected', delivery_boy_id = NULL, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$order_id]);

    // Set delivery boy back to available
    RapidHelper::syncStatus($pdo, $partner_id);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Order rejected.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
