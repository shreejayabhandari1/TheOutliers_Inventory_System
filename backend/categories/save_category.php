<?php

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireManager();

$id          = intval($_POST['id'] ?? 0);
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$status      = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

if (empty($name)) {
    header("Location: /storehub/manager/categories.php?error=Category+name+is+required");
    exit();
}

if ($id === 0) {

    $stmt = mysqli_prepare($conn, "INSERT INTO categories (name, description, status) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $name, $description, $status);
    $msg = 'Category+added+successfully';
} else {

    $stmt = mysqli_prepare($conn, "UPDATE categories SET name=?, description=?, status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssi", $name, $description, $status, $id);
    $msg = 'Category+updated+successfully';
}

if (mysqli_stmt_execute($stmt)) {
    header("Location: /storehub/manager/categories.php?success=$msg");
} else {
    header("Location: /storehub/manager/categories.php?error=Failed+to+save+category");
}
exit();
?>
