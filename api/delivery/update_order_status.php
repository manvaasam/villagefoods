<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/mail_helper.php';
require_once '../../includes/auth_helper.php';
require_once '../../includes/delivery_helper.php';

require_once '../../includes/settings_helper.php';

requireRole(['delivery']);
Settings::load($pdo);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = $data['order_id'] ?? null;
    $status = $data['status'] ?? null;
    $cash_collected = $data['cash_collected'] ?? 0;

    if (!$order_id || !$status) throw new Exception('Order ID and status are required');

    // Verify order is assigned to this delivery boy
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND delivery_boy_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $orderData = $stmt->fetch();
    if (!$orderData) throw new Exception('Access denied: This order is not assigned to you.');

    $allowedStatus = ['Accepted', 'On the Way', 'Arrived', 'Delivered', 'Cancelled', 'Processing', 'Confirmed', 'Preparing', 'Ready', 'Placed', 'Picked Up'];
    if (!in_array($status, $allowedStatus)) throw new Exception('Invalid status value');

    // Handle specific status updates
    if ($status === 'Delivered') {
        require_once '../../includes/PricingHelper.php';
        
        $distance = DeliveryHelper::calculateHaversineDistance(
            $orderData['pickup_lat'], 
            $orderData['pickup_lng'], 
            $orderData['customer_lat'], 
            $orderData['customer_lng']
        );
        
        $pricing = PricingHelper::calculateBill($orderData['total_amount'], $distance);
        $earnings = $pricing['delivery_partner_payout'];
        $profit = $pricing['platform_profit'];

        // If it's a COD order and cash was collected, mark payment as paid
        $paymentStatusUpdate = "";
        if ($orderData['payment_type'] === 'cod' && $cash_collected) {
            $paymentStatusUpdate = ", payment_status = 'Paid'";
        }

        $updStmt = $pdo->prepare("UPDATE orders SET status = ?, distance_km = ?, delivery_earning = ?, platform_profit = ?, delivered_at = NOW() $paymentStatusUpdate WHERE id = ?");
        $updStmt->execute([$status, $distance, $earnings, $profit, $order_id]);
    } elseif ($status === 'Picked Up') {
        $updStmt = $pdo->prepare("UPDATE orders SET status = ?, picked_up_at = NOW() WHERE id = ?");
        $updStmt->execute([$status, $order_id]);
    } else {
        $updStmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $updStmt->execute([$status, $order_id]);
    }

    // Send email notification to customer
    MailHelper::sendStatusUpdateEmail($order_id);

    echo json_encode(['success' => true, 'message' => "Order status updated to $status"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
