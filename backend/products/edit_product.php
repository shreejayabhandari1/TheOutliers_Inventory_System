<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireManager();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/manager/products.php"); exit();
}

$id               = intval($_POST['id'] ?? 0);
$name             = trim($_POST['name'] ?? '');
$sku              = trim($_POST['sku'] ?? '');
$brand            = trim($_POST['brand'] ?? '');
$model_number     = trim($_POST['model_number'] ?? '');
$category_id      = intval($_POST['category_id'] ?? 0);
$supplier_id      = intval($_POST['supplier_id'] ?? 0) ?: null;
$cost_price       = floatval($_POST['cost_price'] ?? 0);
$selling_price    = floatval($_POST['selling_price'] ?? 0);
$reorder_level    = intval($_POST['reorder_level'] ?? 5);
$reorder_quantity = intval($_POST['reorder_quantity'] ?? 10);
$warranty_period  = trim($_POST['warranty_period'] ?? '');
$status           = ($_POST['status'] ?? '') === 'inactive' ? 'inactive' : 'active';
$description      = trim($_POST['description'] ?? '');
$image_url        = trim($_POST['image_url'] ?? '') ?: null;

if ($id === 0 || empty($name) || empty($sku)) {
    header("Location: /storehub/manager/products.php?error=Invalid+data"); exit();
}

$check = mysqli_prepare($conn, "SELECT id FROM products WHERE sku = ? AND id != ?");
mysqli_stmt_bind_param($check, "si", $sku, $id);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);
if (mysqli_stmt_num_rows($check) > 0) {
    header("Location: /storehub/manager/products.php?error=SKU+already+used+by+another+product"); exit();
}
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../../assets/images/products/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','webp'])) {
        $filename = 'prod_' . $id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadDir . $filename)) {
            $image_url = '/storehub/assets/images/products/' . $filename;
        }
    }
}

$sql = "UPDATE products SET
    name=?, brand=?, model_number=?, sku=?,
    category_id=?, supplier_id=?,
    cost_price=?, selling_price=?,
    reorder_level=?, reorder_quantity=?,
    warranty_period=?, status=?, description=?, image_url=?
    WHERE id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssiiddiiisssi",
    $name,$brand,$model_number,$sku,
    $category_id,$supplier_id,
    $cost_price,$selling_price,
    $reorder_level,$reorder_quantity,
    $warranty_period,$status,$description,$image_url,
    $id
);
if (mysqli_stmt_execute($stmt)) {
    header("Location: /storehub/manager/products.php?success=Product+updated+successfully");
} else {
    header("Location: /storehub/manager/products.php?error=" . urlencode('Update failed: ' . mysqli_error($conn)));
}
exit();
