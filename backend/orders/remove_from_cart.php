<?php
require_once __DIR__ . '/../../includes/session.php';
requireClient();

$productId = intval($_GET['id'] ?? 0);

if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
    unset($_SESSION['cart'][$productId]);
}

header("Location: /storehub/client/cart.php");
exit();
?>
