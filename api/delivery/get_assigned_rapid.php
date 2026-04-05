<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';

requireRole(['delivery']);

$delivery_boy_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'active';

try {
    // Check verification status first
    $stmt = $pdo->prepare("SELECT verification_status FROM delivery_details WHERE user_id = ?");
    $stmt->execute([$delivery_boy_id]);
    $vStatus = $stmt->fetchColumn();

    if ($vStatus !== 'Verified') {
        echo json_encode(['error' => 'Not Verified']);
        exit;
    }

    $query = "SELECT r.*, u.name as customer_name, u.phone as customer_phone 
              FROM rapid_orders r 
              JOIN users u ON r.customer_id = u.id 
              WHERE r.delivery_boy_id = ?";
    
    if ($status_filter === 'active') {
        $query .= " AND r.status IN ('Accepted', 'Picked', 'Delivering')";
    } elseif ($status_filter === 'done') {
        $query .= " AND r.status IN ('Completed', 'Cancelled')";
    }

    $query .= " ORDER BY r.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$delivery_boy_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mask PII
    foreach ($orders as &$order) {
        $isFinished = in_array($order['status'], ['Completed', 'Cancelled']);
        if ($isFinished && !empty($order['customer_phone'])) {
            $len = strlen($order['customer_phone']);
            if ($len > 4) {
                $order['customer_phone'] = str_repeat('X', $len - 4) . substr($order['customer_phone'], -4);
            } else {
                $order['customer_phone'] = 'XXXX';
            }
        }
    }

    echo json_encode($orders);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
