<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireClient();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/client/cart.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: /storehub/client/cart.php?error=Your+cart+is+empty");
    exit();
}

$cart     = $_SESSION['cart'];
$clientId = $_SESSION['user_id'];
$notes    = trim($_POST['notes'] ?? '');
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}
mysqli_begin_transaction($conn);

try {
    $ostmt = mysqli_prepare($conn, "INSERT INTO orders (client_id, total_amount, status, notes) VALUES (?, ?, 'pending', ?)");
    mysqli_stmt_bind_param($ostmt, "ids", $clientId, $total, $notes);
    mysqli_stmt_execute($ostmt);
    $orderId = mysqli_insert_id($conn);
    foreach ($cart as $productId => $item) {
        $checkStmt = mysqli_prepare($conn, "SELECT stock FROM products WHERE id = ? FOR UPDATE");
        mysqli_stmt_bind_param($checkStmt, "i", $productId);
        mysqli_stmt_execute($checkStmt);
        $productRow = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));

        if (!$productRow || $productRow['stock'] < $item['quantity']) {
            throw new Exception("Not enough stock for: " . $item['name']);
        }
        $itemStmt = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($itemStmt, "iiid", $orderId, $productId, $item['quantity'], $item['price']);
        mysqli_stmt_execute($itemStmt);

        $stockStmt = mysqli_prepare($conn, "UPDATE products SET stock = stock - ? WHERE id = ?");
        mysqli_stmt_bind_param($stockStmt, "ii", $item['quantity'], $productId);
        mysqli_stmt_execute($stockStmt);
        
        $reason = 'other';
        $newStock = $productRow['stock'] - $item['quantity'];
        $change   = -$item['quantity'];
        $notes2   = "Order #" . str_pad($orderId, 4, '0', STR_PAD_LEFT) . " placed by client";
        $logStmt  = mysqli_prepare($conn, "
            INSERT INTO stock_adjustments (product_id, adjusted_by, stock_before, change_amount, stock_after, reason, notes)
            VALUES (?, ?, ?, ?, ?, 'other', ?)
        ");
        mysqli_stmt_bind_param($logStmt, "iiiiis",
            $productId, $clientId,
            $productRow['stock'], $change, $newStock,
            $notes2
        );
        mysqli_stmt_execute($logStmt);
    }

    mysqli_commit($conn);
    $_SESSION['cart'] = [];

    header("Location: /storehub/client/orders.php?success=Order+placed+successfully!+Order+%23" . str_pad($orderId, 4, '0', STR_PAD_LEFT));

} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: /storehub/client/cart.php?error=" . urlencode($e->getMessage()));
}
exit();
?>