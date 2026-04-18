<?php

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireManager();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/manager/stock_adjustment.php");
    exit();
}

$product_id = intval($_POST['product_id'] ?? 0);
$adj_type   = $_POST['adj_type'] === 'remove' ? 'remove' : 'add';
$quantity   = intval($_POST['quantity'] ?? 0);
$reason     = trim($_POST['reason'] ?? '');
$notes      = trim($_POST['notes'] ?? '');

$valid_reasons = ['new_stock_received','damaged_goods','lost_missing','supplier_delivery','return_from_client','other'];

if ($product_id === 0 || $quantity < 1 || !in_array($reason, $valid_reasons)) {
    header("Location: /storehub/manager/stock_adjustment.php?error=Invalid+input");
    exit();
}

$pstmt = mysqli_prepare($conn, "SELECT id, stock FROM products WHERE id = ? AND status = 'active'");
mysqli_stmt_bind_param($pstmt, "i", $product_id);
mysqli_stmt_execute($pstmt);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($pstmt));

if (!$product) {
    header("Location: /storehub/manager/stock_adjustment.php?error=Product+not+found");
    exit();
}

$stock_before  = intval($product['stock']);
$change_amount = $adj_type === 'add' ? $quantity : -$quantity;
$stock_after   = $stock_before + $change_amount;

if ($stock_after < 0) {
    header("Location: /storehub/manager/stock_adjustment.php?product_id=$product_id&error=Cannot+remove+more+than+current+stock+($stock_before+units)");
    exit();
}

$manager_id = $_SESSION['user_id'];

mysqli_begin_transaction($conn);

try {

    $updateStmt = mysqli_prepare($conn, "UPDATE products SET stock = ? WHERE id = ?");
    mysqli_stmt_bind_param($updateStmt, "ii", $stock_after, $product_id);
    mysqli_stmt_execute($updateStmt);

    $logStmt = mysqli_prepare($conn, "
        INSERT INTO stock_adjustments (product_id, adjusted_by, stock_before, change_amount, stock_after, reason, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($logStmt, "iiiiiss",
        $product_id, $manager_id,
        $stock_before, $change_amount, $stock_after,
        $reason, $notes
    );
    mysqli_stmt_execute($logStmt);

    mysqli_commit($conn);
    header("Location: /storehub/manager/stock_adjustment.php?success=Stock+adjusted+successfully");

} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: /storehub/manager/stock_adjustment.php?error=Failed+to+save+adjustment");
}
exit();
?>
