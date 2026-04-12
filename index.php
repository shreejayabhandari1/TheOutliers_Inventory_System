<?php
require_once __DIR__ . '/includes/session.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'manager') {
    header("Location: /storehub/manager/dashboard.php");
    exit();
}

$error = '';

if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Login – StoreHub</title> 
       <style>

    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

:root {
    --brown-dark: #6F4532;
    --brown-main: #6F4532;
    --brown-light: #BD7559;
    --brown-accent: #BD7559;
    --cream-bg: #F8EDEB;
    --cream-light: #F8EDEB;
    --white: #FFFFFF;
    --text-dark: #2D1A10;
    --text-mid: #6F4532;
    --text-light: #BD7559;
    --border: #F3D1CB;
    --green: #2D7A4F;
    --green-bg: #E8F5EE;
    --red: #C0392B;
    --red-bg: #FDECEA;
    --orange: #D4750A;
    --orange-bg: #FEF3E2;
    --shadow: 0 2px 8px rgba(90, 40, 15, .10);
    --shadow-md: 0 4px 16px rgba(90, 40, 15, .14);
    --radius: 8px;
    --radius-lg: 12px;
    --sidebar-w: 220px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


body {
    font-family: 'Inter', sans-serif;
    background: var(--cream-bg);
    color: var(--text-dark);
    font-size: 14px;
    line-height: 1.5;
}

a {
    text-decoration: none;
    color: inherit;
}


.text-center { text-align: center; }
.mt-4 { margin-top: 16px; }
.mb-4 { margin-bottom: 16px; }
.text-sm { font-size: 13px; }
.text-muted { color: var(--text-light); }
.font-bold { font-weight: 700; }
.hidden { display: none; }

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: var(--radius);
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all .2s;
}

.btn-primary {
    background: var(--brown-accent);
    color: #fff;
}
.btn-primary:hover {
    background: var(--brown-main);
}

.btn-secondary {
    background: var(--cream-bg);
    color: var(--text-dark);
    border: 1.5px solid var(--border);
}
.btn-secondary:hover {
    background: var(--border);
}

.btn-danger {
    background: var(--red-bg);
    color: var(--red);
    border: 1.5px solid #F5C6C0;
}
.btn-danger:hover {
    background: var(--red);
    color: #fff;
}

.btn-full { width: 100%; padding: 12px; }
.btn-sm { padding: 6px 14px; font-size: 13px; }


.form-group { margin-bottom: 18px; }

.form-group label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--text-mid);
    margin-bottom: 6px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--text-dark);
    background: #fff;
    outline: none;
}


.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(
        135deg,
        #8B4A28 0%,
        #C07840 50%,
        #D4956A 100%
    );
}

.auth-container {
    display: flex;
    width: 860px;
    min-height: 500px;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.auth-left {
    width: 320px;
    background: rgba(0, 0, 0, .15);
    padding: 48px 36px;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.auth-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 32px;
}

.auth-logo-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, .25);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
}

.auth-logo h2 {
    font-size: 20px;
    font-weight: 600;
}

.auth-tagline {
    font-size: 13px;
    opacity: .85;
    line-height: 1.6;
    margin-bottom: 40px;
}

.auth-stats {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.auth-stat-card {
    background: rgba(255, 255, 255, .15);
    border-radius: var(--radius);
    padding: 16px 20px;
}

.auth-stat-card .num {
    font-size: 28px;
    font-weight: 700;
}

.auth-stat-card .label {
    font-size: 12px;
    opacity: .8;
    margin-top: 2px;
}

.auth-right {
    flex: 1;
    background: #fff;
    padding: 48px 44px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.auth-right h1 {
    font-size: 26px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 6px;
}

.auth-right .subtitle {
    color: var(--text-mid);
    font-size: 13px;
    margin-bottom: 32px;
}


@media (max-width: 700px) {
    .auth-container {
        flex-direction: column;
        width: 95%;
    }

    .auth-left {
        width: 100%;
        padding: 24px;
    }

    .auth-right {
        padding: 28px 24px;
    }
}
    </style>
</head>
<body>

<div class="auth-page">
    <div class="auth-container">

        <div class="auth-left">
            <div>
                <div class="auth-logo">
                    <div class="auth-logo-icon">S</div>
                    <h2>StoreHub</h2>
                </div>
                <p class="auth-tagline">
                    Your warehouse.<br>
                    Organised, tracked,<br>
                    and always in control.
                </p>
            </div>

            <div class="auth-stats">
                <div class="auth-stat-card">
                    <div class="num">12</div>
                    <div class="label">Products tracked</div>
                </div>
                <div class="auth-stat-card">
                    <div class="num">14</div>
                    <div class="label">Active suppliers</div>
                </div>
            </div>
        </div>

        <div class="auth-right">
            <h1>Welcome back</h1>
            <p class="subtitle">Sign in to the manager portal to access your warehouse.</p>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠ <?= $error ?></div>
            <?php endif; ?>

            <form action="/storehub/backend/auth/manager_login.php" method="POST"
                  onsubmit="return validateLoginForm()">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           
                           value="<?= isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password">
                </div>

                <div class="form-check-row">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    Sign In to Dashboard
                </button>
            </form>

            <p class="text-center mt-4 text-sm text-muted">
                Manager portal – secure access only.
            </p>
        </div>

    </div>
</div>

<script src="/storehub/assets/js/main.js"></script>
</body>
</html>
