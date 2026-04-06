<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';
safe_session_start();

// Clear persistent token if exists
if (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
    
    // Delete from DB
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE auth_token = ?");
    $stmt->execute([$token]);
    
    // Clear Cookie
    setcookie('auth_token', '', time() - 3600, '/');
}

session_destroy();
header('Location: ../../index');
exit;
?>
