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
$email = $data['email'] ?? '';
$otp = $data['otp'] ?? '';

if (!$email || !$otp) {
    echo json_encode(['status' => 'error', 'message' => 'Email and OTP are required']);
    exit;
}

try {
    // 1. Get the current OTP record
    $stmt = $pdo->prepare("SELECT * FROM otps WHERE email = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$email]);
    $otpRecord = $stmt->fetch();
    
    if (!$otpRecord) {
        echo json_encode(['status' => 'error', 'message' => 'No OTP found or expired. Please resend.']);
        exit;
    }

    // 2. Check Expiry
    if (strtotime($otpRecord['expires_at']) < time()) {
        echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please resend a new one.']);
        exit;
    }

    // 3. Check Brute Force (Max 5 attempts)
    if ($otpRecord['attempts'] >= 5) {
        echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. This OTP is now invalid. Please resend a new one.']);
        // Delete compromised OTP
        $stmt = $pdo->prepare("DELETE FROM otps WHERE email = ?");
        $stmt->execute([$email]);
        exit;
    }

    // 4. Verify OTP
    if ($otpRecord['otp'] === $otp) {
        // Successful verification - Delete the used OTP
        $stmt = $pdo->prepare("DELETE FROM otps WHERE email = ?");
        $stmt->execute([$email]);

        // Check if user exists, if not create them
        $stmt = $pdo->prepare("SELECT id, email, name, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $name = $_SESSION['temp_name'] ?? '';
        unset($_SESSION['temp_name']); // Clear temp name

        if (!$user) {
            $stmt = $pdo->prepare("INSERT INTO users (email, name, role) VALUES (?, ?, 'customer')");
            $stmt->execute([$email, $name]);
            $userId = $pdo->lastInsertId();
            $userName = $name;
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
                $userName = $user['name'] ?: ($name ?: 'User');
            }
        }

        // Set session
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $userName;
        $_SESSION['user_role'] = $userRole;
        $_SESSION['logged_in'] = true;

        // --- NEW: Link pending guest address to this user ---
        if (isset($_SESSION['pending_address_id'])) {
            $pendingAddressId = $_SESSION['pending_address_id'];
            
            // Check if user already has a default address
            $stmt = $pdo->prepare("SELECT id FROM user_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
            $stmt->execute([$userId]);
            $existingDefault = $stmt->fetch();

            // Link the guest address and make it default if no other default exists
            $isDefault = $existingDefault ? 0 : 1;
            $updateStmt = $pdo->prepare("UPDATE user_addresses SET user_id = ?, is_default = ? WHERE id = ? AND user_id IS NULL");
            $updateStmt->execute([$userId, $isDefault, $pendingAddressId]);

            unset($_SESSION['pending_address_id']);
        }

        // --- NEW: Persistent Login (30 Days) ---
        // ... (existing persistent logic) ...
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
        $stmt = $pdo->prepare("UPDATE otps SET attempts = attempts + 1 WHERE email = ?");
        $stmt->execute([$email]);
        
        $remaining = 4 - $otpRecord['attempts'];
        $msg = $remaining > 0 
            ? "Invalid OTP. You have $remaining attempts left before this code is locked." 
            : "Invalid OTP. This code is now locked for security. Please resend a new one.";
            
        echo json_encode(['status' => 'error', 'message' => $msg]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
