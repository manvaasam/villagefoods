<?php
/**
 * Admin Login Page
 * Email + Password Authentication
 */
require_once '../includes/db.php';
require_once '../includes/auth_helper.php';

if (isset($_SESSION['logged_in']) && $_SESSION['user_role'] === 'admin') {
    header('Location: dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Village Foods</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <style>
        :root { --primary: #10b981; --dark: #064e3b; }
        body { margin: 0; padding: 0; font-family: 'Sora', sans-serif; background: #f8fafc; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: white; padding: 40px; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .logo { width: 120px; margin-bottom: 24px; }
        h1 { font-size: 24px; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
        p { color: #64748b; font-size: 14px; margin-bottom: 32px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 8px; }
        input { width: 100%; padding: 12px 16px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 15px; box-sizing: border-box; }
        input:focus { outline: none; border-color: var(--primary); }
        .login-btn { width: 100%; padding: 14px; border-radius: 12px; border: none; background: var(--primary); color: white; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.2s; }
        .login-btn:hover { background: var(--dark); }
        .toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); padding: 12px 24px; border-radius: 99px; color: white; font-size: 14px; display: none; z-index: 9999; }
        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 48px; }
        .pw-toggle { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0; display: flex; align-items: center; }
        .pw-toggle:hover { color: var(--primary); }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="../assets/images/logo/VillageFoods Delivery Logo.png" alt="Village Foods" class="logo">
        <h1>Admin Login</h1>
        <p>Access the management dashboard</p>

        <form onsubmit="handleLogin(event)">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="email" required placeholder="admin@email.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="pw-wrap">
                    <input type="password" id="password" required placeholder="••••••••">
                    <button type="button" class="pw-toggle" onclick="togglePw()" title="Show/Hide Password">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="login-btn" id="loginBtn">Sign In</button>
        </form>
    </div>
    <div id="toast" class="toast"></div>

    <script>
        function togglePw() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            }
        }

        async function handleLogin(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const btn = document.getElementById('loginBtn');
            const toast = document.getElementById('toast');

            btn.disabled = true;
            btn.textContent = 'Authenticating...';

            try {
                const res = await fetch('../api/admin/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                const data = await res.json();

                if(data.status === 'success') {
                    window.location.href = 'dashboard';
                } else {
                    toast.textContent = data.message;
                    toast.style.background = '#ef4444';
                    toast.style.display = 'block';
                    setTimeout(() => toast.style.display = 'none', 3000);
                }
            } catch(err) {
                console.error(err);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Sign In';
            }
        }
    </script>
</body>
</html>
