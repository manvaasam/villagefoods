<?php
session_start();
// If already logged in as delivery, go to dashboard
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'delivery') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Registration — Village Foods</title>
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@0.321.0/font/lucide.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #020617;
            --primary: #10b981;
            --primary-dark: #059669;
            --primary-glow: rgba(16, 185, 129, 0.2);
            --glass: rgba(15, 23, 42, 0.6);
            --border: rgba(255, 255, 255, 0.08);
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
            color: #f8fafc;
        }

        /* Abstract Background Elements */
        .bg-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 20s infinite alternate ease-in-out;
        }

        .blob-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #064e3b 0%, transparent 70%);
            top: -100px;
            right: -100px;
        }

        .blob-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #0f172a 0%, transparent 70%);
            bottom: -50px;
            left: -50px;
            animation-delay: -5s;
        }

        .blob-3 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, #10b981 0%, transparent 70%);
            top: 40%;
            left: 10%;
            opacity: 0.15;
            animation-duration: 15s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, 40px) scale(1.1); }
            100% { transform: translate(-20px, -30px) scale(1); }
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            padding: 24px;
            position: relative;
        }

        .login-card {
            background: var(--glass);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border);
            border-radius: 40px;
            padding: 40px;
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon-box {
            display: inline-flex;
            background: linear-gradient(135deg, #064e3b, #065f46);
            padding: 14px;
            border-radius: 20px;
            margin-bottom: 16px;
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3);
        }

        .logo-icon-box i {
            font-size: 28px;
            color: #10b981;
        }

        .login-card h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(to right, #f8fafc, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-card p {
            color: #94a3b8;
            margin-top: 8px;
            font-size: 14px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full {
            grid-column: span 2;
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: 16px;
        }

        .form-input {
            width: 100%;
            background: rgba(2, 6, 23, 0.4);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px 14px 14px 44px;
            color: white;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(2, 6, 23, 0.8);
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 16px;
            padding: 16px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -8px rgba(16, 185, 129, 0.5);
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .footer-links {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #475569;
        }

        .footer-links a {
            color: #10b981;
            text-decoration: none;
            font-weight: 600;
        }

        /* Toast Styling */
        #toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
        }

        .toast {
            background: #1e293b;
            color: white;
            padding: 16px 24px;
            border-radius: 16px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4);
        }

        .loader {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="bg-elements">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>
    
    <div id="toast-container"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon-box">
                    <i class="lucide-user-plus"></i>
                </div>
                <h1>Partner Signup</h1>
                <p>Join our delivery fleet and start earning</p>
            </div>

            <form id="signupForm">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="lucide-mail"></i>
                            <input type="email" id="email" class="form-input" placeholder="partner@email.com" required>
                        </div>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <i class="lucide-lock"></i>
                            <input type="password" id="password" class="form-input" placeholder="••••••••" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="login-btn" id="submitBtn">
                    <span>Create Account</span>
                    <i class="lucide-arrow-right"></i>
                </button>
            </form>

            <div class="footer-links">
                Already have an account? <a href="index.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        const toastContainer = document.getElementById('toast-container');

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast';
            if (type === 'error') toast.style.borderLeftColor = '#ef4444';
            
            toast.innerHTML = `
                <i class="lucide-${type === 'success' ? 'check-circle' : 'alert-circle'}" style="color:${type === 'success' ? '#10b981' : '#ef4444'}"></i>
                <span>${message}</span>
            `;
            toastContainer.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }

        document.getElementById('signupForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const originalContent = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<div class="loader"></div> <span>Creating account...</span>';

            const data = {
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            };

            try {
                const resp = await fetch('../api/auth/delivery_signup.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await resp.json();

                if (result.status === 'success') {
                    showToast('Account created! Let\'s complete your profile.');
                    setTimeout(() => {
                        window.location.href = 'profile.php';
                    }, 1500);
                } else {
                    showToast(result.message || 'Signup failed', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            } catch (err) {
                showToast('Connection error. Please try again.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        });
    </script>
</body>
</html>
