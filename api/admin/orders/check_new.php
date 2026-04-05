<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    // 1. Get Pending Order Count ('Placed' are new orders, 'Pending' are being processed)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status IN ('Placed', 'Pending')");
    $pending_count = $stmt->fetch()['total'] ?? 0;

    // 2. Get Latest Order ID (for regular orders)
    $stmt = $pdo->query("SELECT id FROM orders ORDER BY id DESC LIMIT 1");
    $latest_order = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. New Rapid Orders (Requested)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rapid_orders WHERE status = 'Requested'");
    $pending_rapid = $stmt->fetch()['total'] ?? 0;

    $stmt = $pdo->query("SELECT id FROM rapid_orders ORDER BY id DESC LIMIT 1");
    $latest_rapid = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Latest Status Update (for any order)
    $stmt = $pdo->query("SELECT order_number, status, UNIX_TIMESTAMP(updated_at) as last_upd FROM orders ORDER BY updated_at DESC LIMIT 1");
    $latest_upd_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $latest_update = $latest_upd_row['last_upd'] ?? 0;
    $latest_upd_num = $latest_upd_row['order_number'] ?? '';
    $latest_upd_status = $latest_upd_row['status'] ?? '';

    // 5. Pending Withdrawals
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM withdrawal_requests WHERE status = 'Pending'");
    $pending_withdrawals = $stmt->fetch()['total'] ?? 0;

    // 6. Pending Refunds
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE payment_status = 'Refund Pending'");
    $pending_refunds = $stmt->fetch()['total'] ?? 0;

    echo json_encode([
        'status' => 'success',
        'pending_count' => (int)$pending_count,
        'latest_id' => $latest_order ? (int)$latest_order['id'] : 0,
        'pending_rapid' => (int)$pending_rapid,
        'latest_rapid_id' => $latest_rapid ? (int)$latest_rapid['id'] : 0,
        'latest_update' => (int)$latest_update,
        'latest_upd_num' => $latest_upd_num,
        'latest_upd_status' => $latest_upd_status,
        'pending_withdrawals' => (int)$pending_withdrawals,
        'pending_refunds' => (int)$pending_refunds
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
