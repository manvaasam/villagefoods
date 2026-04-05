<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['admin', 'super_admin']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) throw new Exception('No data provided');

    $stmt = $pdo->prepare("INSERT INTO store_settings (setting_key, setting_value) 
                          VALUES (?, ?) 
                          ON DUPLICATE KEY UPDATE setting_value = ?");
    
    foreach ($data as $key => $value) {
        file_put_contents('save_log.txt', date('[Y-m-d H:i:s] ') . "Saving $key = $value\n", FILE_APPEND);
        if ($key === 'store_email' && !empty($value)) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid store email address format');
            }
        }
        $stmt->execute([$key, $value, $value]);
    }

    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
