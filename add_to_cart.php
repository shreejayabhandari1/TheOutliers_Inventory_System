<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireClient();

$productId = intval($_POST['product_id'] ?? 0);
$quantity  = max(1, intval($_POST['quantity'] ?? 1));

if ($productId === 0) {
    header("Location: /storehub/client/products.php?error=Invalid+product");
    exit();
}
$stmt = mysqli_prepare($conn, "SELECT id, name, selling_price, stock, status FROM products WHERE id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$product) {
    header("Location: /storehub/client/products.php?error=Product+not+found");
    exit();
}

if ($product['stock'] == 0) {
    header("Location: /storehub/client/products.php?error=Product+is+out+of+stock");
    exit();
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (isset($_SESSION['cart'][$productId])) {
    $newQty = $_SESSION['cart'][$productId]['quantity'] + $quantity;
    if ($newQty > intval($product['stock'])) {
        header("Location: /storehub/client/cart.php?error=" . urlencode("Only {$product['stock']} piece(s) available for {$product['name']}. You already have {$_SESSION['cart'][$productId]['quantity']} in cart."));
        exit();
    }
    $_SESSION['cart'][$productId]['quantity'] = $newQty;
    $_SESSION['cart'][$productId]['max_stock'] = intval($product['stock']);
} else {
    if ($quantity > intval($product['stock'])) {
        header("Location: /storehub/client/products.php?error=" . urlencode("Only {$product['stock']} piece(s) available for {$product['name']}. Please choose a smaller quantity."));
        exit();
    }
    $_SESSION['cart'][$productId] = [
        'product_id' => $productId,
        'name'       => $product['name'],
        'price'      => $product['selling_price'],
        'quantity'   => $quantity,
        'max_stock'  => intval($product['stock']),
    ];
}

header("Location: /storehub/client/cart.php?success=Item+added+to+cart");
exit();
?>
