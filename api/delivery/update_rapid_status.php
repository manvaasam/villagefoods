<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';

requireRole(['delivery']);

$data = json_decode(file_get_contents('php://input'), true);
$rapid_id = $data['rapid_id'] ?? null;
$status = $data['status'] ?? null;
$delivery_boy_id = $_SESSION['user_id'];

if (!$rapid_id || !$status) {
    echo json_encode(['success' => false, 'error' => 'Rapid ID and status are required']);
    exit;
}

$allowedStatuses = ['Accepted', 'Picked', 'Delivering', 'Completed', 'Cancelled'];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit;
}

try {
    // Verify ownership
    $stmt = $pdo->prepare("SELECT delivery_boy_id FROM rapid_orders WHERE id = ?");
    $stmt->execute([$rapid_id]);
    $order = $stmt->fetch();

    if (!$order || $order['delivery_boy_id'] != $delivery_boy_id) {
        echo json_encode(['success' => false, 'error' => 'Order not assigned to you']);
        exit;
    }

    if ($status === 'Completed') {
        $stmt = $pdo->prepare("UPDATE rapid_orders SET status = ?, delivered_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $rapid_id]);
    } elseif ($status === 'Picked') {
        $stmt = $pdo->prepare("UPDATE rapid_orders SET status = ?, picked_up_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $rapid_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE rapid_orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $rapid_id]);
    }

    echo json_encode(['success' => true, 'message' => 'Status updated to ' . $status]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
