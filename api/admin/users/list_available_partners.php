<?php
header('Content-Type: application/json');
session_start();
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

requireRole(['admin']);

try {
    $stmt = $pdo->query("SELECT u.id, u.name, u.phone, dp.city, dp.area,
                                 (SELECT COUNT(*) FROM orders WHERE delivery_boy_id = u.id AND status NOT IN ('Delivered', 'Cancelled')) as active_orders,
                                 (SELECT COUNT(*) FROM rapid_orders WHERE delivery_boy_id = u.id AND status NOT IN ('Completed', 'Cancelled')) as active_rapid_orders,
                                 COALESCE((SELECT is_online FROM delivery_details WHERE user_id = u.id), 0) as is_online
                          FROM delivery_partners dp 
                          JOIN users u ON dp.user_id = u.id 
                          WHERE dp.verification_status = 'Verified' 
                          AND u.is_deleted = 0");

    $partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'partners' => $partners]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
