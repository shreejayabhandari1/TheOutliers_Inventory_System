<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';

$token  = trim($_GET['token'] ?? '');
$manual = $_GET['manual'] ?? ''; // fallback if email failed

$status  = ''; // 'success' | 'expired' | 'invalid' | 'already'
$message = '';

if (!$token) {
    $status  = 'invalid';
    $message = 'No verification token provided.';
} else {
    // Look up the token
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, email, email_verified, verification_expires FROM users WHERE verification_token = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$user) {
        $status  = 'invalid';
        $message = 'This verification link is invalid or has already been used.';
    } elseif ($user['email_verified']) {
        $status  = 'already';
        $message = 'Your email is already verified. You can log in.';
    } elseif (strtotime($user['verification_expires']) < time()) {
        $status  = 'expired';
        $message = 'This verification link has expired. Please register again.';
    } else {
        // Mark as verified and clear token
        $upd = mysqli_prepare($conn,
            "UPDATE users SET email_verified = 1, verification_token = NULL, verification_expires = NULL WHERE id = ?");
        mysqli_stmt_bind_param($upd, "i", $user['id']);
        mysqli_stmt_execute($upd);

        $status  = 'success';
        $message = 'Your email has been verified! You can now log in.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Email Verification – StoreHub</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
:root{--brown-main:#6F4532;--brown-accent:#BD7559;--cream-bg:#F8EDEB;--text-dark:#2D1A10;--text-mid:#6F4532;--green:#2D7A4F;--green-bg:#E8F5EE;--red:#C0392B;--red-bg:#FDECEA;--orange:#D4750A;--orange-bg:#FEF3E2;--border:#F3D1CB;--radius:8px;--radius-lg:12px;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#8B4A28 0%,#C07840 50%,#D4956A 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;}
.float-circle{position:fixed;width:240px;height:240px;border-radius:50%;background:rgba(255,255,255,.08);animation:floatAnim 8s ease-in-out infinite;z-index:0;}
.float-circle.one{top:10%;left:5%}.float-circle.two{bottom:8%;right:6%;animation-delay:2s}
@keyframes floatAnim{0%{transform:translateY(0)}50%{transform:translateY(-20px)}100%{transform:translateY(0)}}
.card{background:#fff;border-radius:var(--radius-lg);padding:48px 44px;max-width:440px;width:90%;text-align:center;position:relative;z-index:2;box-shadow:0 8px 32px rgba(90,40,15,.18);animation:popIn .6s cubic-bezier(.34,1.56,.64,1);}
@keyframes popIn{from{opacity:0;transform:scale(.88)}to{opacity:1;transform:scale(1)}}
.icon{font-size:60px;margin-bottom:20px;display:block;}
h1{font-size:22px;font-weight:700;color:var(--text-dark);margin-bottom:10px;}
p{font-size:14px;color:var(--text-mid);line-height:1.6;margin-bottom:24px;}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 28px;border-radius:var(--radius);font-family:'Inter';font-size:14px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all .2s;}
.btn-primary{background:var(--brown-accent);color:#fff;}.btn-primary:hover{background:var(--brown-main);}
.btn-secondary{background:var(--cream-bg);color:var(--text-dark);border:1.5px solid var(--border);margin-left:10px;}.btn-secondary:hover{background:var(--border);}
.logo{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:28px;}
.logo-icon{width:38px;height:38px;background:var(--brown-accent);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:17px;}
.logo h2{font-size:18px;font-weight:700;color:var(--text-dark);}
.manual-box{background:var(--orange-bg);border:1px solid #F5D99A;border-radius:var(--radius);padding:14px 16px;font-size:13px;color:var(--orange);margin-bottom:20px;text-align:left;}
</style>
</head>
<body>
<div class="float-circle one"></div>
<div class="float-circle two"></div>

<div class="card">
    <div class="logo">
        <div class="logo-icon">S</div>
        <h2>StoreHub</h2>
    </div>

    <?php if ($manual && $status === 'success'): ?>
        <div class="manual-box">
            ⚠ The verification email could not be sent (check Mailtrap config), but your account has been verified automatically since you just registered.
        </div>
    <?php endif; ?>

    <?php if ($status === 'success'): ?>
        <span class="icon">✅</span>
        <h1>Email Verified!</h1>
        <p><?= htmlspecialchars($message) ?></p>
        <a href="/storehub/client/login.php" class="btn btn-primary">Go to Login</a>

    <?php elseif ($status === 'already'): ?>
        <span class="icon">✓</span>
        <h1>Already Verified</h1>
        <p><?= htmlspecialchars($message) ?></p>
        <a href="/storehub/client/login.php" class="btn btn-primary">Go to Login</a>

    <?php elseif ($status === 'expired'): ?>
        <span class="icon">⏰</span>
        <h1>Link Expired</h1>
        <p><?= htmlspecialchars($message) ?></p>
        <a href="/storehub/client/register.php" class="btn btn-primary">Register Again</a>

    <?php else: ?>
        <span class="icon">❌</span>
        <h1>Invalid Link</h1>
        <p><?= htmlspecialchars($message) ?></p>
        <a href="/storehub/client/login.php" class="btn btn-primary">Back to Login</a>
        <a href="/storehub/client/register.php" class="btn btn-secondary">Register</a>
    <?php endif; ?>
</div>
</body>
</html>
