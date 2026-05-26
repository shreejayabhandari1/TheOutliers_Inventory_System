<?php
require_once __DIR__ . '/../../includes/session.php';
requireClient();

$_SESSION['cart'] = [];
header("Location: /storehub/client/cart.php");
exit();
?>
