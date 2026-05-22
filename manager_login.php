<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/index.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';


if (empty($email) || empty($password)) {
    header("Location: /storehub/index.php?error=Please+fill+in+all+fields");
    exit();
}

$sql  = "SELECT id, name, email, password, role FROM users WHERE email = ? AND role = 'manager' LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

if (!$user || !password_verify($password, $user['password'])) {
    header("Location: /storehub/index.php?error=Invalid+email+or+password");
    exit();
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['name']    = $user['name'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'];

header("Location: /storehub/manager/dashboard.php");
exit();
?>
