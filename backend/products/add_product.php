<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireManager();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /storehub/manager/products.php"); exit();
}

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

if (empty($name) || empty($sku) || $category_id === 0 || $selling_price <= 0) {
    header("Location: /storehub/manager/products.php?error=Please+fill+in+all+required+fields"); exit();
}

$check = mysqli_prepare($conn, "SELECT id FROM products WHERE sku = ?");
mysqli_stmt_bind_param($check, "s", $sku);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);
if (mysqli_stmt_num_rows($check) > 0) {
    header("Location: /storehub/manager/products.php?error=SKU+already+exists"); exit();
}

if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../../assets/images/products/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','webp'])) {
        $filename = 'prod_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadDir . $filename)) {
            $image_url = '/storehub/assets/images/products/' . $filename;
        }
    }
}

$sql = "INSERT INTO products (name,brand,model_number,sku,category_id,supplier_id,cost_price,selling_price,stock,reorder_level,reorder_quantity,warranty_period,status,description,image_url)
        VALUES (?,?,?,?,?,?,?,?,0,?,?,?,?,?,?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssiiddiiisss",
    $name,$brand,$model_number,$sku,$category_id,$supplier_id,
    $cost_price,$selling_price,$reorder_level,$reorder_quantity,
    $warranty_period,$status,$description,$image_url
);
if (mysqli_stmt_execute($stmt)) {
    header("Location: /storehub/manager/products.php?success=Product+added+successfully");
} else {
    header("Location: /storehub/manager/products.php?error=Failed+to+add+product");
}
exit();
