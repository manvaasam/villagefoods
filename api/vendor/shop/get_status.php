<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['vendor']);

$shop_id = $_SESSION['shop_id'];

try {
    $stmt = $pdo->prepare("SELECT status FROM shops WHERE id = ?");
    $stmt->execute([$shop_id]);
    $status = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'status' => $status ?: 'active'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
