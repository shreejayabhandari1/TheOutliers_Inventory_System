<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireClient();

$search = trim($_GET['q'] ?? '');
$catId  = intval($_GET['cat'] ?? 0);

$where  = "WHERE p.status = 'active'";
$params = [];
$types  = "";

if ($search !== '') {
    $where .= " AND (p.name LIKE ? OR p.brand LIKE ? OR p.model_number LIKE ? OR p.sku LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "ssss";
}

if ($catId > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $catId;
    $types .= "i";
}

$sql  = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.name";
$stmt = mysqli_prepare($conn, $sql);
if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$products = mysqli_stmt_get_result($stmt);

// Output just the cards HTML
include __DIR__ . '/../includes/product_cards.php';
?>
