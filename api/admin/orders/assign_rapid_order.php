<?php
header('Content-Type: application/json');
session_start();
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';
require_once '../../../includes/rapid_helper.php';

// Only admins can assign orders
requireRole(['admin']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = !empty($data['order_id']) ? (int)$data['order_id'] : null;
    $delivery_boy_id = !empty($data['delivery_boy_id']) ? (int)$data['delivery_boy_id'] : null;

    if (!$order_id || !$delivery_boy_id) {
        throw new Exception('Order ID and Delivery Boy ID are required.');
    }

    $pdo->beginTransaction();

    // 1. Validate order: must be 'pending' or 'rejected'
    $stmt = $pdo->prepare("SELECT status, delivery_boy_id FROM rapid_orders WHERE id = ? FOR UPDATE");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) throw new Exception('Order not found.');
    
    $status = (!empty($order['status'])) ? strtolower($order['status']) : 'pending';
    if (!in_array($status, ['pending', 'requested', 'rejected', 'assigned'])) {
        throw new Exception("Order is already in progress or completed (Current: {$order['status']}).");
    }

    $old_boy_id = $order['delivery_boy_id'];

    // 2. Validate delivery boy: must be 'available'
    $stmt = $pdo->prepare("SELECT id, status FROM delivery_partners WHERE user_id = ? FOR UPDATE");
    $stmt->execute([$delivery_boy_id]);
    $partner = $stmt->fetch();

    if (!$partner) throw new Exception('Delivery partner not found.');
    if ($partner['status'] !== 'available') {
        throw new Exception('Delivery partner is currently busy.');
    }

    // 3. Double check active orders for peace of mind
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rapid_orders 
                           WHERE delivery_boy_id = ? 
                           AND status IN ('assigned', 'accepted', 'picked')");
    $stmt->execute([$delivery_boy_id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Delivery partner unexpectedly has an active order.');
    }

    // 4. Perform Assignment
    $stmt = $pdo->prepare("UPDATE rapid_orders 
                           SET delivery_boy_id = ?, status = 'assigned', updated_at = NOW() 
                           WHERE id = ?");
    $stmt->execute([$delivery_boy_id, $order_id]);

    // 5. Sync delivery boy statuses
    RapidHelper::syncStatus($pdo, $delivery_boy_id); // Set new boy to 'busy'
    if ($old_boy_id && $old_boy_id != $delivery_boy_id) {
        RapidHelper::syncStatus($pdo, $old_boy_id); // Set old boy back to 'available' if clean
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Order assigned successfully.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
