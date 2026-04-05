<?php
session_start();
require_once '../../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'delivery') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    // 0. Get Verification Status
    $stmt = $pdo->prepare("SELECT verification_status FROM delivery_details WHERE user_id = ?");
    $stmt->execute([$userId]);
    $verification_status = $stmt->fetchColumn() ?: 'Pending';

    // 1. Regular Orders Stats
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total_count,
        SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered_count,
        SUM(CASE WHEN status NOT IN ('Delivered', 'Cancelled') THEN 1 ELSE 0 END) as active_count,
        SUM(COALESCE(delivery_earning, 0)) as total_earnings,
        SUM(CASE WHEN status = 'Delivered' AND DATE(delivered_at) = ? THEN 1 ELSE 0 END) as today_count,
        SUM(CASE WHEN status = 'Delivered' AND DATE(delivered_at) = ? THEN COALESCE(delivery_earning, 0) ELSE 0 END) as today_earnings
    FROM orders WHERE delivery_boy_id = ?");
    $stmt->execute([$today, $today, $userId]);
    $orderStats = $stmt->fetch();

    // 2. Rapid Orders Stats
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total_count,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as delivered_count,
        SUM(CASE WHEN status NOT IN ('Completed', 'Cancelled') THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN status = 'Completed' THEN price ELSE 0 END) as total_earnings,
        SUM(CASE WHEN status = 'Completed' AND DATE(delivered_at) = ? THEN 1 ELSE 0 END) as today_count,
        SUM(CASE WHEN status = 'Completed' AND DATE(delivered_at) = ? THEN price ELSE 0 END) as today_earnings
    FROM rapid_orders WHERE delivery_boy_id = ?");
    $stmt->execute([$today, $today, $userId]);
    $rapidStats = $stmt->fetch();

    // Combined Response
    $todayCount = ($orderStats['today_count'] ?? 0) + ($rapidStats['today_count'] ?? 0);
    $todayEarnings = ($orderStats['today_earnings'] ?? 0) + ($rapidStats['today_earnings'] ?? 0);
    
    $response = [
        'pending_count' => ($orderStats['active_count'] ?? 0) + ($rapidStats['active_count'] ?? 0),
        'delivered_count' => ($orderStats['delivered_count'] ?? 0) + ($rapidStats['delivered_count'] ?? 0),
        'today_delivered_count' => $todayCount,
        'total_earnings' => ($orderStats['total_earnings'] ?? 0) + ($rapidStats['total_earnings'] ?? 0),
        'today_earnings' => $todayEarnings,
        'avg_per_delivery' => 0,
        'today_avg' => $todayCount > 0 ? round($todayEarnings / $todayCount, 2) : 0,
        'verification_status' => $verification_status
    ];

    $totalDelivered = $response['delivered_count'];
    if ($totalDelivered > 0) {
        $response['avg_per_delivery'] = round($response['total_earnings'] / $totalDelivered, 2);
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
