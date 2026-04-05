<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $name = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $email = trim($data['email'] ?? '');
    $role = $data['role'] ?? 'customer';
    $password = $data['password'] ?? null;

    if (!$name || !$phone || !$email) throw new Exception('Name, phone and email are required');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Invalid email address format');

    if ($id) {
        // Update
        $sql = "UPDATE users SET name = ?, phone = ?, email = ?, role = ? WHERE id = ?";
        $params = [$name, $phone, $email, $role, $id];
        
        if ($password) {
            $sql = "UPDATE users SET name = ?, phone = ?, email = ?, role = ?, password = ? WHERE id = ?";
            $params = [$name, $phone, $email, $role, password_hash($password, PASSWORD_DEFAULT), $id];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $message = "User updated successfully";
    } else {
        // Create
        if (!$password) throw new Exception('Password is required for new users');
        
        // Check if email or phone exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (phone = ? OR email = ?) AND is_deleted = 0");
        $stmt->execute([$phone, $email]);
        if ($stmt->fetch()) throw new Exception('Phone number or email already registered');

        $stmt = $pdo->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        $id = $pdo->lastInsertId();
        $message = "User added successfully";
    }

    echo json_encode(['success' => true, 'message' => $message, 'id' => $id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
