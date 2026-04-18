<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireManager() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
        header("Location: /storehub/index.php");
        exit();
    }
}

function requireClient() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
        header("Location: /storehub/client/login.php");
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserName() {
    return isset($_SESSION['name']) ? $_SESSION['name'] : '';
}

function getUserInitials() {
    $name = getUserName();
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return substr($initials, 0, 2);
}
?>
