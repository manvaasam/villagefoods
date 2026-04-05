<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT id, order_number, delivery_boy_id, status FROM orders WHERE id < 58");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
