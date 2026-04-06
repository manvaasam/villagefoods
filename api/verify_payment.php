<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/razorpay_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to continue']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$razorpay_order_id = $data['razorpay_order_id'] ?? null;
$razorpay_payment_id = $data['razorpay_payment_id'] ?? null;
$razorpay_signature = $data['razorpay_signature'] ?? null;
$cart = $data['cart'] ?? [];
$address_id = $data['address_id'] ?? null;

if (!$razorpay_order_id || !$razorpay_payment_id || !$razorpay_signature || empty($cart) || !$address_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid payment data or missing information']);
    exit;
}

// 1. Verify Signature (Security)
$generated_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, RAZORPAY_KEY_SECRET);

if ($generated_signature !== $razorpay_signature) {
    echo json_encode(['status' => 'error', 'message' => 'Payment verification failed: Signature mismatch']);
    exit;
}

// 1.1 Verify Amount with Razorpay (Security - Prevent Amount Tampering)
$ch = curl_init("https://api.razorpay.com/v1/orders/" . $razorpay_order_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ":" . RAZORPAY_KEY_SECRET);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to verify payment with provider']);
    exit;
}

$rzpOrder = json_decode($response, true);
$paidAmountInPaise = $rzpOrder['amount'] ?? 0;

try {
    // 2. Verified! Now Save Order to DB
    $pdo->beginTransaction();

    // Get Address
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);
    $address = $stmt->fetch();
    if (!$address) throw new Exception("Invalid delivery address");

    $addressStr = "{$address['door_no']}, {$address['street']}, {$address['landmark']}, {$address['area']}, {$address['city']} - {$address['pincode']} Ph: {$address['contact_number']}";

    // Calculate Totals Properly
    $totalAmount = 0;
    $processedItems = [];
    $shopId = null;
    foreach ($cart as $itemId => $qty) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$itemId]);
        $product = $stmt->fetch();
        if ($product) {
            if (!$shopId) $shopId = $product['shop_id'];
            $subtotal = $product['price'] * $qty;
            $totalAmount += $subtotal;
            $processedItems[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $qty,
                'subtotal' => $subtotal
            ];
        }
    }

    $deliveryCharge = 0; // Temporarily 0 for testing
    $grandTotal = $totalAmount + $deliveryCharge;

    // 2.1 Verify Amount (Security - Prevent Amount Tampering)
    // We compare the amount in Paise (Razorpay unit)
    $expectedAmountInPaise = (int)round($grandTotal * 100);
    if (abs($paidAmountInPaise - $expectedAmountInPaise) > 1) { // 1 paise tolerance for float rounding
        throw new Exception("Payment amount mismatch. Security verification failed.");
    }

    $orderNumber = 'VF-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

    // Insert Order
    $stmt = $pdo->prepare("INSERT INTO orders (order_number, user_id, shop_id, total_amount, delivery_charge, grand_total, payment_method, payment_status, status, order_status, delivery_address, razorpay_order_id, razorpay_payment_id) VALUES (?, ?, ?, ?, ?, ?, 'Razorpay', 'Paid', 'Pending', 'Placed', ?, ?, ?)");
    $stmt->execute([
        $orderNumber,
        $user_id,
        $shopId,
        $totalAmount,
        $deliveryCharge,
        $grandTotal,
        $addressStr,
        $razorpay_order_id,
        $razorpay_payment_id
    ]);
    
    $orderId = $pdo->lastInsertId();

    // Insert Items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($processedItems as $item) {
        $stmt->execute([
            $orderId, 
            $item['id'],
            $item['name'],
            $item['price'],
            $item['quantity'],
            $item['subtotal']
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Payment verified and order placed!', 'order_id' => $orderNumber]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Server error while saving order: ' . $e->getMessage()]);
}
?>
