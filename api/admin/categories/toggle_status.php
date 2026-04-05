<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = !empty($data['id']) ? (int) $data['id'] : null;

    if (!$id) {
        throw new Exception('Category ID is required.');
    }

    // Get current status
    $stmt = $pdo->prepare('SELECT status FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $status = $stmt->fetchColumn();

    if ($status === false) {
        throw new Exception('Category not found.');
    }

    $newStatus = ($status == 1) ? 0 : 1;

    $stmt = $pdo->prepare('UPDATE categories SET status = ? WHERE id = ?');
    $stmt->execute([$newStatus, $id]);

    echo json_encode([
        'success' => true, 
        'message' => 'Category status updated successfully', 
        'new_status' => $newStatus
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
