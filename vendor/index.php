<?php
/**
 * Vendor Login Page
 * Dedicated login for Vendor accounts
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';
checkPersistentLogin($pdo);

// Only vendor accounts can access vendor dashboard
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'vendor') {
    // Not logged in or not a vendor — show this login page (don't redirect away)
    // The login form below handles authentication
} else {
    header('Location: dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Login — Village Foods</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            width: 100%;
        }

        body {
            font-family: 'Sora', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #f0fdf4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: #ffffff;
            padding: 48px 40px 40px;
            border-radius: 28px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05),
                        0 20px 60px -10px rgba(255, 74, 56,0.12);
            text-align: center;
        }

        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .logo { width: 120px; margin-bottom: 12px; display: block; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0fdf4;
            color: #1a9c3e;
            border: 1px solid rgba(26, 156, 62, 0.25);
            padding: 5px 14px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: 0.3px;
        }

        h1 {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 32px;
            font-family: 'Nunito', sans-serif;
        }

        .form-group {
            text-align: left;
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 13px 16px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            font-size: 15px;
            font-family: 'Sora', sans-serif;
            color: #0f172a;
            background: #f8fafc;
            transition: all 0.2s;
            outline: none;
        }

        input:focus {
            border-color: #1a9c3e;
            background: #fff;
            box-shadow: 0 0 0 4px rgb(52 235 85 / 8%);
        }

        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 50px; }

        .pw-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .pw-toggle:hover { color: #ff4a38; }

        .login-btn {
            width: 100%;
            padding: 15px;
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, #1a9c3e, #22c55e);
            color: white;
            font-weight: 800;
            font-size: 15px;
            font-family: 'Sora', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
            letter-spacing: 0.2px;
            box-shadow: 0 0 0 4px rgb(52 235 85 / 8%);
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 0 0 4px rgb(52 235 85 / 8%);
        }

        .login-btn:active { transform: translateY(0); }
        .login-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .back-link {
            display: block;
            margin-top: 24px;
            color: #94a3b8;
            font-size: 13px;
            text-decoration: none;
            font-family: 'Nunito', sans-serif;
            transition: color 0.2s;
        }

        .back-link:hover { color: #ff4a38; }

        .toast {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 28px;
            border-radius: 99px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            display: none;
            z-index: 9999;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            font-family: 'Sora', sans-serif;
            white-space: nowrap;
        }

        @media (max-width: 480px) {
            .login-card { padding: 36px 24px 32px; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
    <div class="login-card">
        <div class="logo-wrap">
            <img src="../assets/images/logo/VillageFoods Delivery Logo.png" alt="Village Foods" class="logo">
            <div class="badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Vendor Portal
            </div>
        </div>
        <h1>Vendor Login</h1>
        <p class="subtitle">Access your shop dashboard</p>

        <form onsubmit="handleLogin(event)">
            <div class="form-group">
                <label>Email / Phone</label>
                <input type="text" id="email" required placeholder="your@email.com" autocomplete="username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="pw-wrap">
                    <input type="password" id="password" required placeholder="••••••••" autocomplete="current-password">
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

        <a href="../" class="back-link">← Back to main site</a>

        <button id="pwa-install-btn" onclick="installPWA()" style="display: flex; width: 100%; margin-top: 24px; padding: 12px; border: 1.5px solid #1a9c3e; background: transparent; color: #1a9c3e; border-radius: 14px; font-weight: 700; font-size: 14px; cursor: pointer; align-items: center; justify-content: center; gap: 8px; transition: 0.3s; font-family: 'Sora', sans-serif;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download App
        </button>
    </div>
    </div><!-- /.login-wrapper -->

    <script src="../assets/js/pwa.js"></script>
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

        function showToast(msg, color = '#ef4444') {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.style.background = color;
            t.style.display = 'block';
            setTimeout(() => t.style.display = 'none', 3500);
        }

        async function handleLogin(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const btn = document.getElementById('loginBtn');

            btn.disabled = true;
            btn.textContent = 'Signing In...';

            try {
                const res = await fetch('../api/auth/login_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ identifier: email, password })
                });
                const data = await res.json();

                if (data.status === 'success') {
                    const role = data.role || '';
                    if (role === 'vendor' || role === 'admin' || role === 'super_admin') {
                        showToast('Welcome back! Redirecting...', '#ff4a38');
                        setTimeout(() => window.location.href = 'dashboard', 800);
                    } else {
                        showToast('Access denied. This portal is for vendors only.');
                    }
                } else {
                    showToast(data.message || 'Invalid credentials');
                }
            } catch (err) {
                showToast('Connection error. Please try again.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Sign In';
            }
        }
    </script>
</body>
</html>
