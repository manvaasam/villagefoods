<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT id, delivery_boy_id, status FROM rapid_orders WHERE id < 6");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
