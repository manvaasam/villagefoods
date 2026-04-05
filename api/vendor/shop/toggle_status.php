<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['vendor']);

$shop_id = $_SESSION['shop_id'];

try {
    // Get current status
    $stmt = $pdo->prepare("SELECT status FROM shops WHERE id = ?");
    $stmt->execute([$shop_id]);
    $currentStatus = $stmt->fetchColumn();

    $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

    $stmt = $pdo->prepare("UPDATE shops SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $shop_id]);

    echo json_encode([
        'success' => true,
        'status' => $newStatus,
        'message' => ($newStatus === 'active') ? "Shop is now OPEN" : "Shop is now CLOSED"
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
