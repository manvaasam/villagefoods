<?php
require_once '../../includes/db.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Shop ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM shops WHERE id = ?");
    $stmt->execute([$id]);
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($shop) {
        if (empty($shop['shop_image'])) {
            $shop['shop_image'] = 'assets/images/placeholder.png';
        }
        $shop['rating'] = 4.5;
        $shop['is_open'] = ($shop['status'] === 'active');
        
        echo json_encode(['status' => 'success', 'data' => $shop]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Shop not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
