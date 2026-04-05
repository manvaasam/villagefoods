<?php
session_start();
require '../../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to use wishlist']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? null;

if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID missing']);
    exit;
}

try {
    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM user_wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Remove
        $stmt = $pdo->prepare("DELETE FROM user_wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        echo json_encode(['status' => 'success', 'action' => 'removed', 'message' => 'Removed from wishlist']);
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO user_wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
        echo json_encode(['status' => 'success', 'action' => 'added', 'message' => 'Added to wishlist']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
