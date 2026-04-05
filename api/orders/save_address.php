<?php
session_start();
require_once '../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
    exit;
}

// Input Validation
$required_fields = ['door_no', 'street', 'city', 'pincode', 'latitude', 'longitude', 'contact_number'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['status' => 'error', 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit;
    }
}

// Strict Regex Validation
if (!preg_match("/^[a-zA-Z\s]*$/", $data['street'])) {
    echo json_encode(['status' => 'error', 'message' => 'Street Name / Area: Only alphabets and spaces are allowed']);
    exit;
}
if (!preg_match("/^[a-zA-Z\s]*$/", $data['city'])) {
    echo json_encode(['status' => 'error', 'message' => 'City: Only alphabets and spaces are allowed']);
    exit;
}
if (!preg_match("/^[0-9]{6}$/", $data['pincode'])) {
    echo json_encode(['status' => 'error', 'message' => 'Pincode must be exactly 6 digits']);
    exit;
}
if (!preg_match("/^[0-9]{10}$/", $data['contact_number'])) {
    echo json_encode(['status' => 'error', 'message' => 'Contact Number must be exactly 10 digits']);
    exit;
}

// Sanitize inputs
$userId = $_SESSION['user_id'] ?? null;
$lat = filter_var($data['latitude'], FILTER_VALIDATE_FLOAT);
$lng = filter_var($data['longitude'], FILTER_VALIDATE_FLOAT);
$doorNo = htmlspecialchars(strip_tags($data['door_no']));
$street = htmlspecialchars(strip_tags($data['street']));
$landmark = htmlspecialchars(strip_tags($data['landmark'] ?? ''));
$area = htmlspecialchars(strip_tags($data['area'] ?? ''));
$city = htmlspecialchars(strip_tags($data['city'] ?? ''));
$district = htmlspecialchars(strip_tags($data['district'] ?? ''));
$state = htmlspecialchars(strip_tags($data['state'] ?? ''));
$pincode = htmlspecialchars(strip_tags($data['pincode']));
$contactNumber = htmlspecialchars(strip_tags($data['contact_number']));
$isDefault = isset($data['is_default']) ? (int)$data['is_default'] : 1;

try {
    // 1. Fetch Shop Settings
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
    if ($settings) {
        $shopLat = $settings['shop_latitude'];
        $shopLng = $settings['shop_longitude'];
        $maxRadius = $settings['delivery_radius'];

        /* 
        // 2. Haversine Distance Calculation
        $earthRadius = 6371; // In KM
        $dLat = deg2rad($lat - $shopLat);
        $dLng = deg2rad($lng - $shopLng);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($shopLat)) * cos(deg2rad($lat)) *
             sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        if ($distance > $maxRadius) {
            echo json_encode([
                'status' => 'error', 
                'message' => "Sorry, we only deliver within {$maxRadius}km of our shop. Your location is " . round($distance, 1) . "km away."
            ]);
            exit;
        }
        */
    }
    // If setting as default, unset other defaults for this user
    if ($isDefault && $userId) {
        $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    $addressId = $data['address_id'] ?? null;

    if ($addressId && $userId) {
        // UPDATE Existing Address
        $sql = "UPDATE user_addresses SET 
                    latitude = ?, longitude = ?, door_no = ?, street = ?, 
                    landmark = ?, area = ?, city = ?, district = ?, state = ?, 
                    pincode = ?, contact_number = ?, is_default = ?
                WHERE id = ? AND user_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $lat, $lng, $doorNo, $street, 
            $landmark, $area, $city, $district, $state, $pincode, $contactNumber, $isDefault,
            $addressId, $userId
        ]);
        
        $message = 'Address updated successfully!';
    } else {
        // INSERT New Address
        $sql = "INSERT INTO user_addresses (
                    user_id, latitude, longitude, door_no, street, 
                    landmark, area, city, district, state, pincode, contact_number, is_default
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $userId, $lat, $lng, $doorNo, $street, 
            $landmark, $area, $city, $district, $state, $pincode, $contactNumber, $isDefault
        ]);
        $addressId = $pdo->lastInsertId();
        $message = 'Address saved successfully!';
    }

    // If no user is logged in, store the address ID in session to link after login/signup
    if (!$userId) {
        $_SESSION['pending_address_id'] = $addressId;
    }

    echo json_encode([
        'status' => 'success', 
        'message' => $message ?? 'Success',
        'address_id' => $addressId
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['status' => 'error', 'message' => 'Critical error: ' . $e->getMessage()]);
}
?>
