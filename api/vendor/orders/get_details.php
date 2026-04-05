<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['vendor', 'admin', 'super_admin']);

$shop_id = $_SESSION['shop_id'];
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo json_encode(['success' => false, 'error' => 'Order ID is required']);
    exit;
}

try {
    // Get Order Details
    $stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, 
                                 COALESCE(u.phone, (SELECT contact_number FROM user_addresses WHERE user_id = u.id ORDER BY id DESC LIMIT 1)) as customer_phone, 
                                 u.email as customer_email 
                          FROM orders o 
                          JOIN users u ON o.user_id = u.id 
                          WHERE o.id = ? AND o.shop_id = ?");
    $stmt->execute([$order_id, $shop_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

    // Get Order Items
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true, 
        'order' => $order,
        'items' => $items
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
