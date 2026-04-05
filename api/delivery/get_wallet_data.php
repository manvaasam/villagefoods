<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';

header('Content-Type: application/json');

// Ensure user is logged in as delivery
checkPersistentLogin($pdo);
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'delivery') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Total Earnings (Regular + Rapid)
    $stmt = $pdo->prepare("SELECT SUM(COALESCE(delivery_earning, 0)) FROM orders WHERE delivery_boy_id = ? AND status = 'Delivered'");
    $stmt->execute([$user_id]);
    $regular_earnings = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->prepare("SELECT SUM(COALESCE(delivery_earning, 0)) FROM rapid_orders WHERE delivery_boy_id = ? AND status = 'Completed'");
    $stmt->execute([$user_id]);
    $rapid_earnings = $stmt->fetchColumn() ?: 0;

    $total_earnings = $regular_earnings + $rapid_earnings;

    // 2. Total Withdrawn (Success/Approved)
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM withdrawal_requests WHERE user_id = ? AND status = 'Approved'");
    $stmt->execute([$user_id]);
    $total_withdrawn = $stmt->fetchColumn() ?: 0;

    // 3. Pending Withdrawals
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM withdrawal_requests WHERE user_id = ? AND status = 'Pending'");
    $stmt->execute([$user_id]);
    $total_pending = $stmt->fetchColumn() ?: 0;

    $available_balance = $total_earnings - $total_withdrawn - $total_pending;

    // 4. Withdrawal Logs
    $stmt = $pdo->prepare("SELECT id, amount, status, created_at FROM withdrawal_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'available_balance' => round($available_balance, 2),
            'total_earnings' => round($total_earnings, 2),
            'total_withdrawn' => round($total_withdrawn, 2),
            'total_pending' => round($total_pending, 2),
            'logs' => $logs
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
