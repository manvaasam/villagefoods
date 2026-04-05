<?php
session_start();
require_once '../../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'delivery') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['is_online'])) {
    $isOnline = $data['is_online'] ? 1 : 0;
    try {
        $stmt = $pdo->prepare("UPDATE delivery_details SET is_online = ? WHERE user_id = ?");
        $stmt->execute([$isOnline, $userId]);
        echo json_encode(['success' => true, 'is_online' => $isOnline]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    // Just fetch current status
    try {
        $stmt = $pdo->prepare("SELECT is_online FROM delivery_details WHERE user_id = ?");
        $stmt->execute([$userId]);
        $isOnline = $stmt->fetchColumn() ?: 0;
        echo json_encode(['success' => true, 'is_online' => $isOnline]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
