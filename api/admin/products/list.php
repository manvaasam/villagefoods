<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';

    $query = 'SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE 1=1';
    $params = [];

    if (!empty($search)) {
        $query .= ' AND (p.name LIKE ? OR c.name LIKE ?)';
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if (!empty($category) && $category !== 'all') {
        $query .= ' AND c.slug = ?';
        $params[] = $category;
    }

    if (!empty($_GET['shop_id']) && $_GET['shop_id'] !== 'all') {
        $query .= ' AND p.shop_id = ?';
        $params[] = $_GET['shop_id'];
    }

    $query .= ' ORDER BY p.id DESC LIMIT 100';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
