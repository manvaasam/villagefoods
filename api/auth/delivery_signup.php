<?php
session_start();
header('Content-Type: application/json');
require_once '../../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and Password are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address format']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters']);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND is_deleted = 0");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'An account with this email already exists']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert into users
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'delivery', 'Active')");
    $stmt->execute(['Partner', $email, $hashedPassword]);
    $userId = $pdo->lastInsertId();

    // Create delivery_partners record
    $stmt = $pdo->prepare("INSERT INTO delivery_partners (user_id, email, status) VALUES (?, ?, 'Profile Incomplete')");
    $stmt->execute([$userId, $email]);
    $partnerId = $pdo->lastInsertId();

    // Automatic Login
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = 'Partner';
    $_SESSION['user_role'] = 'delivery';

    echo json_encode(['status' => 'success', 'message' => 'Registration successful! Redirecting to profile completion...']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
