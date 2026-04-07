<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['admin']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? null;
    $status = $data['status'] ?? null;

    if (!$user_id || !$status) throw new Exception('User ID and status are required');

    // 1. Update old table (delivery_details) for backward compatibility
    $stmt = $pdo->prepare("INSERT INTO delivery_details (user_id, verification_status, verified_at) 
                           VALUES (?, ?, CURRENT_TIMESTAMP)
                           ON DUPLICATE KEY UPDATE 
                           verification_status = VALUES(verification_status),
                           verified_at = CURRENT_TIMESTAMP");
    $stmt->execute([$user_id, $status]);

    // 2. Update new table (delivery_partners)
    $stmt = $pdo->prepare("UPDATE delivery_partners SET verification_status = ? WHERE user_id = ?");
    $stmt->execute([$status, $user_id]);

    // Notify Partner via Email
    require_once '../../../includes/mail_helper.php';
    MailHelper::sendVerificationEmail($user_id, $status);

    echo json_encode(['success' => true, 'message' => "Partner status updated to $status"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
