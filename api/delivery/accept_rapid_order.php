<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';

requireRole(['delivery']);

$data = json_decode(file_get_contents('php://input'), true);
$rapid_id = $data['rapid_id'] ?? null;
$delivery_boy_id = $_SESSION['user_id'];

if (!$rapid_id) {
    echo json_encode(['success' => false, 'error' => 'Rapid Order ID is required']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Check if the order is still available
    $stmt = $pdo->prepare("SELECT status FROM rapid_orders WHERE id = ? FOR UPDATE");
    $stmt->execute([$rapid_id]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception("Order not found");
    }

    if ($order['status'] !== 'Requested') {
        throw new Exception("Order already accepted by someone else");
    }

    // 2. Assign to delivery boy and update status
    $stmt = $pdo->prepare("UPDATE rapid_orders SET delivery_boy_id = ?, status = 'Accepted' WHERE id = ?");
    $stmt->execute([$delivery_boy_id, $rapid_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Order accepted successfully!']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
