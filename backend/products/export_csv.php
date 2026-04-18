<?php

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireManager();


header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="products_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['ID','Name','Brand','Model','SKU','Category','Supplier','Cost Price','Selling Price','Stock','Reorder Level','Status']);

$result = mysqli_query($conn, "
    SELECT p.id, p.name, p.brand, p.model_number, p.sku,
           c.name as cat_name, s.name as sup_name,
           p.cost_price, p.selling_price, p.stock, p.reorder_level, p.status
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN suppliers s  ON p.supplier_id  = s.id
    ORDER BY p.name
");

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['id'], $row['name'], $row['brand'], $row['model_number'],
        $row['sku'], $row['cat_name'], $row['sup_name'],
        $row['cost_price'], $row['selling_price'],
        $row['stock'], $row['reorder_level'], $row['status']
    ]);
}

fclose($output);
exit();
?>
