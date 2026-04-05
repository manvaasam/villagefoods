<?php
require_once 'includes/db.php';
$userId = 27;

$stmt = $pdo->prepare("SELECT id, user_id FROM delivery_partners WHERE user_id = ?");
$stmt->execute([$userId]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
