<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireClient();

$clientId = $_SESSION['user_id'];
$orderId  = intval($_GET['id'] ?? 0);

if ($orderId === 0) {
    header("Location: /storehub/client/orders.php");
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND client_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $orderId, $clientId);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    header("Location: /storehub/client/orders.php?error=Order+not+found");
    exit();
}

$items = mysqli_query($conn, "
    SELECT oi.*, p.name as product_name, p.sku, p.brand, c.name as cat_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE oi.order_id = $orderId
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= str_pad($orderId, 4, '0', STR_PAD_LEFT) ?> – StoreHub</title><style>
        body { background: var(--cream-bg); }
        .client-wrap { max-width: 800px; margin: 0 auto; padding: 28px; }
    </style>
    <style>

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

:root {
    --brown-dark: #6F4532;
    --brown-main: #6F4532;
    --brown-light: #BD7559;
    --brown-accent: #BD7559;

    --cream-bg: #F8EDEB;
    --cream-light: #F8EDEB;
    --white: #FFFFFF;

    --text-dark: #2D1A10;
    --text-mid: #6F4532;
    --text-light: #BD7559;

    --border: #F3D1CB;

    --green: #2D7A4F;
    --green-bg: #E8F5EE;

    --red: #C0392B;
    --red-bg: #FDECEA;

    --orange: #D4750A;
    --orange-bg: #FEF3E2;

    --shadow: 0 2px 8px rgba(90, 40, 15, .10);
    --shadow-md: 0 4px 16px rgba(90, 40, 15, .14);

    --radius: 8px;
    --radius-lg: 12px;

    --sidebar-w: 220px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--cream-bg);
    color: var(--text-dark);
    font-size: 14px;
    line-height: 1.5;
}

a {
    text-decoration: none;
    color: inherit;
}

.text-center { text-align: center; }
.mt-4 { margin-top: 16px; }
.mb-4 { margin-bottom: 16px; }
.text-sm { font-size: 13px; }
.text-muted { color: var(--text-light); }
.font-bold { font-weight: 700; }
.hidden { display: none; }

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    padding: 10px 20px;

    border-radius: var(--radius);
    border: none;

    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 500;

    cursor: pointer;
    transition: all .2s ease;
}

.btn-primary {
    background: var(--brown-accent);
    color: #fff;
}

.btn-primary:hover {
    background: var(--brown-main);
}

.btn-secondary {
    background: var(--cream-bg);
    color: var(--text-dark);
    border: 1.5px solid var(--border);
}

.btn-secondary:hover {
    background: var(--border);
}

.btn-danger {
    background: var(--red-bg);
    color: var(--red);
    border: 1.5px solid #F5C6C0;
}

.btn-danger:hover {
    background: var(--red);
    color: #fff;
}

.btn-full {
    width: 100%;
    padding: 12px;
}

.btn-sm {
    padding: 6px 14px;
    font-size: 13px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;

    font-size: 11px;
    font-weight: 600;

    letter-spacing: .06em;
    text-transform: uppercase;

    color: var(--text-mid);
    margin-bottom: 6px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 14px;

    border: 1.5px solid var(--border);
    border-radius: var(--radius);

    font-family: 'Inter', sans-serif;
    font-size: 14px;

    color: var(--text-dark);
    background: #fff;

    outline: none;
    transition: border-color .2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--brown-accent);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: var(--text-light);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-check-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.form-check-row label {
    display: flex;
    align-items: center;
    gap: 8px;

    font-size: 13px;
    color: var(--text-mid);

    cursor: pointer;
}

.form-check-row a {
    font-size: 13px;
    color: var(--brown-accent);
}

.filter-bar {
    display: flex;
    align-items: center;
    gap: 12px;

    margin-bottom: 18px;
    flex-wrap: wrap;
}

.search-input-wrap {
    position: relative;
    flex: 1;

    min-width: 200px;
    max-width: 320px;
}

