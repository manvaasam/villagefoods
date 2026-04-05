<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';
checkPersistentLogin($pdo);
requireRole(['admin']);

try {
    $status = $_GET['status'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $date = $_GET['date'] ?? '';
    $paymentType = $_GET['payment_type'] ?? 'all';

    $query = 'SELECT o.*, u.name as customer_name, 
              d.name as delivery_boy_name,
              COALESCE(s.shop_name, (
                  SELECT s2.shop_name 
                  FROM order_items oi2 
                  JOIN products p2 ON oi2.product_id = p2.id 
                  JOIN shops s2 ON p2.shop_id = s2.id 
                  WHERE oi2.order_id = o.id 
                  LIMIT 1
              )) as shop_name,
              (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              LEFT JOIN users d ON o.delivery_boy_id = d.id 
              LEFT JOIN shops s ON o.shop_id = s.id
              WHERE 1=1';
    $params = [];

    if ($status === 'Refund Pending') {
        $query .= " AND o.payment_status = 'Refund Pending'";
    } elseif ($status !== 'all') {
        $query .= ' AND o.status = ?';
        $params[] = $status;
    }

    if (!empty($search)) {
        $query .= ' AND (o.order_number LIKE ? OR u.name LIKE ? OR u.phone LIKE ?)';
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if (!empty($date)) {
        $query .= ' AND DATE(o.created_at) = ?';
        $params[] = $date;
    }

    if ($paymentType !== 'all') {
        $query .= ' AND o.payment_type = ?';
        $params[] = $paymentType;
    }

    $query .= ' ORDER BY o.created_at DESC LIMIT 100';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    echo json_encode($orders);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
