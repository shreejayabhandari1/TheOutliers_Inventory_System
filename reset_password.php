<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/client/forgot_password.php");
    exit();
}

$newPass = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

$userId = $_SESSION['reset_user_id'] ?? 0;
$email  = $_SESSION['reset_email'] ?? '';
$validUntil = $_SESSION['reset_verified_until'] ?? 0;

if (!$userId || !$email || time() > $validUntil) {
    header("Location: /storehub/client/forgot_password.php?error=" . urlencode('Reset session expired. Please request a new OTP.'));
    exit();
}

if (strlen($newPass) < 8) {
    header("Location: /storehub/client/forgot_password.php?step=reset&email=" . urlencode($email) . "&error=" . urlencode('Password must be at least 8 characters.'));
    exit();
}

if ($newPass !== $confirm) {
    header("Location: /storehub/client/forgot_password.php?step=reset&email=" . urlencode($email) . "&error=" . urlencode('Passwords do not match.'));
    exit();
}

$hash = password_hash($newPass, PASSWORD_DEFAULT);

$upd = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ? AND role = 'client'");
mysqli_stmt_bind_param($upd, "si", $hash, $userId);
mysqli_stmt_execute($upd);

$mark = mysqli_prepare($conn, "UPDATE password_resets SET used = 1 WHERE user_id = ?");
mysqli_stmt_bind_param($mark, "i", $userId);
mysqli_stmt_execute($mark);

// Clear reset session
unset($_SESSION['reset_user_id'], $_SESSION['reset_email'], $_SESSION['reset_verified_until']);

header("Location: /storehub/client/login.php?success=" . urlencode('Password reset successfully. Please login with your new password.'));
exit();
?>
