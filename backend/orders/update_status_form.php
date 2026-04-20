<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireManager();

$id     = intval($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$valid  = ['pending','processing','completed','cancelled'];

if ($id === 0 || !in_array($status, $valid)) {
    header("Location: /storehub/manager/orders.php?error=Invalid+data");
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE orders SET status=? WHERE id=?");
mysqli_stmt_bind_param($stmt, "si", $status, $id);
mysqli_stmt_execute($stmt);

header("Location: /storehub/manager/order_detail.php?id=$id&success=Status+updated");
exit();
?>
