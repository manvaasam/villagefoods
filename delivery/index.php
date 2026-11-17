<?php
session_start();
// If already logged in as delivery, go to dashboard
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'delivery') {
    header('Location: dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Login — Village Foods</title>
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@0.321.0/font/lucide.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <style>
        :root {
            --bg-dark: #020617;
            --primary: #10b981;
            --primary-dark: #059669;
            --primary-glow: rgba(16, 185, 129, 0.2);
            --glass: rgba(15, 23, 42, 0.7);
            --border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: var(--text-main);
        }

        /* Ambient Background blobs */
        .bg-blobs {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            filter: blur(80px);
        }

        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            opacity: 0.25;
            animation: move 20s infinite alternate linear;
        }

        .blob-1 { background: var(--primary); top: -200px; right: -100px; }
        .blob-2 { background: #1e3a8a; bottom: -200px; left: -100px; animation-delay: -5s; }

        @keyframes move {
            from { transform: translate(0,0) scale(1); }
            to { transform: translate(100px, 50px) scale(1.2); }
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--glass);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid var(--border);
            border-radius: 40px;
            padding: 32px 40px;
            box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.7);
            text-align: center;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-box {
            display: inline-flex;
            background: linear-gradient(135deg, #064e3b, #059669);
            padding: 18px;
            border-radius: 20px;
            margin-bottom: 20px;
            box-shadow: 0 20px 40px -10px rgba(16, 185, 129, 0.3);
        }

        .logo-box i { font-size: 28px; color: white; }

        h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -1px;
            background: linear-gradient(to bottom, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            color: var(--text-dim);
            font-size: 15px;
            margin: 8px 0 28px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: 18px;
            transition: 0.3s;
        }

        .form-input {
            width: 100%;
            background: rgba(2, 6, 23, 0.6);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 14px 16px 14px 50px;
            color: white;
            font-family: inherit;
            font-size: 15px;
            box-sizing: border-box;
            transition: 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(2, 6, 23, 0.9);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }

        .form-input:focus + i { color: var(--primary); }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 18px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 20px 40px -10px rgba(16, 185, 129, 0.4);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.5);
            filter: brightness(1.1);
        }

        .footer-links {
            margin-top: 32px;
            color: var(--text-dim);
            font-size: 14px;
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .footer-links a:hover { text-decoration: underline; }

        .support-link {
            display: block;
            margin-top: 10px;
            font-size: 13px;
            opacity: 0.6;
        }

        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; display: inline-block; }
    </style>
</head>
<body>
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div id="toast-container" style="position:fixed; top:20px; right:20px; z-index:1000"></div>

    <div class="login-card">
        <div class="logo-box">
            <i class="lucide-bike"></i>
        </div>
        <h1>Partner Login</h1>
        <p>Welcome back! Let's hit the road.</p>

        <form id="loginForm">
            <input type="hidden" name="role" value="delivery">
            
            <div class="form-group">
                <label class="form-label">Email or Phone</label>
                <div class="input-wrapper">
                    <input type="text" name="identifier" class="form-input" placeholder="Email or Phone" required>
                    <i class="lucide-user"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                    <i class="lucide-lock"></i>
                </div>
            </div>

            <button type="submit" class="login-btn">
                <span id="btnText">Login as Partner</span>
                <i class="lucide-arrow-right" id="btnIcon"></i>
            </button>
        </form>

        <div class="footer-links">
            Don't have an account? <a href="signup.php">Register as Partner</a>
            <a href="#" class="support-link">Difficulty logging in? Contact Support</a>
        </div>

        <button id="pwa-install-btn" onclick="installPWA()" style="display: flex; width: 100%; margin-top: 24px; padding: 12px; border: 1px solid var(--primary); background: transparent; color: var(--primary); border-radius: 14px; font-weight: 700; font-size: 14px; cursor: pointer; align-items: center; justify-content: center; gap: 8px; transition: 0.3s;">
            <i class="lucide-download"></i>
            Download App
        </button>
    </div>

    <script src="../assets/js/pwa.js"></script>
    <script>
        const Toast = {
            show: (msg, type = 'success') => {
                const container = document.getElementById('toast-container');
                const t = document.createElement('div');
                t.style.cssText = `
                    background: #1e293b; color: white; padding: 14px 24px; 
                    border-radius: 20px; margin-bottom: 12px; font-size: 14px;
                    border-left: 4px solid ${type === 'success' ? '#10b981' : '#f43f5e'};
                    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
                    animation: slideIn 0.3s ease-out; font-weight: 600;
                    backdrop-filter: blur(10px); display: flex; align-items: center; gap: 10px;
                `;
                t.innerHTML = msg;
                container.appendChild(t);
                setTimeout(() => {
                    t.style.opacity = '0';
                    t.style.transform = 'translateX(20px)';
                    t.style.transition = '0.5s';
                    setTimeout(() => t.remove(), 500);
                }, 3000);
            }
        };

        const styleTag = document.createElement('style');
        styleTag.innerHTML = `@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }`;
        document.head.appendChild(styleTag);

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.querySelector('.login-btn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');
            
            btn.disabled = true;
            btnText.textContent = 'Verifying...';
            btnIcon.className = 'lucide-loader-2 animate-spin';

            const formData = new FormData(e.target);
            try {
                const res = await fetch('../api/auth/login_password.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                
                if (result.status === 'success') {
                    Toast.show('Welcome back, partner!', 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard';
                    }, 1000);
                } else {
                    Toast.show(result.message || 'Invalid credentials', 'error');
                    btn.disabled = false;
                    btnText.textContent = 'Login as Partner';
                    btnIcon.className = 'lucide-arrow-right';
                }
            } catch (err) {
                Toast.show('Network error. Please try again.', 'error');
                btn.disabled = false;
                btnText.textContent = 'Login as Partner';
                btnIcon.className = 'lucide-arrow-right';
            }
        });
    </script>
</body>
</html>
