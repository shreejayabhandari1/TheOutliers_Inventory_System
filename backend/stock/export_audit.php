<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireManager();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="audit_log_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['#','Date/Time','Product','SKU','Before','Change','After','Reason','Notes','Logged By']);

$result = mysqli_query($conn, "
    SELECT sa.*, p.name as product_name, p.sku, u.name as manager_name
    FROM stock_adjustments sa
    JOIN products p ON sa.product_id = p.id
    JOIN users u ON sa.adjusted_by = u.id
    ORDER BY sa.created_at DESC
");

$i = 1;
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        str_pad($i++, 3, '0', STR_PAD_LEFT),
        date('Y-m-d H:i', strtotime($row['created_at'])),
        $row['product_name'], $row['sku'],
        $row['stock_before'], $row['change_amount'], $row['stock_after'],
        ucwords(str_replace('_', ' ', $row['reason'])),
        $row['notes'], $row['manager_name']
    ]);
}
fclose($output);
exit();
?>
