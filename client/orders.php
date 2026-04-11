<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireClient();

$clientId = $_SESSION['user_id'];
$success  = $_GET['success'] ?? '';

$stmt = mysqli_prepare($conn, "
    SELECT o.*, COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.client_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $clientId);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders – StoreHub</title><style>
        body { background: var(--cream-bg); }
        .client-wrap { max-width: 900px; margin: 0 auto; padding: 28px; }
    </style>
    <style>

    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');


:root{
 --brown-dark:#6F4532;
 --brown-main:#6F4532;
 --brown-light:#BD7559;
 --brown-accent:#BD7559;

 --cream-bg:#F8EDEB;
 --cream-light:#F8EDEB;

 --white:#FFFFFF;

 --text-dark:#2D1A10;
 --text-mid:#6F4532;
 --text-light:#BD7559;

 --border:#F3D1CB;

 --green:#2D7A4F;
 --green-bg:#E8F5EE;

 --red:#C0392B;
 --red-bg:#FDECEA;

 --orange:#D4750A;
 --orange-bg:#FEF3E2;

 --shadow:0 2px 8px rgba(90,40,15,.10);
 --shadow-md:0 4px 16px rgba(90,40,15,.14);

 --radius:8px;
 --radius-lg:12px;

 --sidebar-w:220px;
}


*{
 margin:0;
 padding:0;
 box-sizing:border-box;
}

body{
 font-family:'Inter',sans-serif;
 background:var(--cream-bg);
 color:var(--text-dark);
 font-size:14px;
 line-height:1.5;
}

a{
 text-decoration:none;
 color:inherit;
}


.text-center{ text-align:center; }
.mt-4{ margin-top:16px; }
.mb-4{ margin-bottom:16px; }

.text-sm{ font-size:13px; }

.text-muted{ color:var(--text-light); }

.font-bold{ font-weight:700; }

.hidden{ display:none; }


.btn{
 display:inline-flex;
 align-items:center;
 justify-content:center;
 gap:8px;

 padding:10px 20px;

 border-radius:var(--radius);

 font-size:14px;
 font-weight:500;

 cursor:pointer;

 border:none;

 transition:.2s;
}

.btn-primary{
 background:var(--brown-accent);
 color:#fff;
}

.btn-primary:hover{
 background:var(--brown-main);
}

.btn-secondary{
 background:var(--cream-bg);
 color:var(--text-dark);
 border:1.5px solid var(--border);
}

.btn-secondary:hover{
 background:var(--border);
}

.btn-danger{
 background:var(--red-bg);
 color:var(--red);
 border:1.5px solid #F5C6C0;
}

.btn-danger:hover{
 background:var(--red);
 color:#fff;
}

.btn-full{
 width:100%;
 padding:12px;
}

.btn-sm{
 padding:6px 14px;
 font-size:13px;
}


.form-group{
 margin-bottom:18px;
}

.form-group label{
 display:block;

 font-size:11px;
 font-weight:600;

 letter-spacing:.06em;

 text-transform:uppercase;

 color:var(--text-mid);

 margin-bottom:6px;
}

