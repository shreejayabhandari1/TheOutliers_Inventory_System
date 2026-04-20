<?php
require_once __DIR__ . '/../../includes/session.php';
requireClient();

$productId = intval($_POST['product_id'] ?? 0);
$quantity  = intval($_POST['quantity'] ?? 1);

if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
    if ($quantity < 1) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $maxStock = $_SESSION['cart'][$productId]['max_stock'];
        $_SESSION['cart'][$productId]['quantity'] = min($quantity, $maxStock);
    }
}

header("Location: /storehub/client/cart.php");
exit();
?>
