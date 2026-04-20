<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$id     = intval($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$valid  = ['pending','processing','completed','cancelled'];

if ($id === 0 || !in_array($status, $valid)) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE orders SET status=? WHERE id=?");
mysqli_stmt_bind_param($stmt, "si", $status, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'DB error']);
}
exit();
?>
