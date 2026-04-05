<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';
checkPersistentLogin($pdo);
requireRole(['admin']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = $data['id'] ?? null;
    $status = $data['status'] ?? null;

    if (!$order_id || !$status) throw new Exception('Order ID and status are required');

    $allowedStatus = ['Placed', 'Confirmed', 'Preparing', 'Ready for Pickup', 'Picked Up', 'On the Way', 'Delivered', 'Cancelled', 'Pending', 'Processing', 'Accepted', 'Arrived'];
    if (!in_array($status, $allowedStatus)) throw new Exception('Invalid status value');

    $pdo->beginTransaction();

    // Fetch old status and payment info
    $stmt = $pdo->prepare("SELECT status, payment_method, payment_status FROM orders WHERE id = ? FOR UPDATE");
    $stmt->execute([$order_id]);
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldStatus = $orderData['status'] ?? null;
    $paymentMethod = $orderData['payment_method'] ?? '';
    $paymentStatus = $orderData['payment_status'] ?? '';

    if (!$oldStatus) throw new Exception("Order not found (ID: $order_id)");

    // Determine Payment Status if cancelling
    $newPaymentStatus = $paymentStatus;
    if ($status === 'Cancelled' && $paymentMethod === 'Razorpay' && $paymentStatus === 'Paid') {
        $newPaymentStatus = 'Refund Pending';
    }

    // Update status and payment_status
    $stmt = $pdo->prepare('UPDATE orders SET status = ?, payment_status = ? WHERE id = ?');
    $stmt->execute([$status, $newPaymentStatus, $order_id]);

    // If transitioning TO Cancelled from a non-cancelled state, replenish stock
    if ($status === 'Cancelled' && $oldStatus !== 'Cancelled') {
        $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            $upd = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $upd->execute([$item['quantity'], $item['product_id']]);
        }
    }
    // If transitioning FROM Cancelled to a non-cancelled state, decrement stock (if available)
    elseif ($status !== 'Cancelled' && $oldStatus === 'Cancelled') {
        $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            // Check stock before decrementing
            $check = $pdo->prepare("SELECT stock, name FROM products WHERE id = ? FOR UPDATE");
            $check->execute([$item['product_id']]);
            $p = $check->fetch();
            if ($p && $p['stock'] < $item['quantity']) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => "Cannot reinstate order: Insufficient stock for {$p['name']}"]);
                exit;
            }
            $upd = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $upd->execute([$item['quantity'], $item['product_id']]);
        }
    }

    $pdo->commit();
        
    // Notify Customer of Status Change
    require_once '../../../includes/mail_helper.php';
    MailHelper::sendStatusUpdateEmail($order_id);

    echo json_encode(['success' => true, 'message' => "Order #$order_id status updated to $status"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
