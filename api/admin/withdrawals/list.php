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
    $query = "SELECT wr.*, 
                     u.name as partner_name, u.email as partner_email,
                     dp.phone as partner_phone
              FROM withdrawal_requests wr
              JOIN users u ON wr.user_id = u.id
              JOIN delivery_partners dp ON u.id = dp.user_id
              ORDER BY wr.created_at DESC";
    
    $stmt = $pdo->query($query);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
