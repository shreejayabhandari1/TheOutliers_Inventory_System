<?php
require_once __DIR__ . '/../includes/session.php';

if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'client') {
    header("Location: /storehub/client/products.php");
    exit();
}

$step    = $_GET['step'] ?? 'request'; // request | verify | reset
$email   = $_GET['email'] ?? '';
$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';

if (!in_array($step, ['request', 'verify', 'reset'], true)) {
    $step = 'request';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password – StoreHub</title>
<link rel="stylesheet" href="/storehub/assets/css/animations.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
:root{--brown-main:#6F4532;--brown-accent:#BD7559;--cream-bg:#F8EDEB;--text-dark:#2D1A10;--text-mid:#6F4532;--text-light:#BD7559;--border:#F3D1CB;--green:#2D7A4F;--green-bg:#E8F5EE;--red:#C0392B;--red-bg:#FDECEA;--shadow-md:0 4px 16px rgba(90,40,15,.14);--radius:8px;--radius-lg:12px}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#8B4A28 0%,#C07840 50%,#D4956A 100%);min-height:100vh;display:flex;align-items:center;justify-content:center}
.float-circle{position:fixed;width:240px;height:240px;border-radius:50%;background:rgba(255,255,255,0.08);animation:floatAnim 8s ease-in-out infinite;z-index:0}
.float-circle.one{top:10%;left:5%}.float-circle.two{bottom:8%;right:6%;animation-delay:2s}
@keyframes floatAnim{0%{transform:translateY(0)}50%{transform:translateY(-20px)}100%{transform:translateY(0)}}
.auth-container{display:flex;width:820px;min-height:460px;border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-md);position:relative;z-index:2;animation:fadeIn .7s ease}
@keyframes fadeIn{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}
.auth-left{width:300px;background:rgba(0,0,0,.18);padding:48px 36px;color:#fff;display:flex;flex-direction:column;justify-content:space-between}
.auth-logo{display:flex;align-items:center;gap:12px;margin-bottom:32px}
.auth-logo-icon{width:40px;height:40px;background:rgba(255,255,255,.25);border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px}
.auth-logo h2{font-size:20px;font-weight:600}.auth-tagline{font-size:13px;opacity:.85;line-height:1.7}
.steps{display:flex;flex-direction:column;gap:16px;margin-top:32px}.step-item{display:flex;align-items:flex-start;gap:12px;font-size:13px;opacity:.85}
.step-num{width:24px;height:24px;border-radius:50%;background:rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0}
.auth-right{flex:1;background:#fff;padding:48px 44px;display:flex;flex-direction:column;justify-content:center}
.auth-right h1{font-size:26px;font-weight:700;color:var(--text-dark);margin-bottom:6px}.auth-right .subtitle{color:var(--text-mid);font-size:13px;margin-bottom:28px}
.form-group{margin-bottom:18px}.form-group label{display:block;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--text-mid);margin-bottom:6px}
.form-group input{width:100%;padding:12px 14px;border:1.5px solid var(--border);border-radius:var(--radius);font-family:'Inter';font-size:14px;color:var(--text-dark);background:#fff;transition:.2s;outline:none}
.form-group input:focus{border-color:var(--brown-accent);box-shadow:0 4px 12px rgba(189,117,89,.12)}
.otp-input{text-align:center;font-size:24px!important;letter-spacing:8px;font-weight:700}
.btn{display:inline-flex;align-items:center;justify-content:center;width:100%;padding:12px;border:none;border-radius:var(--radius);background:var(--brown-accent);color:#fff;font-family:'Inter';font-size:14px;font-weight:600;cursor:pointer;transition:.25s}
.btn:hover{background:var(--brown-main);transform:translateY(-2px)}
.alert{padding:12px 16px;border-radius:var(--radius);font-size:13.5px;margin-bottom:18px;display:flex;align-items:flex-start;gap:10px}
.alert-success{background:var(--green-bg);color:var(--green);border:1px solid #B8DFC9}.alert-error{background:var(--red-bg);color:var(--red);border:1px solid #F5C6C0}
.back-link{font-size:13px;color:var(--text-mid);margin-top:18px;text-align:center}.back-link a{color:var(--brown-accent);font-weight:600}
@media(max-width:700px){.auth-container{flex-direction:column;width:95%}.auth-left{width:100%;padding:24px}.auth-right{padding:28px 24px}}
</style>
</head>
<body>
<div class="float-circle one"></div>
<div class="float-circle two"></div>

<div class="auth-container">
    <div class="auth-left">
        <div>
            <div class="auth-logo">
                <div class="auth-logo-icon">S</div>
                <h2>StoreHub</h2>
            </div>
            <p class="auth-tagline">Reset your password using OTP verification.</p>
            <div class="steps">
                <div class="step-item"><div class="step-num">1</div><div>Enter your registered email address</div></div>
                <div class="step-item"><div class="step-num">2</div><div>Check Mailtrap and enter the OTP</div></div>
                <div class="step-item"><div class="step-num">3</div><div>Create your new password</div></div>
            </div>
        </div>
    </div>

    <div class="auth-right">
        <?php if ($success): ?><div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if ($step === 'verify'): ?>
            <h1>Enter OTP</h1>
            <p class="subtitle">We sent a 6 digit OTP to <strong><?= htmlspecialchars($email) ?></strong>.</p>

            <form action="/storehub/backend/auth/verify_otp.php" method="POST">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <div class="form-group">
                    <label>OTP Code</label>
                    <input class="otp-input" type="text" name="otp" maxlength="6" pattern="[0-9]{6}" required placeholder="------" autofocus>
                </div>
                <button type="submit" class="btn">Verify OTP</button>
            </form>

        <?php elseif ($step === 'reset'): ?>
            <h1>Set New Password</h1>
            <p class="subtitle">Create a new password for <strong><?= htmlspecialchars($email) ?></strong>.</p>

            <form action="/storehub/backend/auth/reset_password.php" method="POST">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" minlength="8" required placeholder="Minimum 8 characters">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" minlength="8" required placeholder="Re-enter password">
                </div>
                <button type="submit" class="btn">Change Password</button>
            </form>

        <?php else: ?>
            <h1>Forgot Password?</h1>
            <p class="subtitle">Enter your registered client email. We will send an OTP to Mailtrap.</p>

            <form action="/storehub/backend/auth/forgot_password.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="your@email.com" autofocus>
                </div>
                <button type="submit" class="btn">Send OTP</button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            Remember your password? <a href="/storehub/client/login.php">Back to Login</a>
        </div>
    </div>
</div>
</body>
</html>
