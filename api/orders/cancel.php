<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';

header('Content-Type: application/json');

// Only customers can cancel their own orders (admins can use update_status)
requireRole(['customer', 'admin']);

$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? null;

if (!$order_id) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID is required']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['user_role'];

    // 1. Fetch order and check ownership (if customer)
    if ($role === 'customer') {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
    }
    
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found or access denied']);
        exit;
    }

    // 2. Check if order can be cancelled
    if ($role === 'customer') {
        // Customer can only cancel if Placed or Pending
        $allowed_cancellation_statuses = ['Pending', 'Placed'];
        if (!in_array($order['status'], $allowed_cancellation_statuses)) {
            echo json_encode(['status' => 'error', 'message' => 'Order is already being prepared or confirmed and cannot be cancelled. Please contact support.']);
            exit;
        }
    } else {
        // Admin can cancel if not already cancelled or delivered
        $allowed_cancellation_statuses = ['Pending', 'Processing', 'Placed', 'Confirmed', 'Preparing', 'Ready for Pickup'];
        if (!in_array($order['status'], $allowed_cancellation_statuses)) {
            echo json_encode(['status' => 'error', 'message' => 'Order cannot be cancelled at this stage (' . $order['status'] . ')']);
            exit;
        }
    }

    // 3. Database Transaction to update status and replenish stock
    $pdo->beginTransaction();

    // Determine Payment Status
    $newPaymentStatus = $order['payment_status'];
    // If it's a Razorpay order, it's eligible for refund if it was 'Paid' or if it's already verified online (type=online)
    if ($order['payment_method'] === 'Razorpay' && ($order['payment_status'] === 'Paid' || empty($order['payment_status']))) {
        $newPaymentStatus = 'Refund Pending';
    }

    // Update status to Cancelled and update payment_status if needed
    $stmt = $pdo->prepare("UPDATE orders SET status = 'Cancelled', payment_status = ? WHERE id = ?");
    $stmt->execute([$newPaymentStatus, $order_id]);

    // Fetch order items and increment stock
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {
        $upd = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $upd->execute([$item['quantity'], $item['product_id']]);
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Order cancelled successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
