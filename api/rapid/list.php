<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';

header('Content-Type: application/json');

requireRole(['customer']);

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM rapid_orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'orders' => $orders
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
