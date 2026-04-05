<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM store_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    file_put_contents('get_log.txt', date('[Y-m-d H:i:s] ') . "Fetched settings\n", FILE_APPEND);

    echo json_encode(['success' => true, 'settings' => $settings]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
