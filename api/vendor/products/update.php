<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['vendor']);

$shop_id = $_SESSION['shop_id'];
$data = json_decode(file_get_contents('php://input'), true);

$product_id = $data['product_id'] ?? null;
$is_available = isset($data['is_available']) ? (int)$data['is_available'] : null;
$stock = isset($data['stock']) ? (int)$data['stock'] : null;

if (!$product_id) {
    echo json_encode(['success' => false, 'error' => 'Product ID is required']);
    exit;
}

try {
    // Verify product belongs to shop
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND shop_id = ?");
    $stmt->execute([$product_id, $shop_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized or product not found']);
        exit;
    }

    $updates = [];
    $params = [];

    if ($is_available !== null) {
        $updates[] = "is_available = ?";
        $params[] = $is_available;
    }
    if ($stock !== null) {
        $updates[] = "stock = ?";
        $params[] = $stock;
    }

    if (empty($updates)) {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        exit;
    }

    $params[] = $product_id;
    $sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
