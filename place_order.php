<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/notifications.php';
requireClient();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/client/cart.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: /storehub/client/cart.php?error=Your+cart+is+empty");
    exit();
}

$fullCart  = $_SESSION['cart'];
$clientId  = $_SESSION['user_id'];
$notes     = trim($_POST['notes'] ?? '');

// Filter to only selected items (if provided)
$selectedRaw = trim($_POST['selected_items'] ?? '');
if ($selectedRaw !== '') {
    $selectedIds = array_filter(array_map('intval', explode(',', $selectedRaw)));
    $cart = array_intersect_key($fullCart, array_flip($selectedIds));
} else {
    $cart = $fullCart;
}

if (empty($cart)) {
    header("Location: /storehub/client/cart.php?error=No+items+selected+for+order.");
    exit();
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Ensure InnoDB transaction support
mysqli_autocommit($conn, false);

try {
    // Insert order (Cash on Delivery)
    $ostmt = mysqli_prepare($conn, "INSERT INTO orders (client_id, total_amount, status, payment_method, notes) VALUES (?, ?, 'processing', 'cod', ?)");
    mysqli_stmt_bind_param($ostmt, "ids", $clientId, $total, $notes);
    if (!mysqli_stmt_execute($ostmt)) throw new Exception("Failed to create order.");
    $orderId = mysqli_insert_id($conn);

    foreach ($cart as $productId => $item) {
        $productId = intval($productId);
        $qty       = intval($item['quantity']);

        // Lock the row for update
        $checkStmt = mysqli_prepare($conn, "SELECT stock, name FROM products WHERE id = ? AND status = 'active' FOR UPDATE");
        mysqli_stmt_bind_param($checkStmt, "i", $productId);
        mysqli_stmt_execute($checkStmt);
        $productRow = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));

        if (!$productRow) {
            throw new Exception("Product no longer available: " . htmlspecialchars($item['name']));
        }
        if ($productRow['stock'] < $qty) {
            throw new Exception("Not enough stock for: " . htmlspecialchars($productRow['name']) .
                " (available: {$productRow['stock']}, requested: $qty)");
        }

        // Insert order item
        $itemStmt = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($itemStmt, "iiid", $orderId, $productId, $qty, $item['price']);
        if (!mysqli_stmt_execute($itemStmt)) throw new Exception("Failed to save order items.");

        // Deduct stock
        $stockStmt = mysqli_prepare($conn, "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        mysqli_stmt_bind_param($stockStmt, "iii", $qty, $productId, $qty);
        mysqli_stmt_execute($stockStmt);

        if (mysqli_affected_rows($conn) === 0) {
            throw new Exception("Stock update failed for: " . htmlspecialchars($productRow['name']) . " — possibly sold out during checkout.");
        }

        // Audit log
        $newStock = $productRow['stock'] - $qty;
        $change   = -$qty;
        $logNote  = "Order #" . str_pad($orderId, 4, '0', STR_PAD_LEFT) . " placed by client (COD)";
        $logStmt  = mysqli_prepare($conn,
            "INSERT INTO stock_adjustments (product_id, adjusted_by, stock_before, change_amount, stock_after, reason, notes)
             VALUES (?, ?, ?, ?, ?, 'other', ?)"
        );
        mysqli_stmt_bind_param($logStmt, "iiiiis",
            $productId, $clientId,
            $productRow['stock'], $change, $newStock, $logNote
        );
        mysqli_stmt_execute($logStmt);
        // Note: audit log failure is non-fatal — don't throw here
    }

    mysqli_commit($conn);
    mysqli_autocommit($conn, true);

    // ── Push manager notifications ─────────────────────
    // 1. New order placed
    $clientName = $_SESSION['name'] ?? 'A client';
    pushNotification($conn, 'new_order',
        '🛒 New Order #' . str_pad($orderId, 4, '0', STR_PAD_LEFT),
        "$clientName placed a new order for Rs. " . number_format($total, 2) . ".",
        $orderId
    );
    // 2. Check if any item's stock is now low or out
    foreach ($cart as $productId => $item) {
        $productId = intval($productId);
        $chk = mysqli_prepare($conn, "SELECT stock, reorder_level, name FROM products WHERE id = ?");
        mysqli_stmt_bind_param($chk, "i", $productId);
        mysqli_stmt_execute($chk);
        $chkRow = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
        if ($chkRow) {
            checkStockNotification($conn, $productId, $chkRow['stock'], $chkRow['reorder_level'], $chkRow['name']);
        }
    }

    // Remove only ordered items from cart
    foreach ($cart as $pid => $item) { unset($_SESSION['cart'][$pid]); }

    header("Location: /storehub/client/orders.php?success=Order+placed+successfully!+Order+%23" . str_pad($orderId, 4, '0', STR_PAD_LEFT));

} catch (Exception $e) {
    mysqli_rollback($conn);
    mysqli_autocommit($conn, true);
    header("Location: /storehub/client/cart.php?error=" . urlencode($e->getMessage()));
}
exit();
