<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'delivery') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$delivery_boy_id = $_SESSION['user_id'];
$status = $_GET['status'] ?? 'all';

try {
    // Check verification status first
    $stmt = $pdo->prepare("SELECT verification_status FROM delivery_details WHERE user_id = ?");
    $stmt->execute([$delivery_boy_id]);
    $vStatus = $stmt->fetchColumn();

    if ($vStatus !== 'Verified') {
        echo json_encode(['error' => 'Your account is not verified yet. Orders will be assigned once approved.']);
        exit;
    }

    $query = "SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, 
                     s.shop_name, s.address as shop_address, s.phone as shop_phone,
                     s.latitude as shop_lat, s.longitude as shop_lng 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              LEFT JOIN shops s ON o.shop_id = s.id
              WHERE o.delivery_boy_id = ?";
    
    $params = [$delivery_boy_id];

    if ($status === 'active') {
        $query .= " AND o.status IN ('Confirmed', 'Preparing', 'Ready for Pickup', 'Picked Up', 'On the Way')";
    } elseif ($status === 'done') {
        $query .= " AND o.status IN ('Delivered', 'Completed', 'Cancelled')";
    }

    $query .= " ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch items for each order and mask PII
    foreach ($orders as &$order) {
        $stmtItems = $pdo->prepare("SELECT oi.*, p.image_url 
                                   FROM order_items oi 
                                   LEFT JOIN products p ON oi.product_id = p.id 
                                   WHERE oi.order_id = ?");
        $stmtItems->execute([$order['id']]);
        $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // Mask PII
        $isFinished = in_array($order['status'], ['Delivered', 'Completed', 'Cancelled']);
        
        // Mask Email Always (Delivery partner doesn't need it)
        $order['customer_email'] = '***@***.***';
        
        // Mask Phone if finished
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
?>
