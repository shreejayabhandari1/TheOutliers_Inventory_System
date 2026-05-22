<?php
require_once __DIR__ . '/../includes/session.php';
if (isset($_SESSION['user_id'])) { header("Location: /storehub/client/products.php"); exit(); }
$error   = $_GET['error']   ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – StoreHub</title>
    <link rel="stylesheet" href="/storehub/assets/css/animations.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    :root{--brown-dark:#6F4532;--brown-main:#6F4532;--brown-light:#BD7559;--brown-accent:#BD7559;--cream-bg:#F8EDEB;--white:#FFFFFF;--text-dark:#2D1A10;--text-mid:#6F4532;--text-light:#BD7559;--border:#F3D1CB;--green:#2D7A4F;--green-bg:#E8F5EE;--red:#C0392B;--red-bg:#FDECEA;--shadow-md:0 4px 16px rgba(90,40,15,.14);--radius:8px;--radius-lg:12px;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Inter',sans-serif;background:var(--cream-bg);color:var(--text-dark);font-size:14px;line-height:1.5;}
    a{text-decoration:none;color:inherit;}
    .text-center{text-align:center;}.mt-4{margin-top:16px;}.text-sm{font-size:13px;}
    .btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 20px;border-radius:var(--radius);font-family:'Inter',sans-serif;font-size:14px;font-weight:500;cursor:pointer;border:none;transition:all .2s;}
    .btn-primary{background:var(--brown-accent);color:#fff;}.btn-primary:hover{background:var(--brown-main);}
    .btn-full{width:100%;padding:12px;}
    .form-group{margin-bottom:16px;}
    .form-group label{display:block;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--text-mid);margin-bottom:6px;}
    .form-group input{width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:var(--radius);font-family:'Inter',sans-serif;font-size:14px;color:var(--text-dark);background:#fff;transition:border-color .2s;outline:none;}
    .form-group input:focus{border-color:var(--brown-accent);}
    .form-group input::placeholder{color:var(--text-light);}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .alert{padding:12px 16px;border-radius:var(--radius);font-size:13.5px;margin-bottom:16px;display:flex;align-items:center;gap:10px;}
    .alert-success{background:var(--green-bg);color:var(--green);border:1px solid #B8DFC9;}
    .alert-error{background:var(--red-bg);color:var(--red);border:1px solid #F5C6C0;}
    .auth-page{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#8B4A28 0%,#C07840 50%,#D4956A 100%);padding:20px;}
    .auth-container{display:flex;width:900px;border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-md);}
    .auth-left{width:280px;background:rgba(0,0,0,.18);padding:44px 32px;color:#fff;display:flex;flex-direction:column;justify-content:space-between;flex-shrink:0;}
    .auth-logo{display:flex;align-items:center;gap:12px;margin-bottom:28px;}
    .auth-logo-icon{width:40px;height:40px;background:rgba(255,255,255,.25);border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;}
    .auth-logo h2{font-size:20px;font-weight:600;}
    .auth-tagline{font-size:13px;opacity:.85;line-height:1.6;margin-bottom:36px;}
    .auth-stats{display:flex;flex-direction:column;gap:14px;}
    .auth-stat-card{background:rgba(255,255,255,.15);border-radius:var(--radius);padding:14px 18px;}
    .auth-stat-card .num{font-size:26px;font-weight:700;}
    .auth-stat-card .label{font-size:12px;opacity:.8;margin-top:2px;}
    .auth-right{flex:1;background:#fff;padding:36px 40px;display:flex;flex-direction:column;justify-content:center;}
    .auth-right h1{font-size:24px;font-weight:700;color:var(--text-dark);margin-bottom:4px;}
    .auth-right .subtitle{color:var(--text-mid);font-size:13px;margin-bottom:22px;}
    @media(max-width:700px){.auth-container{flex-direction:column;width:100%;}.auth-left{width:100%;padding:24px;}.auth-right{padding:24px;}.form-row{grid-template-columns:1fr;}}
    </style>

<style>
body{
overflow-x:hidden;
}
.float-circle{
position:fixed;
width:240px;
height:240px;
border-radius:50%;
background:rgba(255,255,255,0.08);
animation:floatAnim 8s ease-in-out infinite;
z-index:0;
}
.float-circle.one{top:10%;left:5%}
.float-circle.two{bottom:8%;right:6%;animation-delay:2s}
@keyframes floatAnim{
0%{transform:translateY(0)}
50%{transform:translateY(-20px)}
100%{transform:translateY(0)}
}
.login-card,.auth-card,.register-card,form{
animation:fadeIn .7s ease;
position:relative;
z-index:2;
}
@keyframes fadeIn{
from{opacity:0;transform:scale(.96)}
to{opacity:1;transform:scale(1)}
}
button:hover{
transform:translateY(-2px) scale(1.02);
transition:.25s;
}
input:focus{
transform:scale(1.01);
transition:.2s;
}
</style>
<div class="float-circle one"></div>
<div class="float-circle two"></div>

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
                <p class="auth-tagline">Create your free account today.<br>Start ordering electronics instantly.</p>
            </div>
            <div class="auth-stats">
                <div class="auth-stat-card"><div class="num">500+</div><div class="label">Products available</div></div>
                <div class="auth-stat-card"><div class="num">Free</div><div class="label">Account forever</div></div>
            </div>
        </div>
        <div class="auth-right">
            <h1>Create Account</h1>
            <p class="subtitle">Register to browse and order products.</p>
            <?php if ($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>
            <form action="/storehub/backend/auth/client_register.php" method="POST" onsubmit="return validateRegisterForm()">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your name" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <div class="phone-wrap">
                            <span class="phone-prefix">🇳🇵 +977</span>
                            <input type="tel" id="phone" name="phone" placeholder="98XXXXXXXX" maxlength="10" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="password" name="password" placeholder="Create a strong password" autocomplete="new-password">
                        <button type="button" class="sh-toggle-pw" aria-label="Toggle password visibility">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <div class="strength-bar-wrap">
                        <div class="strength-bar-track"><div class="strength-bar-fill" id="strengthFill"></div></div>
                        <div class="strength-label" id="strengthLabel">Enter a password</div>
                    </div>
                    <div class="pw-rules">
                        <div class="pw-rule" id="rule-len"><span class="dot"></span>At least 8 characters</div>
                        <div class="pw-rule" id="rule-upper"><span class="dot"></span>One uppercase letter (A–Z)</div>
                        <div class="pw-rule" id="rule-num"><span class="dot"></span>One number (0–9)</div>
                        <div class="pw-rule" id="rule-special"><span class="dot"></span>One special character (!@#$…)</div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" autocomplete="new-password">
                        <button type="button" class="sh-toggle-pw" aria-label="Toggle password visibility">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
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
<script>
/* Password strength live meter */
var pwInput      = document.getElementById('password');
var strengthFill = document.getElementById('strengthFill');
var strengthLabel= document.getElementById('strengthLabel');
var rules = {
    len:     { el: document.getElementById('rule-len'),     test: function(v){ return v.length >= 8; } },
    upper:   { el: document.getElementById('rule-upper'),   test: function(v){ return /[A-Z]/.test(v); } },
    num:     { el: document.getElementById('rule-num'),     test: function(v){ return /[0-9]/.test(v); } },
    special: { el: document.getElementById('rule-special'), test: function(v){ return /[^A-Za-z0-9]/.test(v); } }
};
var strLabels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
var strColors = ['#eee','#e74c3c','#e67e22','#f1c40f','#27ae60'];
var strWidths = ['0%','25%','50%','75%','100%'];

if (pwInput) {
    pwInput.addEventListener('input', function() {
        var v = this.value, score = 0;
        for (var key in rules) {
            var pass = rules[key].test(v);
            rules[key].el.classList.toggle('pass', pass);
            if (pass) score++;
        }
        var s = v.length ? score : 0;
        strengthFill.style.width      = strWidths[s];
        strengthFill.style.background = strColors[s];
        strengthLabel.textContent  = v.length ? strLabels[score] : 'Enter a password';
        strengthLabel.style.color  = v.length ? strColors[score] : '';
    });
}
</script>
</body>
</html>