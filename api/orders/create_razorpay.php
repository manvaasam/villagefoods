<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/razorpay_config.php';
require_once '../../includes/settings_helper.php';
header('Content-Type: application/json');
Settings::load($pdo);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to continue']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$cartItems = $data['cart'] ?? [];
if (empty($cartItems)) {
    echo json_encode(['status' => 'error', 'message' => 'Your cart is empty']);
    exit;
}

try {
    // 1. Fetch Settings
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
    $minOrder = $settings['minimum_order_value'] ?? 0;

    // 2. Calculate Total (Securely from DB)
    $totalAmount = 0;
    $shopId = null;

    $cartItemIds = array_keys($cartItems);
    $placeholders = implode(',', array_fill(0, count($cartItemIds), '?'));
    $stmt = $pdo->prepare("SELECT price, stock, name, shop_id, id FROM products WHERE id IN ($placeholders) AND is_available = 1");
    $stmt->execute($cartItemIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Multi-shop validation
    $shopIds = array_unique(array_column($products, 'shop_id'));
    if (count($shopIds) > 1) {
        echo json_encode(['status' => 'error', 'message' => 'Cart contains items from multiple shops. Please order from one shop at a time.']);
        exit;
    }
    $shopId = !empty($shopIds) ? array_values($shopIds)[0] : null;

    foreach ($products as $product) {
        $qty = (int)$cartItems[$product['id']];
        if ($qty <= 0) {
            echo json_encode(['status' => 'error', 'message' => "Invalid quantity for {$product['name']}"]);
            exit;
        }
        if ($product['stock'] < $qty) {
            echo json_encode(['status' => 'error', 'message' => "Insufficient stock for {$product['name']}"]);
            exit;
        }
        $totalAmount += $product['price'] * $qty;
    }

    if (count($products) !== count($cartItems)) {
        echo json_encode(['status' => 'error', 'message' => 'Some products are unavailable or invalid']);
        exit;
    }

    if ($totalAmount < $minOrder) {
        echo json_encode(['status' => 'error', 'message' => "Minimum order value is ₹{$minOrder}"]);
        exit;
    }

    // Calculate Robust Pricing
    $address_id = $data['address_id'] ?? null;
    $distance = 0;
    if ($address_id && $shopId) {
        require_once '../../includes/PricingHelper.php';
        require_once '../../includes/delivery_helper.php';

        $stmt = $pdo->prepare("SELECT latitude, longitude FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$address_id, $user_id]);
        $address = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT latitude, longitude FROM shops WHERE id = ?");
        $stmt->execute([$shopId]);
        $shop = $stmt->fetch();

        if ($address && $shop) {
            $distance = DeliveryHelper::calculateHaversineDistance(
                $shop['latitude'], $shop['longitude'],
                $address['latitude'], $address['longitude']
            );
        }
    }

    $pricing = PricingHelper::calculateBill($totalAmount, $distance);
    $grandTotal = $pricing['total_payable'];
    $amountInPaise = (int)round($grandTotal * 100);

    // 2. Create Razorpay Order via CURL
    $api_url = "https://api.razorpay.com/v1/orders";
    $receipt = "rcpt_" . time() . "_" . rand(100, 999);
    
    $payload = [
        "amount" => $amountInPaise,
        "currency" => "INR",
        "receipt" => $receipt,
        "notes" => ["user_id" => $user_id]
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

    $razorpayOrder = json_decode($response, true);

    if ($http_code === 200 && isset($razorpayOrder['id'])) {
        echo json_encode([
            'status' => 'success',
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount' => $amountInPaise,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Customer',
                'email' => $_SESSION['user_email'] ?? '',
                'phone' => $_SESSION['user_phone'] ?? ''
            ]
        ]);
    } else {
        $errorMsg = $razorpayOrder['error']['description'] ?? 'Unknown Razorpay Error';
        if ($response === false) {
            $errorMsg = 'CURL Error: ' . curl_error($ch);
        }
        echo json_encode([
            'status' => 'error', 
            'message' => 'Order creation failed: ' . $errorMsg,
            'debug' => [
                'http_code' => $http_code,
                'response' => $razorpayOrder
            ]
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
