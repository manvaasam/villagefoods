<?php
header('Content-Type: application/json');
session_start();
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

requireRole(['admin']);

try {
    $stmt = $pdo->query("SELECT dp.id, u.name, u.phone 
                         FROM delivery_partners dp 
                         JOIN users u ON dp.user_id = u.id 
                         WHERE dp.verification_status = 'Verified' 
                         AND dp.status = 'available' 
                         AND u.is_deleted = 0");
    $partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'partners' => $partners]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
