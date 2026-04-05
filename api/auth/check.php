<?php
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$address = null;

if ($user_id) {
    require_once '../../includes/db.php';
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
    $stmt->execute([$user_id]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);
}

echo json_encode([
    'logged_in' => $_SESSION['logged_in'] ?? false,
    'email' => $_SESSION['user_email'] ?? null,
    'name' => $_SESSION['user_name'] ?? null,
    'role' => $_SESSION['user_role'] ?? 'customer',
    'address' => $address
]);
?>
