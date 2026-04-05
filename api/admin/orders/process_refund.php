<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';
require_once '../../../includes/razorpay_config.php';

checkPersistentLogin($pdo);
requireRole(['admin']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = $data['order_id'] ?? null;

    if (!$order_id) throw new Exception('Order ID is required');

    // 1. Fetch Order and Payment Details
    $stmt = $pdo->prepare("SELECT order_number, razorpay_payment_id, grand_total, payment_status FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) throw new Exception('Order not found');
    if ($order['payment_status'] !== 'Refund Pending') throw new Exception('Only "Refund Pending" orders can be refunded');
    if (!$order['razorpay_payment_id']) throw new Exception('Razorpay Payment ID missing for this order');

    // 2. Call Razorpay Refund API
    $api_url = "https://api.razorpay.com/v1/payments/" . $order['razorpay_payment_id'] . "/refund";
    
    // Amount in paise (optional, defaults to full amount)
    $payload = [
        "amount" => (int)round($order['grand_total'] * 100),
        "speed" => "normal",
        "notes" => [
            "order_number" => $order['order_number'],
            "reason" => "Order Cancelled"
        ]
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(RAZORPAY_KEY_ID . ":" . RAZORPAY_KEY_SECRET)
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_code === 200 && isset($result['id'])) {
        // 3. Update Database on Success
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Refunded' WHERE id = ?");
        $stmt->execute([$order_id]);

        // Log Transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (order_id, razorpay_payment_id, amount, status) VALUES (?, ?, ?, 'Refunded')");
        $stmt->execute([$order_id, $result['id'], $order['grand_total']]);

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Refund processed successfully via Razorpay',
            'refund_id' => $result['id']
        ]);
    } else {
        $errorMsg = $result['error']['description'] ?? 'Razorpay Refund Failed';
        throw new Exception($errorMsg);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
