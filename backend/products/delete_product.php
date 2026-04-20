<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireManager();

$id = intval($_GET['id'] ?? 0);

if ($id === 0) {
    header("Location: /storehub/manager/products.php?error=Invalid+product");
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE products SET status='inactive' WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: /storehub/manager/products.php?success=Product+deactivated");
} else {
    header("Location: /storehub/manager/products.php?error=Failed+to+deactivate+product");
}
exit();
?>
