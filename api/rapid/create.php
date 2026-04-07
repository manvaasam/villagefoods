<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';
require_once '../../includes/settings_helper.php';
Settings::load($pdo);

header('Content-Type: application/json');

requireRole(['customer']);

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$pickup = $data['pickup_address'] ?? null;
$drop = $data['drop_address'] ?? null;
$description = $data['item_description'] ?? '';
$type = $data['package_type'] ?? 'bike';
$sender_name = $data['sender_name'] ?? '';
$sender_phone = $data['sender_phone'] ?? '';

if (!$pickup || !$drop || !$sender_name || !$sender_phone) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// Strict Regex Validation
if (!preg_match("/^[a-zA-Z\s]*$/", $sender_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Sender Name: Only alphabets and spaces are allowed']);
    exit;
}
if (!preg_match("/^[0-9]{10}$/", $sender_phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Sender Phone must be exactly 10 digits']);
    exit;
}

// Pricing logic
$prices = [
    'bike' => (int)Settings::get('rapid_price_bike', 20),
    'eco' => (int)Settings::get('rapid_price_heavy', 30),
    'express' => (int)Settings::get('rapid_price_express', 45)
];
$km_rate = (int)Settings::get('rapid_price_per_km', 10);

$base_price = $prices[$type] ?? $prices['bike'];
$price = (float)($data['price'] ?? $base_price); // Prefer frontend calculated price if provided

// Admin Commission (Profit) Logic
$commission_pct = (float)Settings::get('vendor_commission_percentage', 20);
$commission_rate = $commission_pct / 100;
$platform_profit = round($price * $commission_rate, 2);
$delivery_earning = round($price - $platform_profit, 2);
if ($delivery_earning < 0) $delivery_earning = 0; // Guard against edge cases

try {
    $pickup_lat = $data['pickup_lat'] ?? null;
    $pickup_lng = $data['pickup_lng'] ?? null;
    $drop_lat = $data['drop_lat'] ?? null;
    $drop_lng = $data['drop_lng'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO rapid_orders 
        (customer_id, sender_name, sender_phone, pickup_address, pickup_lat, pickup_lng, drop_address, drop_lat, drop_lng, item_description, price, platform_profit, delivery_earning, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$user_id, $sender_name, $sender_phone, $pickup, $pickup_lat, $pickup_lng, $drop, $drop_lat, $drop_lng, $description, $price, $platform_profit, $delivery_earning]);
    
    $rapid_id = $pdo->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => 'Rapid pickup booked successfully!',
        'rapid_id' => $rapid_id
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
