<?php

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireManager();

$id             = intval($_POST['id'] ?? 0);
$name           = trim($_POST['name'] ?? '');
$contact_person = trim($_POST['contact_person'] ?? '');
$email          = trim($_POST['email'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$address        = trim($_POST['address'] ?? '');
$status         = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

if (empty($name)) {
    header("Location: /storehub/manager/suppliers.php?error=Supplier+name+is+required");
    exit();
}

if ($id === 0) {
    $stmt = mysqli_prepare($conn, "INSERT INTO suppliers (name, contact_person, email, phone, address, status) VALUES (?,?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "ssssss", $name, $contact_person, $email, $phone, $address, $status);
    $msg = 'Supplier+added+successfully';
} else {
    $stmt = mysqli_prepare($conn, "UPDATE suppliers SET name=?, contact_person=?, email=?, phone=?, address=?, status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssssi", $name, $contact_person, $email, $phone, $address, $status, $id);
    $msg = 'Supplier+updated+successfully';
}

if (mysqli_stmt_execute($stmt)) {
    header("Location: /storehub/manager/suppliers.php?success=$msg");
} else {
    header("Location: /storehub/manager/suppliers.php?error=Failed+to+save+supplier");
}
exit();
?>
