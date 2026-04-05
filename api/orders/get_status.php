<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';

$order_id = $_GET['order_id'] ?? '';
$clean_id = strtoupper(trim($order_id));

if (!$clean_id) {
    echo json_encode(['error' => 'No order ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT status, picked_up_at, delivered_at, delivery_boy_id, user_id FROM orders WHERE order_number = ? OR order_number = ? OR id = ?");
    $stmt->execute([$clean_id, '#' . $clean_id, is_numeric($clean_id) ? $clean_id : 0]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['error' => 'Order not found']);
        exit;
    }

    // SECURITY: Ensure the requester is the owner, an admin, or the assigned delivery partner
    session_start();
    $requester_id = $_SESSION['user_id'] ?? null;
    $requester_role = $_SESSION['user_role'] ?? null;

    $is_owner = ($requester_id && $requester_id == $order['user_id']);
    $is_admin = ($requester_role === 'admin');
    $is_delivery = ($requester_role === 'delivery' && $requester_id == $order['delivery_boy_id']);

    if (!$is_owner && !$is_admin && !$is_delivery) {
        // Option: allow public tracking if status is generic, but keep it tight for now
        echo json_encode(['error' => 'Unauthorized access to this order status']);
        exit;
    }
    
    echo json_encode(['success' => true, 'status' => $order['status'], 'picked_up_at' => $order['picked_up_at'], 'delivered_at' => $order['delivered_at']]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
