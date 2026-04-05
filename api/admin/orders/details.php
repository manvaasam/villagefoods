<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';
checkPersistentLogin($pdo);
requireRole(['admin']);

try {
    $order_id = $_GET['id'] ?? null;
    if (!$order_id) throw new Exception('Order ID is required');

    // 1. Get Order Details
    $stmt = $pdo->prepare('SELECT o.*, u.name as customer_name, 
                           COALESCE(u.phone, (SELECT contact_number FROM user_addresses WHERE user_id = u.id ORDER BY id DESC LIMIT 1)) as customer_phone, 
                           u.email as customer_email,
                           d.name as delivery_boy_name,
                           COALESCE(s.shop_name, s2.shop_name) as shop_name, 
                           COALESCE(s.address, s2.address) as shop_address,
                           COALESCE(s.phone, s2.phone) as shop_phone,
                           COALESCE(s.latitude, s2.latitude) as latitude, 
                           COALESCE(s.longitude, s2.longitude) as longitude
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.id 
                          LEFT JOIN users d ON o.delivery_boy_id = d.id 
                          LEFT JOIN shops s ON o.shop_id = s.id
                          LEFT JOIN order_items oi ON o.id = oi.order_id
                          LEFT JOIN products p ON oi.product_id = p.id
                          LEFT JOIN shops s2 ON p.shop_id = s2.id
                          WHERE o.id = ?
                          LIMIT 1');
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) throw new Exception('Order not found');

    // 2. Get Order Items
    $stmt = $pdo->prepare('SELECT oi.*, p.name as product_name, p.image_url 
                          FROM order_items oi 
                          LEFT JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?');
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
