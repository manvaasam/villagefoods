<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';

requireRole(['delivery']);

try {
    // Check verification status first
    $stmt = $pdo->prepare("SELECT verification_status FROM delivery_details WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $vStatus = $stmt->fetchColumn();

    if ($vStatus !== 'Verified') {
        echo json_encode([]); // Return empty if not verified
        exit;
    }

    // Fetch all Rapid Orders that are still in 'Requested' status (not yet accepted)
    $stmt = $pdo->query("SELECT r.*, u.name as customer_name 
                        FROM rapid_orders r 
                        JOIN users u ON r.customer_id = u.id 
                        WHERE r.status = 'Requested' 
                        ORDER BY r.created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($orders);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
