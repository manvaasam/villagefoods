<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/sms_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

$phoneInput = trim($data['phone'] ?? ($data['email'] ?? ''));
$name = trim($data['name'] ?? '');

// 1. Sanitize to 10-digit Indian phone number
$phone = preg_replace('/[^0-9]/', '', $phoneInput);
if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
    $phone = substr($phone, 2);
} elseif (strlen($phone) === 11 && str_starts_with($phone, '0')) {
    $phone = substr($phone, 1);
}

if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 10-digit mobile number']);
    exit;
}

if (!empty($name) && !preg_match("/^[a-zA-Z\s]*$/", $name)) {
    echo json_encode(['status' => 'error', 'message' => 'Name: Only alphabets and spaces are allowed']);
    exit;
}

// Store name & phone in session temporarily for user creation/update in verify_otp
$_SESSION['temp_name'] = $name;
$_SESSION['temp_phone'] = $phone;

// Check if user exists and has a staff role (admin/delivery/vendor)
$stmt = $pdo->prepare("SELECT role FROM users WHERE phone = ? AND is_deleted = 0");
$stmt->execute([$phone]);
$userRole = $stmt->fetchColumn();

if ($userRole && $userRole !== 'customer') {
    $portal = ($userRole === 'delivery') ? 'Partner Portal (/delivery)' : 'Admin Panel (/admin)';
    echo json_encode([
        'status' => 'error', 
        'message' => "Staff account detected. Please login using {$portal}."
    ]);
    exit;
}

try {
    // Ensure column 'phone' exists in 'otps' table
    try {
        $stmtCheck = $pdo->query("SHOW COLUMNS FROM otps LIKE 'phone'");
        if (!$stmtCheck->fetch()) {
            $pdo->exec("ALTER TABLE otps ADD COLUMN phone VARCHAR(20) NULL AFTER id, MODIFY COLUMN email VARCHAR(191) NULL");
        }
    } catch (\Throwable $ex) {
        // Continue if column already exists
    }

    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(100000, 999999));

    // Check for resend cooldown (60 seconds)
    $stmt = $pdo->prepare("SELECT TIMESTAMPDIFF(SECOND, last_sent_at, NOW()) as diff FROM otps WHERE phone = ?");
    $stmt->execute([$phone]);
    $diff = $stmt->fetchColumn();

    if ($diff !== false && $diff !== null && $diff < 60) {
        $wait = 60 - $diff;
        echo json_encode(['status' => 'error', 'message' => "Please wait {$wait} seconds before resending."]);
        exit;
    }

    // Delete existing OTP for this phone
    $stmt = $pdo->prepare("DELETE FROM otps WHERE phone = ?");
    $stmt->execute([$phone]);

    // Store new OTP with 5 minute expiration
    $stmt = $pdo->prepare("INSERT INTO otps (phone, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
    $stmt->execute([$phone, $otp]);

    // Send SMS via Fast2SMS
    $smsResult = send_sms_otp($phone, $otp);

    if ($smsResult['success']) {
        echo json_encode([
            'status' => 'success', 
            'message' => "OTP sent successfully to +91 {$phone}!"
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => $smsResult['message']
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (\Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
}
