<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';
require_once '../../includes/rapid_helper.php';

// Cleanup timed-out assignments
RapidHelper::handleTimeouts($pdo);

requireRole(['delivery']);

try {
    // Check verification and availability status
    $stmt = $pdo->prepare("SELECT verification_status, status FROM delivery_partners WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $partner = $stmt->fetch();

    if (!$partner || $partner['verification_status'] !== 'Verified' || $partner['status'] !== 'available') {
        echo json_encode([]); // Return empty if not verified or busy
        exit;
    }

    // Fetch all Rapid Orders that are still in 'pending' status
    $stmt = $pdo->query("SELECT r.*, u.name as customer_name 
                        FROM rapid_orders r 
                        JOIN users u ON r.customer_id = u.id 
                        WHERE r.status = 'pending' AND r.delivery_boy_id IS NULL 
                        ORDER BY r.created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($orders);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
