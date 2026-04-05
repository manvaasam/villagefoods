<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $search = $_GET['search'] ?? '';

    $query = "SELECT o.id, o.order_number, o.grand_total as total_amount, o.payment_method, o.payment_status, o.created_at, 
                     u.name as customer_name, o.razorpay_payment_id
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.id
              WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR o.razorpay_payment_id LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $query .= " ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'payments' => $payments
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
