<?php
// ============================================
// Client Register Page
// ============================================
require_once __DIR__ . '/../includes/session.php';

if (isset($_SESSION['user_id'])) {
    header("Location: /storehub/client/products.php");
    exit();
}

$error   = $_GET['error']   ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – StoreHub</title>    <style>

    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    :root{--brown-dark:#6F4532;--brown-main:#6F4532;--brown-light:#BD7559;--brown-accent:#BD7559;--cream-bg:#F8EDEB;--cream-light:#F8EDEB;--white:#FFFFFF;--text-dark:#2D1A10;--text-mid:#6F4532;--text-light:#BD7559;--border:#F3D1CB;--green:#2D7A4F;--green-bg:#E8F5EE;--red:#C0392B;--red-bg:#FDECEA;--orange:#D4750A;--orange-bg:#FEF3E2;--shadow:0 2px 8px rgba(90,40,15,.10);--shadow-md:0 4px 16px rgba(90,40,15,.14);--radius:8px;--radius-lg:12px;--sidebar-w:220px;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Inter',sans-serif;background:var(--cream-bg);color:var(--text-dark);font-size:14px;line-height:1.5;}
    a{text-decoration:none;color:inherit;}
    .text-center{text-align:center;}.mt-4{margin-top:16px;}.mb-4{margin-bottom:16px;}.text-sm{font-size:13px;}.text-muted{color:var(--text-light);}.font-bold{font-weight:700;}.hidden{display:none;}

    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 20px;border-radius:var(--radius);font-family:'Inter',sans-serif;font-size:14px;font-weight:500;cursor:pointer;border:none;transition:all .2s;}
    .btn-primary{background:var(--brown-accent);color:#fff;}.btn-primary:hover{background:var(--brown-main);}
    .btn-secondary{background:var(--cream-bg);color:var(--text-dark);border:1.5px solid var(--border);}.btn-secondary:hover{background:var(--border);}
    .btn-danger{background:var(--red-bg);color:var(--red);border:1.5px solid #F5C6C0;}.btn-danger:hover{background:var(--red);color:#fff;}
    .btn-full{width:100%;padding:12px;}.btn-sm{padding:6px 14px;font-size:13px;}

    .form-group{margin-bottom:18px;}
    .form-group label{display:block;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--text-mid);margin-bottom:6px;}
    .form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:var(--radius);font-family:'Inter',sans-serif;font-size:14px;color:var(--text-dark);background:#fff;transition:border-color .2s;outline:none;}
    .form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--brown-accent);}
    .form-group input::placeholder,.form-group textarea::placeholder{color:var(--text-light);}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .form-check-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
    .form-check-row label{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-mid);cursor:pointer;}
    .form-check-row a{font-size:13px;color:var(--brown-accent);}
    .filter-bar{display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap;}
    .search-input-wrap{position:relative;flex:1;min-width:200px;max-width:320px;}
    .search-input-wrap input{width:100%;padding:9px 14px 9px 36px;border:1.5px solid var(--border);border-radius:var(--radius);font-size:13.5px;background:#fff;outline:none;font-family:'Inter',sans-serif;color:var(--text-dark);}
    .search-input-wrap input:focus{border-color:var(--brown-accent);}
    .search-icon{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-light);font-size:15px;}
    .filter-select{padding:9px 32px 9px 12px;border:1.5px solid var(--border);border-radius:var(--radius);font-size:13.5px;background:#fff;outline:none;font-family:'Inter',sans-serif;color:var(--text-dark);cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%23A08060' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;}
    .alert{padding:12px 16px;border-radius:var(--radius);font-size:13.5px;margin-bottom:16px;display:flex;align-items:center;gap:10px;}
    .alert-success{background:var(--green-bg);color:var(--green);border:1px solid #B8DFC9;}
    .alert-error{background:var(--red-bg);color:var(--red);border:1px solid #F5C6C0;}
    .alert-warning{background:var(--orange-bg);color:var(--orange);border:1px solid #F5D99A;}

    .auth-page{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#8B4A28 0%,#C07840 50%,#D4956A 100%);}
    .auth-container{display:flex;width:860px;min-height:500px;border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-md);}
    .auth-left{width:320px;background:rgba(0,0,0,.15);padding:48px 36px;color:#fff;display:flex;flex-direction:column;justify-content:space-between;}
    .auth-logo{display:flex;align-items:center;gap:12px;margin-bottom:32px;}
    .auth-logo-icon{width:40px;height:40px;background:rgba(255,255,255,.25);border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;}
    .auth-logo h2{font-size:20px;font-weight:600;}
    .auth-tagline{font-size:13px;opacity:.85;line-height:1.6;margin-bottom:40px;}
    .auth-stats{display:flex;flex-direction:column;gap:16px;}
    .auth-stat-card{background:rgba(255,255,255,.15);border-radius:var(--radius);padding:16px 20px;}
    .auth-stat-card .num{font-size:28px;font-weight:700;}
    .auth-stat-card .label{font-size:12px;opacity:.8;margin-top:2px;}
    .auth-right{flex:1;background:#fff;padding:48px 44px;display:flex;flex-direction:column;justify-content:center;}
    .auth-right h1{font-size:26px;font-weight:700;color:var(--text-dark);margin-bottom:6px;}
    .auth-right .subtitle{color:var(--text-mid);font-size:13px;margin-bottom:32px;}
    @media(max-width:700px){.auth-container{flex-direction:column;width:95%;}.auth-left{width:100%;padding:24px;}.auth-right{padding:28px 24px;}}

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
                    Create your free account today.<br>
                    Start ordering electronics instantly.
                </p>
            </div>
        </div>

        <div class="auth-right">
            <h1>Create Account</h1>
            <p class="subtitle">Register to browse and order products.</p>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form action="/storehub/backend/auth/client_register.php" method="POST"
                  onsubmit="return validateRegisterForm()">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="John Smith">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" >
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="At least 6 characters">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password">
                </div>

                <button type="submit" class="btn btn-primary btn-full">Create Account</button>
            </form>

            <p class="text-center mt-4 text-sm">
                Already have an account?
                <a href="/storehub/client/login.php" style="color:var(--brown-accent);font-weight:600">Sign in</a>
            </p>
        </div>
    </div>
</div>
<script src="/storehub/assets/js/main.js"></script>
</body>
</html>
