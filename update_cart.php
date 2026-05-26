<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireClient();

$productId = intval($_POST['product_id'] ?? 0);
$quantity  = intval($_POST['quantity'] ?? 1);

if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
    if ($quantity < 1) {
        unset($_SESSION['cart'][$productId]);
        header("Location: /storehub/client/cart.php?success=Item+removed+from+cart");
        exit();
    }

    // Always re-check LIVE stock from DB (not stale session value)
    $stmt = mysqli_prepare($conn, "SELECT stock FROM products WHERE id = ? AND status = 'active'");
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$row) {
        header("Location: /storehub/client/cart.php?error=Product+no+longer+available");
        exit();
    }

    $liveStock = intval($row['stock']);

    // Update the max_stock in session with the current live value
    $_SESSION['cart'][$productId]['max_stock'] = $liveStock;

    if ($quantity > $liveStock) {
        header("Location: /storehub/client/cart.php?error=" . urlencode("Only $liveStock piece(s) available for {$_SESSION['cart'][$productId]['name']}. Please enter $liveStock or less."));
        exit();
    }

    $_SESSION['cart'][$productId]['quantity'] = $quantity;
    $_SESSION['cart'][$productId]['max_stock'] = $liveStock;
    header("Location: /storehub/client/cart.php?success=Quantity+updated+to+" . $quantity);
    exit();
}

header("Location: /storehub/client/cart.php");
exit();
?>
