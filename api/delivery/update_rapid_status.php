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
    $new_status = !empty($data['status']) ? $data['status'] : null;
    $delivery_boy_user_id = $_SESSION['user_id'];

    if (!$order_id || !$new_status) throw new Exception('Order ID and Status are required.');

    $stmt = $pdo->prepare("SELECT id FROM delivery_partners WHERE user_id = ?");
    $stmt->execute([$delivery_boy_user_id]);
    $partner = $stmt->fetch();
    if (!$partner) throw new Exception('Delivery partner record not found.');
    $partner_id = $partner['id'];

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT status FROM rapid_orders WHERE id = ? AND delivery_boy_id = ? FOR UPDATE");
    $stmt->execute([$order_id, $delivery_boy_user_id]);
    $order = $stmt->fetch();

    if (!$order) throw new Exception('Order assignment not found.');

    // Transition validation
    if ($new_status === 'picked') {
        if ($order['status'] !== 'accepted') throw new Exception('Order must be accepted before picking.');
    } elseif ($new_status === 'completed') {
        if ($order['status'] !== 'picked') throw new Exception('Order must be picked up before completing.');
    } else {
        throw new Exception('Invalid status transition.');
    }

    // Update order
    $updateFields = "status = ?, updated_at = NOW()";
    if ($new_status === 'picked') $updateFields .= ", picked_up_at = NOW()";
    if ($new_status === 'completed') $updateFields .= ", delivered_at = NOW()";

    $stmt = $pdo->prepare("UPDATE rapid_orders SET $updateFields WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);

    // On completion, set delivery boy back to available
    if ($new_status === 'completed') {
        RapidHelper::syncStatus($pdo, $delivery_boy_user_id);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Status updated to ' . $new_status]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
