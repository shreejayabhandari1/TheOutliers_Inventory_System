<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/client/forgot_password.php");
    exit();
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: /storehub/client/forgot_password.php?error=" . urlencode('Please enter a valid email address.'));
    exit();
}

// Make sure OTP table exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_code VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

// Check only client account
$stmt = mysqli_prepare($conn, "SELECT id, name, email FROM users WHERE email = ? AND role = 'client' LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user) {
    header("Location: /storehub/client/forgot_password.php?error=" . urlencode('No client account found with this email.'));
    exit();
}

// Delete previous unused OTPs for this user
$del = mysqli_prepare($conn, "DELETE FROM password_resets WHERE user_id = ?");
mysqli_stmt_bind_param($del, "i", $user['id']);
mysqli_stmt_execute($del);

// Generate 6 digit OTP
$otp = (string) random_int(100000, 999999);
$otpHash = password_hash($otp, PASSWORD_DEFAULT);
$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Save OTP hash
$ins = mysqli_prepare($conn, "INSERT INTO password_resets (user_id, otp_code, expires_at, used) VALUES (?, ?, ?, 0)");
mysqli_stmt_bind_param($ins, "iss", $user['id'], $otpHash, $expires);
mysqli_stmt_execute($ins);

try {
    sendPasswordOtpEmail($user['email'], $user['name'], $otp);
} catch (Exception $e) {
    // Helpful local testing log
    $logDir = __DIR__ . '/../../logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logDir . 'mail_errors.log', date('Y-m-d H:i:s') . ' | ' . $e->getMessage() . PHP_EOL, FILE_APPEND);

    header("Location: /storehub/client/forgot_password.php?error=" . urlencode('OTP could not be sent. Check Mailtrap details, PHPMailer install, and logs/mail_errors.log.'));
    exit();
}

header("Location: /storehub/client/forgot_password.php?step=verify&email=" . urlencode($user['email']) . "&success=" . urlencode('OTP sent successfully. Check your Mailtrap inbox.'));
exit();
?>
