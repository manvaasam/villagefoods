<?php
require_once 'includes/db.php';
$userId = 27;

$stmt = $pdo->prepare("SELECT id, order_number, total_amount, delivery_charge, delivery_earning, status, created_at FROM orders WHERE delivery_boy_id = ?");
$stmt->execute([$userId]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
