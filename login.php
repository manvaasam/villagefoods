<?php
session_start();
$pageTitle = 'Login — Village Foods';
require_once 'includes/db.php';

// If already logged in, redirect based on role (or to the requested redirect URL)
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Honor redirect param if safe (same site only)
    if (!empty($_GET['redirect'])) {
        $redirect = $_GET['redirect'];
        // Only allow redirects within this app
        if (strpos($redirect, '/new_food/') === 0) {
            header('Location: ' . $redirect);
            exit;
        }
    }
    // Default role-based redirect
    if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'super_admin') header('Location: admin/dashboard');
    elseif ($_SESSION['user_role'] === 'vendor') header('Location: vendor/');
    elseif ($_SESSION['user_role'] === 'delivery') header('Location: delivery/dashboard');
    else header('Location: index');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #1a9c3e;
            --primary-dark: #147a30;
            --bg: #f8fafc;
            --text: #1e293b;
            --text-muted: #64748b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        h1, h2, h3 { font-family: 'Sora', sans-serif; }
        body { background: var(--bg); color: var(--text); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .login-card { background: white; padding: 40px; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; border: 1px solid #e2e8f0; }
        .logo-box { margin-bottom: 24px; }
        .logo-box img { height: 100px; }
        h1 { font-size: 24px; font-weight: 800; margin-bottom: 8px; color: var(--text); }
        p { color: var(--text-muted); font-size: 14px; margin-bottom: 32px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 8px; color: var(--text); }
        .form-input { width: 100%; padding: 12px 16px; border-radius: 12px; border: 1.5px solid #e2e8f0; outline: none; transition: 0.2s; font-size: 15px; }
        .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(26, 156, 62, 0.1); }
        .login-btn { width: 100%; padding: 14px; border-radius: 12px; border: none; background: var(--primary); color: white; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.2s; margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .login-btn:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .login-btn:active { transform: translateY(0); }
        .login-btn:disabled { opacity: 0.7; cursor: not-allowed; }
        .footer-links { margin-top: 24px; font-size: 13px; color: var(--text-muted); }
        .footer-links a { color: var(--primary); text-decoration: none; font-weight: 700; }
        .spinner { width: 20px; height: 20px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: white; animation: spin 0.8s linear infinite; display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        /* Password Toggle Styling */
        .password-wrapper { position: relative; }
        .toggle-password { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 4px; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .toggle-password:hover { color: var(--primary); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-box">
             <a href="index.php"><img src="assets/images/logo/VillageFoods Delivery Logo.png" alt="Village Foods"></a>
        </div>
        <h1>Portal Login</h1>
        <p>Sign in to your account to continue</p>
        
        <div id="errorMsg" class="error-msg"></div>

        <form id="loginForm">
            <div class="form-group">
                <label class="form-label">Email or Phone Number</label>
                <input class="form-input" type="text" id="identifier" placeholder="Enter your email or phone" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="password-wrapper">
                    <input class="form-input" type="password" id="password" placeholder="••••••••" required>
                    <button type="button" class="toggle-password" id="togglePassword" tabindex="-1">
                        <i data-lucide="eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="login-btn" id="loginBtn">
                <span id="btnText">Sign In</span>
                <div class="spinner" id="btnSpinner"></div>
            </button>
        </form>

        <div class="footer-links">
            Don't have an account? <a href="index.php">Visit Shop</a>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        const errorMsg = document.getElementById('errorMsg');
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        // Toggle Password Visibility
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle Icon
            const icon = togglePassword.querySelector('i');
            if (type === 'text') {
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        });

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const identifier = document.getElementById('identifier').value.trim();
            const password = document.getElementById('password').value;
            
            if(!identifier || !password) return;

            // Reset UI
            errorMsg.style.display = 'none';
            loginBtn.disabled = true;
            btnText.style.display = 'none';
            btnSpinner.style.display = 'block';

            try {
                const response = await fetch('api/auth/login_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ identifier, password })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    window.location.href = result.redirect || 'index.php';
                } else {
                    errorMsg.textContent = result.message || 'Invalid login details';
                    errorMsg.style.display = 'block';
                    loginBtn.disabled = false;
                    btnText.style.display = 'block';
                    btnSpinner.style.display = 'none';
                }
            } catch (err) {
                errorMsg.textContent = 'Connection error. Please try again.';
                errorMsg.style.display = 'block';
                loginBtn.disabled = false;
                btnText.style.display = 'block';
                btnSpinner.style.display = 'none';
            }
        });
    </script>
</body>
</html>
