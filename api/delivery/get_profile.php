<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';

header('Content-Type: application/json');

// Ensure user is logged in as delivery
checkPersistentLogin($pdo);
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'delivery') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $query = "SELECT u.email, u.created_at, u.image,
                     dp.id as partner_id, dp.full_name, dp.phone, dp.verification_status as partner_status,
                     pvd.vehicle_type, pvd.vehicle_number, pvd.license_number,
                     pbd.bank_name, pbd.holder_name, pbd.account_number, pbd.ifsc_code, pbd.upi_id,
                     pd.license_doc, pd.aadhaar_doc, pd.rc_doc
              FROM users u
              LEFT JOIN delivery_partners dp ON u.id = dp.user_id
              LEFT JOIN partner_vehicle_details pvd ON dp.id = pvd.partner_id
              LEFT JOIN partner_bank_details pbd ON dp.id = pbd.partner_id
              LEFT JOIN partner_documents pd ON dp.id = pd.partner_id
              WHERE u.id = ? AND u.is_deleted = 0";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $data = $stmt->fetch();

    if (!$data) {
        throw new Exception('Partner record not found');
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
