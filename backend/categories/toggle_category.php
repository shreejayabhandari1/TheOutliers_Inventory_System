<?php

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireManager();

$id     = intval($_GET['id'] ?? 0);
$action = $_GET['action'] === 'activate' ? 'active' : 'inactive';

if ($id === 0) {
    header("Location: /storehub/manager/categories.php?error=Invalid+category");
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE categories SET status=? WHERE id=?");
mysqli_stmt_bind_param($stmt, "si", $action, $id);

if (mysqli_stmt_execute($stmt)) {
    $msg = $action === 'active' ? 'Category+reactivated' : 'Category+deactivated';
    header("Location: /storehub/manager/categories.php?success=$msg");
} else {
    header("Location: /storehub/manager/categories.php?error=Failed+to+update");
}
exit();
?>
