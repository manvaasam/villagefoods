<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['vendor']);

$shop_id = $_SESSION['shop_id'];
$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? 'update_profile';

try {
    if ($action === 'toggle_status') {
        $status = $data['status'] ?? 'active';
        $stmt = $pdo->prepare("UPDATE shops SET status = ? WHERE id = ?");
        $stmt->execute([$status, $shop_id]);
        echo json_encode(['success' => true, 'message' => 'Shop status updated']);
    } else {
        $phone = $data['shop_phone'] ?? null;
        $address = $data['shop_address'] ?? null;
        $lat = $data['latitude'] ?? null;
        $lng = $data['longitude'] ?? null;

        if (!$phone || !$address) {
            echo json_encode(['success' => false, 'error' => 'Phone and Address are required']);
            exit;
        }

        $query = "UPDATE shops SET phone = ?, address = ?";
        $params = [$phone, $address];

        if ($lat !== null && $lng !== null) {
            $query .= ", latitude = ?, longitude = ?";
            $params[] = $lat;
            $params[] = $lng;
        }

        $query .= " WHERE id = ?";
        $params[] = $shop_id;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        echo json_encode(['success' => true, 'message' => 'Shop profile updated']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
