<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/client/forgot_password.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$otp   = trim($_POST['otp'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9]{6}$/', $otp)) {
    header("Location: /storehub/client/forgot_password.php?step=verify&email=" . urlencode($email) . "&error=" . urlencode('Please enter the valid 6 digit OTP.'));
    exit();
}

$now = date('Y-m-d H:i:s');

$stmt = mysqli_prepare($conn, "
    SELECT pr.id, pr.user_id, pr.otp_code, u.email
    FROM password_resets pr
    JOIN users u ON u.id = pr.user_id
    WHERE u.email = ? AND u.role = 'client' AND pr.used = 0 AND pr.expires_at > ?
    ORDER BY pr.id DESC
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ss", $email, $now);
mysqli_stmt_execute($stmt);
$reset = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$reset || !password_verify($otp, $reset['otp_code'])) {
    header("Location: /storehub/client/forgot_password.php?step=verify&email=" . urlencode($email) . "&error=" . urlencode('Invalid or expired OTP.'));
    exit();
}

// Store temporary verification in session
$_SESSION['reset_user_id'] = (int)$reset['user_id'];
$_SESSION['reset_email'] = $email;
$_SESSION['reset_verified_until'] = time() + (10 * 60);

header("Location: /storehub/client/forgot_password.php?step=reset&email=" . urlencode($email) . "&success=" . urlencode('OTP verified. Now set your new password.'));
exit();
?>
