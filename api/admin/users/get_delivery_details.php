<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['admin']);

try {
    $user_id = $_GET['user_id'] ?? null;
    if (!$user_id) throw new Exception('User ID is required');

    $query = "SELECT u.image, u.email,
                     dp.id as partner_id, dp.full_name, dp.phone, dp.status as verification_status,
                     pvd.vehicle_type, pvd.vehicle_number, pvd.license_number,
                     pbd.bank_name, pbd.holder_name as acc_holder_name, pbd.account_number as acc_number, pbd.ifsc_code, pbd.upi_id,
                     pd.license_doc, pd.aadhaar_doc, pd.rc_doc
              FROM users u
              LEFT JOIN delivery_partners dp ON u.id = dp.user_id
              LEFT JOIN partner_vehicle_details pvd ON dp.id = pvd.partner_id
              LEFT JOIN partner_bank_details pbd ON dp.id = pbd.partner_id
              LEFT JOIN partner_documents pd ON dp.id = pd.partner_id
              WHERE u.id = ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $details = $stmt->fetch();

    echo json_encode(['success' => true, 'details' => $details ?: (object)[]]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
