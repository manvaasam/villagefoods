<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $id = $_GET['id'] ?? null;
    if (!$id) throw new Exception("User ID required");

    // 1. Basic Info
    $stmt = $pdo->prepare("SELECT u.*, u.created_at AS joined_at,
                                 u.name as name,
                                 COALESCE(NULLIF(u.phone, ''), (SELECT contact_number FROM user_addresses WHERE user_id = u.id ORDER BY id DESC LIMIT 1)) as phone,
                                 COUNT(o.id) as total_orders, 
                                 COALESCE(SUM(o.grand_total), 0) as total_spent 
                          FROM users u 
                          LEFT JOIN orders o ON u.id = o.user_id 
                          WHERE u.id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) throw new Exception("User not found");

    // 2. Last Order Date
    $stmt = $pdo->prepare("SELECT created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$id]);
    $last_order = $stmt->fetch();

    // 3. Saved Addresses
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
    $stmt->execute([$id]);
    $addresses = $stmt->fetchAll();

    // 4. Order History
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$id]);
    $orders = $stmt->fetchAll();

    echo json_encode([
        'user' => $user,
        'last_order_date' => $last_order['created_at'] ?? null,
        'addresses' => $addresses,
        'orders' => $orders
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
