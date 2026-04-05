<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['vendor', 'admin', 'super_admin']);

$shop_id = $_SESSION['shop_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['orderId']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required data']);
    exit;
}

$orderId = (int)$data['orderId'];
$newStatus = $data['status'];

// Allowed statuses for vendor to set
$allowedStatuses = ['Confirmed', 'Ready for Pickup'];

if (!in_array($newStatus, $allowedStatuses)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid status transition for vendor']);
    exit;
}

try {
    // Ensure order belongs to this shop
    $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND shop_id = ?");
    $stmt->execute([$orderId, $shop_id]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found or access denied']);
        exit;
    }

    // Update status and timestamps
    if ($newStatus === 'Confirmed' && ($order['status'] === 'Placed' || $order['status'] === 'Pending')) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, vendor_accepted_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
    } elseif ($newStatus === 'Ready for Pickup' && $order['status'] !== 'Ready for Pickup') {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, ready_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
    } else {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
    }

    // Optional: Log transition or update timestamps
    // $pdo->prepare("UPDATE orders SET {$newStatus}_at = NOW() WHERE id = ?")->execute([$orderId]);

    echo json_encode([
        'success' => true, 
        'message' => "Order status updated to $newStatus",
        'status' => $newStatus
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
