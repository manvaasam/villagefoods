<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 1");
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
