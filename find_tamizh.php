<?php
require_once 'includes/db.php';
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE name LIKE ?");
$stmt->execute(['%Tamizh%']);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
