<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/client/login.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header("Location: /storehub/client/login.php?error=Please+fill+in+all+fields");
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role FROM users WHERE email = ? AND role = 'client' LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user || !password_verify($password, $user['password'])) {
    header("Location: /storehub/client/login.php?error=Invalid+email+or+password");
    exit();
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['name']    = $user['name'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'];

header("Location: /storehub/client/products.php");
exit();
?>