.form-group input,
.form-group select,
.form-group textarea{

 width:100%;

 padding:10px 14px;

 border:1.5px solid var(--border);

 border-radius:var(--radius);

 font-size:14px;

 background:#fff;

 outline:none;

 transition:.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus{

 border-color:var(--brown-accent);
}

.form-group input::placeholder,
.form-group textarea::placeholder{

 color:var(--text-light);
}


.form-row{

 display:grid;

 grid-template-columns:1fr 1fr;

 gap:16px;
}


.form-check-row{

 display:flex;

 align-items:center;

 justify-content:space-between;

 margin-bottom:20px;
}


.form-check-row label{

 display:flex;

 align-items:center;

 gap:8px;

 font-size:13px;

 color:var(--text-mid);
}

.form-check-row a{

 font-size:13px;

 color:var(--brown-accent);
}


.filter-bar{

 display:flex;

 align-items:center;

 gap:12px;

 margin-bottom:18px;

 flex-wrap:wrap;
}

.search-input-wrap{

 position:relative;

 flex:1;

 min-width:200px;

 max-width:320px;
}

.search-input-wrap input{

 width:100%;

 padding:9px 14px 9px 36px;

 border:1.5px solid var(--border);

 border-radius:var(--radius);
}

.search-icon{

 position:absolute;

 left:11px;

 top:50%;

 transform:translateY(-50%);

 color:var(--text-light);
}


.filter-select{

 padding:9px 32px 9px 12px;

 border:1.5px solid var(--border);

 border-radius:var(--radius);

 cursor:pointer;

 appearance:none;

 background:#fff;
}


.alert{

 padding:12px 16px;

 border-radius:var(--radius);

 font-size:13.5px;

 margin-bottom:16px;

 display:flex;

 align-items:center;

 gap:10px;
}

.alert-success{

 background:var(--green-bg);

 color:var(--green);
}

.alert-error{

 background:var(--red-bg);

 color:var(--red);
}

.alert-warning{

 background:var(--orange-bg);

 color:var(--orange);
}


.client-wrap{

 max-width:1200px;

 margin:auto;

 padding:28px;
}

.cat-pills{

 display:flex;

 flex-wrap:wrap;

 gap:8px;

 margin-bottom:22px;
}

.cat-pill{

 padding:7px 18px;

 border-radius:20px;

 border:1.5px solid var(--border);

 background:#fff;

 font-size:13px;

 cursor:pointer;

 transition:.18s;
}

.cat-pill:hover{

 border-color:var(--brown-accent);

 color:var(--brown-accent);
}

.cat-pill.active{

 background:var(--brown-accent);

 color:#fff;
}


.products-grid{

 display:grid;

 grid-template-columns:repeat(auto-fill,minmax(210px,1fr));

 gap:18px;
}


.product-card{

 background:#fff;

 border-radius:var(--radius-lg);

 border:1px solid var(--border);

 overflow:hidden;

 transition:.2s;
}

.product-card:hover{

 box-shadow:var(--shadow-md);

 transform:translateY(-2px);
}


.product-card-body{

 padding:14px 16px;
}

.product-card-brand{

 font-size:11px;

 color:var(--text-light);
}

.product-card-name{

 font-size:14px;

 font-weight:600;
}

.product-card-price{

 font-size:18px;

 font-weight:700;

 color:var(--brown-accent);

 margin-bottom:10px;
}


.cart-summary{

 background:#fff;

 border-radius:var(--radius-lg);

 border:1px solid var(--border);

 padding:20px;
}

.cart-item{

 display:flex;

 justify-content:space-between;

 padding:12px 0;

 border-bottom:1px solid var(--border);
}

.cart-total{

 display:flex;

 justify-content:space-between;

 font-size:16px;

 font-weight:700;
}


.table-card{

 background:#fff;

 border-radius:var(--radius-lg);

 border:1px solid var(--border);

 overflow:hidden;

 margin-bottom:24px;
}

.table-card-header{

 display:flex;

 justify-content:space-between;

 padding:16px 20px;

 border-bottom:1px solid var(--border);
}

.data-table{

 width:100%;

 border-collapse:collapse;
}

.data-table th{

 background:var(--cream-bg);

 padding:10px 16px;

 font-size:11px;

 text-transform:uppercase;

 border-bottom:1px solid var(--border);
}

.data-table td{

 padding:12px 16px;

 border-bottom:1px solid var(--border);
}

.data-table tr:hover td{

 background:var(--cream-bg);
}

.badge{

 padding:3px 10px;

 border-radius:20px;

 font-size:12px;

 font-weight:600;
}

.badge-green{

 background:var(--green-bg);

 color:var(--green);
}

.badge-red{

 background:var(--red-bg);

 color:var(--red);
}

.badge-orange{

 background:var(--orange-bg);

 color:var(--orange);
}

.badge-gray{

 background:#F0F0F0;
}


.change-positive{

 color:var(--green);

 font-weight:600;
}

.change-negative{

 color:var(--red);

 font-weight:600;
}


.pagination{

 display:flex;

 justify-content:space-between;

 padding:14px 20px;

 border-top:1px solid var(--border);
}

.pagination-btns{

 display:flex;

 gap:6px;
}

.pagination-btns a,
.pagination-btns span{

 width:30px;

 height:30px;

 display:flex;

 align-items:center;

 justify-content:center;

 border-radius:6px;

 border:1.5px solid var(--border);
}

.pagination-btns a:hover{

 border-color:var(--brown-accent);

 color:var(--brown-accent);
}

.pagination-btns .active{

 background:var(--brown-accent);

 color:#fff;
}


@media(max-width:700px){

 .client-wrap{

  padding:16px;
 }

 .products-grid{

  grid-template-columns:1fr 1fr;
 }

}

@media(max-width:420px){

 .products-grid{

  grid-template-columns:1fr;
 }

}

    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/client_nav.php'; ?>

<div class="client-wrap">
    <div class="page-header">
        <div>
            <h1>My Orders</h1>
            <p class="subtitle">Your order history</p>
        </div>
        <a href="/storehub/client/products.php" class="btn btn-primary">+ Place New Order</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($orders) === 0): ?>
        <div style="text-align:center;padding:80px 20px;background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border)">
            <div style="font-size:64px;margin-bottom:20px">📋</div>
            <h2 style="font-size:20px;color:var(--text-mid);margin-bottom:10px">No orders yet</h2>
            <p class="text-muted text-sm" style="margin-bottom:24px">Browse our products and place your first order!</p>
            <a href="/storehub/client/products.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php else: ?>

        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($o = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td><strong>#<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= $o['item_count'] ?> item<?= $o['item_count'] != 1 ? 's' : '' ?></td>
                        <td><strong>Rs. <?= number_format($o['total_amount'], 2) ?></strong></td>
                        <td>
                            <?php
                            $bmap = [
                                'pending'    => 'badge-orange',
                                'processing' => 'badge-green',
                                'completed'  => 'badge-green',
                                'cancelled'  => 'badge-red',
                            ];
                            $cls = $bmap[$o['status']] ?? 'badge-gray';
                            ?>
                            <span class="badge <?= $cls ?>"><?= ucfirst($o['status']) ?></span>
                        </td>
                        <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                        <td>
                            <a href="/storehub/client/order_detail.php?id=<?= $o['id'] ?>"
                               class="btn btn-secondary btn-sm">View Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

<script src="/storehub/assets/js/main.js"></script>
</body>
</html>
