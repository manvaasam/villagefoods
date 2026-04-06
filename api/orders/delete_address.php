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
$userId = $_SESSION['user_id'] ?? null;
$addressId = $data['address_id'] ?? null;

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if (!$addressId) {
    echo json_encode(['status' => 'error', 'message' => 'Address ID is required']);
    exit;
}

try {
    // Check if the address belongs to the user
    $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$addressId, $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Address deleted successfully!'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Address not found or unauthorized']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
