<?php
require_once 'includes/db.php';
$stmt = $pdo->query("DESCRIBE orders");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cols, JSON_PRETTY_PRINT);
?>
