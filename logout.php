<?php
session_start();
require_once 'includes/db.php';

// Clear persistent token if exists
if (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
    
    // Delete from DB (if $pdo is available)
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE auth_token = ?");
            $stmt->execute([$token]);
        } catch (Exception $e) {
            // Silently fail if DB issue, still clear cookie
        }
    }
    
    // Clear Cookie
    setcookie('auth_token', '', time() - 3600, '/');
}

session_unset();
session_destroy();

// Redirect to login page
header('Location: login');
exit;
?>
