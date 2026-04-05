<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/razorpay_config.php';
require_once '../../includes/mail_helper.php';
require_once '../../includes/PricingHelper.php';
require_once '../../includes/delivery_helper.php';
require_once '../../includes/settings_helper.php';
header('Content-Type: application/json');
Settings::load($pdo);

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

// DEBUG LOGGING
$debugInfo = [
    'time' => date('Y-m-d H:i:s'),
    'user_id' => $user_id,
    'session' => $_SESSION,
    'input_address_id' => $address_id,
    'input_order_id' => $razorpay_order_id
];
file_put_contents('../../payment_debug.log', json_encode($debugInfo) . "\n", FILE_APPEND);

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

try {
    // 2. Verified! Now Save Order to DB
    $pdo->beginTransaction();

    // Get Address
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);
    $address = $stmt->fetch();
    if (!$address) throw new Exception("Invalid delivery address");

    $addressStr = "{$address['door_no']}, {$address['street']}, {$address['landmark']}, {$address['area']}, {$address['city']} - {$address['pincode']}";

    // Calculate Totals Properly
    $totalAmount = 0;
    $processedItems = [];
    $orderShopId = null;
    $shopCoords = null;

    $cartItemIds = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($cartItemIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND is_available = 1 FOR UPDATE");
    $stmt->execute($cartItemIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Multi-shop validation
    $shopIds = array_unique(array_column($products, 'shop_id'));
    if (count($shopIds) > 1) {
        throw new Exception("Cart contains items from multiple shops. Please order from one shop at a time.");
    }
    $orderShopId = !empty($shopIds) ? array_values($shopIds)[0] : null;

    if ($orderShopId) {
        $shopStmt = $pdo->prepare("SELECT latitude, longitude FROM shops WHERE id = ?");
        $shopStmt->execute([$orderShopId]);
        $shopCoords = $shopStmt->fetch();
    }

    $upd = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    
    foreach ($products as $product) {
        $qty = (int)$cart[$product['id']];
        if ($qty <= 0) {
            throw new Exception("Invalid quantity for {$product['name']}");
        }
        if ($product['stock'] < $qty) {
            throw new Exception("Insufficient stock for {$product['name']}");
        }
        
        // Decrement Stock
        $upd->execute([$qty, $product['id']]);

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
    
    if (count($processedItems) !== count($cart)) {
        throw new Exception("Some products in your cart are no longer available or invalid");
    }

    // 2.5 Calculate Robust Pricing
    $distance = 0;
    if ($shopCoords && $address) {
        $distance = DeliveryHelper::calculateHaversineDistance(
            $shopCoords['latitude'], $shopCoords['longitude'],
            $address['latitude'], $address['longitude']
        );
    }

    $pricing = PricingHelper::calculateBill($totalAmount, $distance);
    
    // Generate Sequential Order ID (VF-1001 format)
    $stmt = $pdo->query("SELECT order_number FROM orders WHERE order_number LIKE 'VF-%' ORDER BY id DESC LIMIT 1");
    $lastOrder = $stmt->fetchColumn();
    $nextId = 1001;
    if ($lastOrder) {
        $nextId = (int)str_replace('VF-', '', $lastOrder) + 1;
    }
    $orderNumber = "VF-" . $nextId;

    // Insert Order with New Pricing Columns
    $stmt = $pdo->prepare("INSERT INTO orders 
        (order_number, user_id, shop_id, total_amount, delivery_charge, platform_fee, handling_fee, grand_total, payment_method, payment_type, payment_status, status, address, 
         customer_lat, customer_lng, pickup_lat, pickup_lng, distance_km, delivery_earning, platform_profit, razorpay_order_id, razorpay_payment_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Razorpay', 'online', 'Paid', 'Placed', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $orderNumber,
        $user_id,
        $orderShopId,
        $pricing['product_total'],
        $pricing['delivery_charge'],
        $pricing['platform_fee'],
        $pricing['handling_fee'],
        $pricing['total_payable'],
        $addressStr,
        $address['latitude'],
        $address['longitude'],
        $shopCoords['latitude'],
        $shopCoords['longitude'],
        $pricing['distance'],
        $pricing['delivery_partner_payout'],
        $pricing['platform_profit'],
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

    // 4. Log Transaction
    $stmt = $pdo->prepare("INSERT INTO transactions (order_id, razorpay_payment_id, amount, status) VALUES (?, ?, ?, 'Success')");
    $stmt->execute([$orderId, $razorpay_payment_id, $pricing['total_payable']]);

    $pdo->commit();
    
    // 3. Prepare SUCCESS Response
    $response = [
        'status' => 'success', 
        'message' => 'Payment verified and order placed!', 
        'order_id' => $orderNumber
    ];
    $jsonResponse = json_encode($response);

    // 4. Send Response Immediately to Browser (Fast Redirect)
    if (ob_get_level() > 0) ob_end_clean();
    ignore_user_abort(true);
    
    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($jsonResponse));
    header('Connection: close');
    echo $jsonResponse;
    
    // Flush all output buffers
    if (ob_get_level() > 0) ob_end_flush();
    flush();

    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    // 5. Background Work: Send Emails
    // ignore_user_abort ensures the script continues even if the browser closes the connection
    ignore_user_abort(true);
    try {
        MailHelper::sendOrderEmails($orderId);
    } catch (Exception $mailEx) {
        error_log("Mail Error for Order #$orderNumber: " . $mailEx->getMessage());
    }
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if (ob_get_level() > 0) ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
?>
