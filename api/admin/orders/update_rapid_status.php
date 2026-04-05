<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = !empty($data['id']) ? (int) $data['id'] : null;
    $status = !empty($data['status']) ? trim($data['status']) : null;

    if (!$id || !$status) {
        throw new Exception('Order ID and Status are required.');
    }

    $validStatuses = ['Requested', 'Accepted', 'Picked Up', 'Completed', 'Cancelled'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status.');
    }

    $stmt = $pdo->prepare('UPDATE rapid_orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
