<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $search = !empty($_GET['search']) ? trim($_GET['search']) : '';
    $params = [];
    $whereClause = "";
    
    if ($search) {
        $whereClause = " WHERE c.name LIKE ? ";
        $params[] = "%$search%";
    }

    // List categories with product and order counts
    $query = "SELECT c.*, 
              (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count,
              (SELECT COUNT(DISTINCT oi.order_id) 
               FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               WHERE p.category_id = c.id) as order_count,
              (SELECT SUM(oi.price * oi.quantity) 
               FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               JOIN orders o ON oi.order_id = o.id
               WHERE p.category_id = c.id AND o.payment_status = 'Paid') as revenue
              FROM categories c 
              $whereClause
              ORDER BY c.id ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $categories = $stmt->fetchAll();

    echo json_encode($categories);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
