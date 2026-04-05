<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = !empty($data['id']) ? (int) $data['id'] : null;

    if (!$id) {
        throw new Exception('Shop ID is required.');
    }

    $stmt = $pdo->prepare('SELECT shop_image FROM shops WHERE id = ?');
    $stmt->execute([$id]);
    $shop = $stmt->fetch();

    if (!$shop) {
        throw new Exception('Shop not found.');
    }

    // Attempt to delete image
    if ($shop['shop_image'] && file_exists('../../../' . $shop['shop_image'])) {
        @unlink('../../../' . $shop['shop_image']);
    }

    // Delete associated products and their images
    $stmt = $pdo->prepare('SELECT image_url FROM products WHERE shop_id = ?');
    $stmt->execute([$id]);
    $products = $stmt->fetchAll();
    foreach ($products as $p) {
        if ($p['image_url'] && file_exists('../../../' . $p['image_url'])) {
            @unlink('../../../' . $p['image_url']);
        }
    }
    $stmt = $pdo->prepare('DELETE FROM products WHERE shop_id = ?');
    $stmt->execute([$id]);

    // Delete mappings
    $stmt = $pdo->prepare('DELETE FROM shop_categories WHERE shop_id = ?');
    $stmt->execute([$id]);

    // Delete shop itself
    $stmt = $pdo->prepare('DELETE FROM shops WHERE id = ?');
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
