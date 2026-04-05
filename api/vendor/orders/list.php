<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['vendor', 'admin', 'super_admin']);

$shop_id = $_SESSION['shop_id'];
$status = $_GET['status'] ?? 'all';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;

try {
    $query = "SELECT o.*, u.name as customer_name, 
                     COALESCE(u.phone, (SELECT contact_number FROM user_addresses WHERE user_id = u.id ORDER BY id DESC LIMIT 1)) as customer_phone 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.shop_id = ?";
    $params = [$shop_id];

    if ($status === 'active') {
        $query .= " AND o.status IN ('Placed', 'Pending', 'Confirmed', 'Preparing', 'Ready for Pickup')";
    } elseif ($status !== 'all') {
        $query .= " AND o.status = ?";
        $params[] = $status;
    }

    $query .= " ORDER BY o.created_at DESC";
    
    $finalLimit = $limit ? min($limit, 100) : 50;
    $query .= " LIMIT $finalLimit";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
