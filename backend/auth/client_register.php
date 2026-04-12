<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/client/register.php");
    exit();
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

if (empty($name) || empty($email) || empty($password)) {
    header("Location: /storehub/client/register.php?error=All+fields+are+required");
    exit();
}
if (strlen($password) < 6) {
    header("Location: /storehub/client/register.php?error=Password+must+be+at+least+6+characters");
    exit();
}
if ($password !== $confirm) {
    header("Location: /storehub/client/register.php?error=Passwords+do+not+match");
    exit();
}

$check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($check, "s", $email);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);
if (mysqli_stmt_num_rows($check) > 0) {
    header("Location: /storehub/client/register.php?error=Email+already+registered");
    exit();
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'client')");
mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed);

if (mysqli_stmt_execute($stmt)) {

    $_SESSION['user_id'] = mysqli_insert_id($conn);
    $_SESSION['name']    = $name;
    $_SESSION['email']   = $email;
    $_SESSION['role']    = 'client';
    header("Location: /storehub/client/products.php");
} else {
    header("Location: /storehub/client/register.php?error=Registration+failed.+Please+try+again.");
}
exit();
?>
