<?php

require_once __DIR__ . '/../../includes/session.php';

$role = $_SESSION['role'] ?? 'client';

session_unset();
session_destroy();

if ($role === 'manager') {
    header("Location: /storehub/index.php");
} else {
    header("Location: /storehub/client/login.php");
}
exit();
?>
