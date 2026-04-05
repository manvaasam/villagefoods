<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT id, name, phone, created_at FROM users WHERE name LIKE '%Tamizh%' OR phone LIKE '%9042%'"); // Search by name or partial phone if known
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
