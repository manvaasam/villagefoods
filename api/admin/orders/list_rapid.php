<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $stmt = $pdo->query("SELECT r.*, u.name as customer_name, u.phone as customer_phone, 
                                 dp_u.name as delivery_boy_name 
                          FROM rapid_orders r 
                          LEFT JOIN users u ON r.customer_id = u.id 
                          LEFT JOIN delivery_partners dp ON r.delivery_boy_id = dp.id
                          LEFT JOIN users dp_u ON dp.user_id = dp_u.id
                          ORDER BY r.created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($orders);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
