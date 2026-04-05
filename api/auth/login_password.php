<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';
safe_session_start();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}
$identifier = trim($data['identifier'] ?? ''); // email or phone
$password = $data['password'] ?? '';

if (empty($identifier) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Credentials are required']);
    exit;
}

try {
    // Check for both email and phone
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR phone = ?) AND is_deleted = 0");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Restricted to certain roles for this specific login if needed, or check in FE
        if (!in_array($user['role'], ['admin', 'super_admin', 'delivery', 'vendor'])) {
             echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
             exit;
        }

        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Login successful',
            'role' => $user['role'],
            'redirect' => $user['role'] === 'delivery' ? 'delivery/dashboard' : ($user['role'] === 'vendor' ? 'vendor/' : 'admin/dashboard')
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
