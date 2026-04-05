<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['vendor']);

$shop_id = $_SESSION['shop_id'];

try {
    // Today's Orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE shop_id = ? AND DATE(created_at) = CURDATE()");
    $stmt->execute([$shop_id]);
    $todayOrders = $stmt->fetchColumn();

    // Today's Revenue (Net Earnings after 20% commission)
    $stmt = $pdo->prepare("SELECT SUM(vendor_earning) FROM orders WHERE shop_id = ? AND DATE(created_at) = CURDATE() AND status != 'Cancelled'");
    $stmt->execute([$shop_id]);
    $todayRevenue = $stmt->fetchColumn() ?: 0;

    // Average Preparation Time in Minutes (from Confirmed to Ready for Pickup)
    $stmt = $pdo->prepare("SELECT AVG(TIMESTAMPDIFF(MINUTE, vendor_accepted_at, ready_at)) FROM orders WHERE shop_id = ? AND vendor_accepted_at IS NOT NULL AND ready_at IS NOT NULL");
    $stmt->execute([$shop_id]);
    $avgPrepMin = $stmt->fetchColumn();
    $avgPrep = $avgPrepMin ? round($avgPrepMin) . "m" : "--";

    echo json_encode([
        'success' => true,
        'stats' => [
            'todayOrders' => $todayOrders,
            'todayRevenue' => (float)$todayRevenue,
            'avgPrep' => $avgPrep
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