.search-input-wrap input {
    width: 100%;
    padding: 9px 14px 9px 36px;

    border: 1.5px solid var(--border);
    border-radius: var(--radius);

    font-size: 13.5px;
    font-family: 'Inter', sans-serif;

    background: #fff;
    color: var(--text-dark);

    outline: none;
}

.search-input-wrap input:focus {
    border-color: var(--brown-accent);
}

.search-icon {
    position: absolute;
    left: 11px;
    top: 50%;

    transform: translateY(-50%);

    font-size: 15px;
    color: var(--text-light);
}

.filter-select {
    padding: 9px 32px 9px 12px;

    border: 1.5px solid var(--border);
    border-radius: var(--radius);

    font-size: 13.5px;
    font-family: 'Inter', sans-serif;

    background: #fff;
    color: var(--text-dark);

    cursor: pointer;
    appearance: none;

    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%23A08060' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");

    background-repeat: no-repeat;
    background-position: right 10px center;
}

.alert {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 12px 16px;
    margin-bottom: 16px;

    border-radius: var(--radius);
    font-size: 13.5px;
}

.alert-success {
    background: var(--green-bg);
    color: var(--green);
    border: 1px solid #B8DFC9;
}

.alert-error {
    background: var(--red-bg);
    color: var(--red);
    border: 1px solid #F5C6C0;
}

.alert-warning {
    background: var(--orange-bg);
    color: var(--orange);
    border: 1px solid #F5D99A;
}

.client-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 28px;
}

.cat-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 22px;
}

.cat-pill {
    padding: 7px 18px;

    border-radius: 20px;
    border: 1.5px solid var(--border);

    background: #fff;
    color: var(--text-mid);

    font-size: 13px;
    font-weight: 500;

    cursor: pointer;
    transition: all .18s ease;
}

.cat-pill:hover {
    border-color: var(--brown-accent);
    color: var(--brown-accent);
}

.cat-pill.active {
    background: var(--brown-accent);
    border-color: var(--brown-accent);
    color: #fff;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 18px;
}

.product-card {
    background: #fff;

    border-radius: var(--radius-lg);
    border: 1px solid var(--border);

    overflow: hidden;
    transition: box-shadow .2s ease, transform .2s ease;
}

.product-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.product-card-body {
    padding: 14px 16px;
}

.product-card-brand {
    font-size: 11px;
    color: var(--text-light);

    text-transform: uppercase;
    letter-spacing: .06em;

    margin-bottom: 4px;
}

.product-card-name {
    font-size: 14px;
    font-weight: 600;

    color: var(--text-dark);
    margin-bottom: 4px;
}

.product-card-price {
    font-size: 18px;
    font-weight: 700;

    color: var(--brown-accent);
    margin-bottom: 10px;
}

.cart-summary {
    background: #fff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);

    padding: 20px;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-total {
    display: flex;
    justify-content: space-between;

    font-size: 16px;
    font-weight: 700;

    padding-top: 12px;
}

@media (max-width: 700px) {
    .client-wrap {
        padding: 16px;
    }

    .products-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 420px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
}

.table-card {
    background: #fff;

    border-radius: var(--radius-lg);
    border: 1px solid var(--border);

    overflow: hidden;
    margin-bottom: 24px;
}

.table-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}

.table-card-header h3 {
    font-size: 15px;
    font-weight: 600;
}

.table-card-header a {
    font-size: 13px;
    font-weight: 500;

    color: var(--brown-accent);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: var(--cream-bg);

    padding: 10px 16px;

    text-align: left;

    font-size: 11px;
    font-weight: 600;

    letter-spacing: .07em;
    text-transform: uppercase;

    color: var(--text-mid);

    border-bottom: 1px solid var(--border);
}

.data-table td {
    padding: 12px 16px;

    border-bottom: 1px solid var(--border);

    font-size: 13.5px;
    color: var(--text-dark);

    vertical-align: middle;
}

.data-table tr:last-child td {
    border-bottom: none;
}

.data-table tr:hover td {
    background: var(--cream-bg);
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;

    padding: 3px 10px;

    border-radius: 20px;

    font-size: 12px;
    font-weight: 600;
}

