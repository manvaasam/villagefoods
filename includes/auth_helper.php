<?php
/**
 * Village Foods - Auth Helper
 * Handles persistent login via auth_token cookies.
 */

function safe_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        // Ensure session cookie is available site-wide with secure defaults
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false, // Set to true if using HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

function checkPersistentLogin($pdo) {
    safe_session_start();
    
    // If user is already session-logged in, sync the role from DB to ensure it's current
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        if (isset($_SESSION['user_id'])) {
            try {
                $stmt = $pdo->prepare("SELECT role, name FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                if ($user) {
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['name'];
                }
            } catch (PDOException $e) {}
        }
        return;
    }

    // Check if auth_token cookie exists
    if (!isset($_COOKIE['auth_token'])) {
        return;
    }

    $token = $_COOKIE['auth_token'];

    try {
        // Look up token in database and ensure it hasn't expired
        $stmt = $pdo->prepare("
            SELECT s.*, u.id as user_id, u.email, u.name, u.role 
            FROM user_sessions s
            JOIN users u ON s.user_id = u.id
            WHERE s.auth_token = ? AND s.expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $session = $stmt->fetch();

        if ($session) {
            // Token is valid! Log the user in
            $_SESSION['user_id'] = $session['user_id'];
            $_SESSION['user_email'] = $session['email'];
            $_SESSION['user_name'] = $session['name'];
            $_SESSION['user_role'] = $session['role'];
            $_SESSION['logged_in'] = true;
        } else {
            // Token is invalid or expired - Clear the cookie
            setcookie('auth_token', '', time() - 3600, '/');
        }
    } catch (PDOException $e) {
        // Log error silently
    }
}

function requireRole($allowedRoles) {
    safe_session_start();
    
    // Attempt to sync role if $pdo is available in global scope
    global $pdo;
    if (isset($pdo)) {
        checkPersistentLogin($pdo);
    }
    
    // Detect if this is an API request (called via fetch/XHR) or a browser page load
    $isApiRequest = (
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false
    );
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        if ($isApiRequest) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
        } else {
            // For browser page loads, redirect to login
            header('Location: /new_food/login?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
        }
        exit;
    }

    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        if ($isApiRequest) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden: Insufficient permissions']);
        } else {
            header('Location: /new_food/login?error=forbidden');
        }
        exit;
    }
}
?>
