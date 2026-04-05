<?php
require_once 'includes/db.php';
$userId = 27;

$res = [];

// 1. Regular Orders Earnings
$stmt = $pdo->prepare("SELECT id, order_number, delivery_earning, status FROM orders WHERE delivery_boy_id = ? AND status = 'Delivered'");
$stmt->execute([$userId]);
$res['regular_orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Rapid Orders Earnings
$stmt = $pdo->prepare("SELECT id, delivery_earning, status FROM rapid_orders WHERE delivery_boy_id = ? AND status = 'Completed'");
$stmt->execute([$userId]);
$res['rapid_orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Withdrawal Requests
$stmt = $pdo->prepare("SELECT id, amount, status, created_at FROM withdrawal_requests WHERE user_id = ?");
$stmt->execute([$userId]);
$res['withdrawals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($res, JSON_PRETTY_PRINT);
?>
