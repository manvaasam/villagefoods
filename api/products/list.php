<?php
session_start();
header('Content-Type: application/json');
require_once '../../includes/db.php';

$user_id = $_SESSION['user_id'] ?? 0;
$category_slug = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';
$shop_id = isset($_GET['shop_id']) ? intval($_GET['shop_id']) : null;
$bestseller = isset($_GET['bestseller']) ? intval($_GET['bestseller']) : 0;

try {
    $query = 'SELECT p.*, c.slug as category_slug, s.shop_name, s.latitude as shop_lat, s.longitude as shop_lng,
              (SELECT COUNT(*) FROM user_wishlist WHERE user_id = ? AND product_id = p.id) as in_wishlist
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              LEFT JOIN shops s ON p.shop_id = s.id
              WHERE p.is_available = 1';
    $params = [$user_id];

    if ($category_slug !== 'all') {
        $query .= ' AND c.slug = ?';
        $params[] = $category_slug;
    }

    if (!empty($search)) {
        $query .= ' AND (p.name LIKE ? OR c.name LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($shop_id) {
        $query .= ' AND p.shop_id = ?';
        $params[] = $shop_id;
    }

    if ($bestseller === 1) {
        $query .= ' AND p.is_bestseller = 1';
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
