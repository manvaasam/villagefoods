<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['vendor']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = !empty($data['id']) ? (int)$data['id'] : null;

    if (!$id) {
        throw new Exception('Product ID is required.');
    }

    if (!isset($_SESSION['shop_id'])) {
        $stmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['shop_id'] = $stmt->fetchColumn();
    }

    $shop_id = $_SESSION['shop_id'];

    // Verify ownership and get image_url
    $stmt = $pdo->prepare('SELECT image_url, shop_id FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception('Product not found.');
    }

    if ($product['shop_id'] != $shop_id) {
        throw new Exception('Unauthorized to delete this product.');
    }

    // Delete image if exists
    if ($product['image_url'] && file_exists('../../../' . $product['image_url'])) {
        @unlink('../../../' . $product['image_url']);
    }

    // Delete product
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ? AND shop_id = ?');
    $stmt->execute([$id, $shop_id]);

    echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
