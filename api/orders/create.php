<?php
session_start();
require '../../includes/db.php';
require_once '../../includes/settings_helper.php';
require_once '../../includes/mail_helper.php';
require_once '../../includes/PricingHelper.php';
require_once '../../includes/delivery_helper.php';
Settings::load($pdo);
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to place an order']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$cartItems = $data['cart'] ?? [];
$addressId = $data['address_id'] ?? null;
$paymentMethod = $data['payment_method'] ?? 'Online';

if ($paymentMethod === 'COD' && !Settings::isEnabled('enable_cod')) {
    echo json_encode(['status' => 'error', 'message' => 'Cash on Delivery is currently disabled. Please use Online Payment.']);
    exit;
}

if (empty($cartItems)) {
    echo json_encode(['status' => 'error', 'message' => 'Your cart is empty']);
    exit;
}

if (!$addressId) {
    echo json_encode(['status' => 'error', 'message' => 'Delivery address missing']);
    exit;
}

try {
    // 1. Get and Verify Address
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$addressId, $user_id]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$address) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid delivery address']);
        exit;
    }
    
    $addressStr = "{$address['door_no']}, {$address['street']}, {$address['landmark']}, {$address['area']}, {$address['city']} - {$address['pincode']}";

    // 2. Process Cart and Calculate Totals (Securely)
    $totalAmount = 0;
    $processedItems = [];
    $shopId = null;
    
    $cartItemIds = array_keys($cartItems);
    if (empty($cartItemIds)) {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
        exit;
    }
    
    $placeholders = implode(',', array_fill(0, count($cartItemIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND is_available = 1 FOR UPDATE");
    $stmt->execute($cartItemIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Multi-shop validation
    $shopIds = array_unique(array_column($products, 'shop_id'));
    if (count($shopIds) > 1) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(['status' => 'error', 'message' => 'Cart contains items from multiple shops. Please order from one shop at a time.']);
        exit;
    }
    $shopId = !empty($shopIds) ? array_values($shopIds)[0] : null;

    foreach ($products as $product) {
        $qty = (int)$cartItems[$product['id']];
        if ($qty <= 0) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            echo json_encode(['status' => 'error', 'message' => "Invalid quantity for {$product['name']}"]);
            exit;
        }
        if ($product['stock'] < $qty) {
            echo json_encode(['status' => 'error', 'message' => "Insufficient stock for {$product['name']} (Available: {$product['stock']})"]);
            exit;
        }
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

    if (count($processedItems) !== count($cartItems)) {
        echo json_encode(['status' => 'error', 'message' => 'Some products in your cart are no longer available or invalid']);
        exit;
    }

    // 2.5 Calculate Robust Pricing
    $stmt = $pdo->prepare("SELECT latitude, longitude FROM shops WHERE id = ?");
    $stmt->execute([$shopId]);
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $distance = 0;
    if ($shop && $address) {
        $distance = DeliveryHelper::calculateHaversineDistance(
            $shop['latitude'], $shop['longitude'],
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

    // 3. Database Transaction
    $pdo->beginTransaction();

    // Insert Order with New Pricing Columns
    $stmt = $pdo->prepare("INSERT INTO orders 
        (order_number, user_id, shop_id, total_amount, delivery_charge, platform_fee, handling_fee, grand_total, payment_method, payment_type, payment_status, status, address, 
         customer_lat, customer_lng, pickup_lat, pickup_lng, distance_km, delivery_earning, platform_profit, 
         commission_rate, commission_amount, vendor_earning) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'cod', 'Pending', 'Placed', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $orderNumber,
        $user_id,
        $shopId,
        $pricing['product_total'],
        $pricing['delivery_charge'],
        $pricing['platform_fee'],
        $pricing['handling_fee'],
        $pricing['total_payable'],
        $paymentMethod,
        $addressStr,
        $address['latitude'],
        $address['longitude'],
        $shop['latitude'],
        $shop['longitude'],
        $pricing['distance'],
        $pricing['delivery_partner_payout'],
        $pricing['platform_profit'],
        $pricing['commission_rate'],
        $pricing['commission_amount'],
        $pricing['vendor_earning']
    ]);
    
    $orderId = $pdo->lastInsertId();

    // Insert Items and Decrement Stock
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $updStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    foreach ($processedItems as $item) {
        $stmt->execute([
            $orderId,
            $item['id'],
            $item['name'],
            $item['price'],
            $item['quantity'],
            $item['subtotal']
        ]);
        // Decrement stock
        $updStock->execute([$item['quantity'], $item['id']]);
    }

    $pdo->commit();
    
    // 4. Respond FAST to User (Non-blocking redirect)
    $response = json_encode([
        'status' => 'success',
        'message' => 'Order placed successfully!',
        'order_id' => $orderNumber
    ]);

    ignore_user_abort(true);
    ob_start();
    echo $response;
    $size = ob_get_length();
    header("Content-Length: $size");
    header('Connection: close');
    ob_end_flush();
    ob_flush();
    flush();

    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    
    // 5. Send Confirmation Emails in Background
    MailHelper::sendOrderEmails($orderId);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
