<?php
session_start();
require_once '../../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'delivery') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $history = [];

    // 1. Regular Orders History
    $stmt = $pdo->prepare("SELECT 
        'food' as type,
        o.id,
        o.order_number as display_id,
        o.grand_total,
        COALESCE(o.delivery_earning, 0) as earning,
        o.status,
        o.created_at,
        o.picked_up_at,
        o.delivered_at,
        u.name as customer_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.delivery_boy_id = ? AND o.status IN ('Delivered', 'Cancelled')
    ORDER BY o.created_at DESC LIMIT 20");
    $stmt->execute([$userId]);
    $foodHistory = $stmt->fetchAll();

    // 2. Rapid Orders History
    $stmt = $pdo->prepare("SELECT 
        'rapid' as type,
        ro.id,
        CONCAT('RAPID #', ro.id) as display_id,
        ro.price as grand_total,
        ro.price as earning,
        ro.status,
        ro.created_at,
        ro.picked_up_at,
        ro.delivered_at,
        u.name as customer_name
    FROM rapid_orders ro
    JOIN users u ON ro.customer_id = u.id
    WHERE ro.delivery_boy_id = ? AND ro.status IN ('Completed', 'Cancelled')
    ORDER BY ro.created_at DESC LIMIT 20");
    $stmt->execute([$userId]);
    $rapidHistory = $stmt->fetchAll();

    // Combine and Sort
    $history = array_merge($foodHistory, $rapidHistory);
    usort($history, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    echo json_encode(array_slice($history, 0, 30));

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
