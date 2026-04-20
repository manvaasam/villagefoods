<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';

requireRole(['delivery']);

$user_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'active';

try {
    // 1. Get Partner ID and check verification status
    $stmt = $pdo->prepare("SELECT id, verification_status FROM delivery_partners WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $partner = $stmt->fetch();

    if (!$partner) {
        echo json_encode(['error' => 'Partner profile not found']);
        exit;
    }

    if ($partner['verification_status'] !== 'Verified') {
        echo json_encode(['error' => 'Not Verified']);
        exit;
    }

    $partner_id = $partner['id'];

    $query = "SELECT r.*, u.name as customer_name, 
                     COALESCE(NULLIF(u.phone, ''), (SELECT contact_number FROM user_addresses WHERE user_id = u.id ORDER BY id DESC LIMIT 1)) as customer_phone 
              FROM rapid_orders r 
              JOIN users u ON r.customer_id = u.id 
              WHERE r.delivery_boy_id = ?";
    
    if ($status_filter === 'active') {
        // Active: assigned (newly assigned), accepted (accepted by boy), picked (item picked)
        $query .= " AND r.status IN ('assigned', 'accepted', 'picked')";
    } elseif ($status_filter === 'done') {
        $query .= " AND r.status IN ('completed', 'rejected')";
    }

    $query .= " ORDER BY r.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mask PII
    foreach ($orders as &$order) {
        $isFinished = in_array($order['status'], ['completed', 'rejected']);
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
