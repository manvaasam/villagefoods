<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT DISTINCT delivery_boy_id FROM orders WHERE delivery_boy_id IS NOT NULL");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
