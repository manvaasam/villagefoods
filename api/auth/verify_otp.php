<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';
safe_session_start();

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
$otp = trim($data['otp'] ?? '');

// Sanitize phone number to strictly 10 digits
$phone = preg_replace('/[^0-9]/', '', $phoneInput);
if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
    $phone = substr($phone, 2);
} elseif (strlen($phone) === 11 && str_starts_with($phone, '0')) {
    $phone = substr($phone, 1);
}

if (!$phone || !$otp) {
    echo json_encode(['status' => 'error', 'message' => 'Phone number and OTP are required']);
    exit;
}

try {
    // 1. Get the current OTP record for this phone
    $stmt = $pdo->prepare("SELECT * FROM otps WHERE phone = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$phone]);
    $otpRecord = $stmt->fetch();
    
    if (!$otpRecord) {
        echo json_encode(['status' => 'error', 'message' => 'No OTP found or expired. Please request a new one.']);
        exit;
    }

    // 2. Check Expiry
    if (strtotime($otpRecord['expires_at']) < time()) {
        echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please resend a new one.']);
        exit;
    }

    // 3. Check Brute Force (Max 5 attempts)
    if ($otpRecord['attempts'] >= 5) {
        $stmt = $pdo->prepare("DELETE FROM otps WHERE phone = ?");
        $stmt->execute([$phone]);
        echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. This OTP is now locked. Please request a new one.']);
        exit;
    }

    // 4. Verify OTP
    if ($otpRecord['otp'] === $otp) {
        // Successful verification - Delete the used OTP
        $stmt = $pdo->prepare("DELETE FROM otps WHERE phone = ?");
        $stmt->execute([$phone]);

        // Check if user exists by phone
        $stmt = $pdo->prepare("SELECT id, email, phone, name, role FROM users WHERE phone = ? AND is_deleted = 0");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        $name = $_SESSION['temp_name'] ?? '';
        unset($_SESSION['temp_name']);
        unset($_SESSION['temp_phone']);

        if (!$user) {
            // New user registration
            $stmt = $pdo->prepare("INSERT INTO users (phone, name, role) VALUES (?, ?, 'customer')");
            $stmt->execute([$phone, $name ?: 'Customer']);
            $userId = $pdo->lastInsertId();
            $userName = $name ?: 'Customer';
            $userRole = 'customer';
        } else {
            $userId = $user['id'];
            $userRole = $user['role'];
            // Update name if provided and different
            if ($name && $name !== $user['name']) {
                $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
                $stmt->execute([$name, $userId]);
                $userName = $name;
            } else {
                $userName = $user['name'] ?: ($name ?: 'Customer');
            }
        }

        // Set session
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['user_name'] = $userName;
        $_SESSION['user_role'] = $userRole;
        $_SESSION['logged_in'] = true;

        // Link pending guest address to this user if present
        if (isset($_SESSION['pending_address_id'])) {
            $pendingAddressId = $_SESSION['pending_address_id'];
            
            // Check if user already has a default address
            $stmt = $pdo->prepare("SELECT id FROM user_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
            $stmt->execute([$userId]);
            $existingDefault = $stmt->fetch();

            $isDefault = $existingDefault ? 0 : 1;
            $updateStmt = $pdo->prepare("UPDATE user_addresses SET user_id = ?, is_default = ? WHERE id = ? AND user_id IS NULL");
            $updateStmt->execute([$userId, $isDefault, $pendingAddressId]);

            unset($_SESSION['pending_address_id']);
        }

        // Persistent Login (30 Days)
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));
        $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, auth_token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $token, $expiry]);
        setcookie('auth_token', $token, [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        // Get default address to return to frontend
        $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
        $stmt->execute([$userId]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'message' => 'Login successful!',
            'address' => $address,
            'role' => $userRole
        ]);
    } else {
        // Increment attempts on failure
        $stmt = $pdo->prepare("UPDATE otps SET attempts = attempts + 1 WHERE phone = ?");
        $stmt->execute([$phone]);
        
        $remaining = 4 - (int)$otpRecord['attempts'];
        $msg = $remaining > 0 
            ? "Invalid OTP. You have {$remaining} attempt(s) left." 
            : "Invalid OTP. This code is now locked for security. Please resend a new one.";
            
        echo json_encode(['status' => 'error', 'message' => $msg]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
