<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/client/forgot_password.php"); exit();
}

// Must have email in session from step 1
if (empty($_SESSION['reset_email'])) {
    header("Location: /storehub/client/forgot_password.php"); exit();
}

$email = $_SESSION['reset_email'];
$code  = trim($_POST['code'] ?? '');

// Basic format check
if (!preg_match('/^\d{6}$/', $code)) {
    header("Location: /storehub/client/verify_code.php?error=" . urlencode('Please enter a valid 6-digit code.')); exit();
}

// Look up user
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND role = 'client' LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user) {
    // Shouldn't happen, but guard anyway
    unset($_SESSION['reset_email']);
    header("Location: /storehub/client/forgot_password.php?error=" . urlencode('Session expired. Please start again.')); exit();
}

// Validate code: must match, not expired, not used
$now  = date('Y-m-d H:i:s');
$chk  = mysqli_prepare($conn,
    "SELECT id FROM password_resets
     WHERE user_id = ? AND code = ? AND expires_at > ? AND used = 0
     LIMIT 1"
);
mysqli_stmt_bind_param($chk, 'iss', $user['id'], $code, $now);
mysqli_stmt_execute($chk);
$reset = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));

if (!$reset) {
    header("Location: /storehub/client/verify_code.php?error=" . urlencode('Invalid or expired code. Please try again or request a new code.')); exit();
}

// Code is valid — store reset ID in session for the next step
$_SESSION['reset_id'] = $reset['id'];

// Redirect to set new password
header("Location: /storehub/client/reset_password.php");
exit();
