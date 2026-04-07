<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/rapid_helper.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = !empty($data['id']) ? (int) $data['id'] : null;
    $status = !empty($data['status']) ? trim($data['status']) : null;

    if (!$id || !$status) {
        throw new Exception('Order ID and Status are required.');
    }

    $validStatuses = ['pending', 'assigned', 'accepted', 'picked', 'completed', 'rejected'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status: ' . $status);
    }

    $pdo->beginTransaction();

    // Get current order info for syncing
    $stmt = $pdo->prepare("SELECT delivery_boy_id FROM rapid_orders WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    $stmt = $pdo->prepare('UPDATE rapid_orders SET status = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$status, $id]);

    // If order is finished or reset, sync the boy's status
    if (in_array($status, ['completed', 'rejected', 'pending']) && !empty($order['delivery_boy_id'])) {
        RapidHelper::syncStatus($pdo, $order['delivery_boy_id']);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
