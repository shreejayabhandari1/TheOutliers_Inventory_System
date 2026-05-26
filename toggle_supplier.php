<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireManager();

$id     = intval($_GET['id'] ?? 0);
$status = $_GET['action'] === 'activate' ? 'active' : 'inactive';

if ($id === 0) {
    header("Location: /storehub/manager/suppliers.php?error=Invalid");
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE suppliers SET status=? WHERE id=?");
mysqli_stmt_bind_param($stmt, "si", $status, $id);
mysqli_stmt_execute($stmt);

$msg = $status === 'active' ? 'Supplier+reactivated' : 'Supplier+deactivated';
header("Location: /storehub/manager/suppliers.php?success=$msg");
exit();
?>
