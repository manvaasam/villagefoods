<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT wr.*, u.name FROM withdrawal_requests wr LEFT JOIN users u ON wr.user_id = u.id");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