.badge-green {
    background: var(--green-bg);
    color: var(--green);
}

.badge-red {
    background: var(--red-bg);
    color: var(--red);
}

.badge-orange {
    background: var(--orange-bg);
    color: var(--orange);
}

.badge-gray {
    background: #F0F0F0;
    color: #666;
}

.change-positive {
    color: var(--green);
    font-weight: 600;
}

.change-negative {
    color: var(--red);
    font-weight: 600;
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 14px 20px;

    border-top: 1px solid var(--border);

    font-size: 13px;
    color: var(--text-mid);
}

.pagination-btns {
    display: flex;
    gap: 6px;
}

.pagination-btns a,
.pagination-btns span {
    width: 30px;
    height: 30px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 6px;
    border: 1.5px solid var(--border);

    font-size: 13px;
    font-weight: 500;

    color: var(--text-mid);

    transition: all .2s ease;
}

.pagination-btns a:hover {
    border-color: var(--brown-accent);
    color: var(--brown-accent);
}

.pagination-btns .active {
    background: var(--brown-accent);
    border-color: var(--brown-accent);
    color: #fff;
}
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/client_nav.php'; ?>

<div class="client-wrap">
    <div class="page-header">
        <div>
            <h1>Order #<?= str_pad($orderId, 4, '0', STR_PAD_LEFT) ?></h1>
            <p class="subtitle">Placed on <?= date('F j, Y \a\t H:i', strtotime($order['created_at'])) ?></p>
        </div>
        <a href="/storehub/client/orders.php" class="btn btn-secondary">← My Orders</a>
    </div>

    <?php
    $statusColors = [
        'pending'    => ['bg' => 'var(--orange-bg)',   'color' => 'var(--orange)'],
        'processing' => ['bg' => 'var(--green-bg)',    'color' => 'var(--green)'],
        'completed'  => ['bg' => 'var(--green-bg)',    'color' => 'var(--green)'],
        'cancelled'  => ['bg' => 'var(--red-bg)',      'color' => 'var(--red)'],
    ];
    $sc = $statusColors[$order['status']] ?? ['bg'=>'#F5F5F5','color'=>'#666'];
    ?>
    <div style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>;border-radius:var(--radius);padding:14px 18px;margin-bottom:24px;font-weight:600;font-size:15px">
        Order Status: <?= ucfirst($order['status']) ?>
        <?php if ($order['status'] === 'pending'): ?>
            <span style="font-weight:400;font-size:13px;margin-left:10px">– We're processing your order</span>
        <?php elseif ($order['status'] === 'completed'): ?>
            <span style="font-weight:400;font-size:13px;margin-left:10px">– Your order has been fulfilled</span>
        <?php endif; ?>
    </div>

    <div class="table-card" style="margin-bottom:20px">
        <div class="table-card-header"><h3>Items Ordered</h3></div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>SKU</th>
                    <th>Unit Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($item = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                        <span class="text-muted text-sm"><?= htmlspecialchars($item['brand'] ?? '') ?></span>
                    </td>
                    <td><?= htmlspecialchars($item['cat_name'] ?? '—') ?></td>
                    <td style="font-family:monospace;font-size:12px"><?= htmlspecialchars($item['sku']) ?></td>
                    <td>Rs. <?= number_format($item['unit_price'], 2) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><strong>Rs. <?= number_format($item['unit_price'] * $item['quantity'], 2) ?></strong></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <div style="padding:14px 20px;text-align:right;border-top:1px solid var(--border)">
            <span style="font-size:18px;font-weight:700">
                Total: Rs. <?= number_format($order['total_amount'], 2) ?>
            </span>
        </div>
    </div>

    <?php if ($order['notes']): ?>
    <div class="adj-card">
        <strong style="font-size:13px;color:var(--text-mid)">YOUR NOTES</strong>
        <p style="margin-top:8px;font-size:13.5px"><?= htmlspecialchars($order['notes']) ?></p>
    </div>
    <?php endif; ?>

</div>

<script src="/storehub/assets/js/main.js"></script>
</body>
</html>
