<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $requestId = $input['id'] ?? null;
    $status = $input['status'] ?? null;
    $adminNote = $input['admin_note'] ?? '';

    if (!$requestId || !in_array($status, ['Approved', 'Rejected'])) {
        throw new Exception('Invalid request parameters');
    }

    $pdo->beginTransaction();

    // 1. Get request details
    $stmt = $pdo->prepare("SELECT * FROM withdrawal_requests WHERE id = ? FOR UPDATE");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) throw new Exception('Request not found');
    if ($request['status'] !== 'Pending') throw new Exception('Request already processed');

    // 2. Update status
    $stmt = $pdo->prepare("UPDATE withdrawal_requests SET status = ?, admin_note = ? WHERE id = ?");
    $stmt->execute([$status, $adminNote, $requestId]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Withdrawal request marked as $status"
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
