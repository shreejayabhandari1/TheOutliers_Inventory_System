<?php
// ============================================
// Manager Dashboard
// Shows stats and recent activity
// ============================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireManager(); // redirect if not manager

// ---- Fetch stats ----

// Total products
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM products");
$totalProducts = mysqli_fetch_assoc($r)['c'];

// Out of stock
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM products WHERE stock = 0");
$outOfStock = mysqli_fetch_assoc($r)['c'];

// Low stock (stock > 0 but <= reorder_level)
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM products WHERE stock > 0 AND stock <= reorder_level");
$lowStock = mysqli_fetch_assoc($r)['c'];

// Total clients
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role = 'client'");
$totalClients = mysqli_fetch_assoc($r)['c'];

// Pending orders
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status = 'pending'");
$pendingOrders = mysqli_fetch_assoc($r)['c'];

// Active suppliers
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM suppliers WHERE status = 'active'");
$activeSuppliers = mysqli_fetch_assoc($r)['c'];

// Last 10 stock adjustments
$adjustments = mysqli_query($conn, "
    SELECT sa.*, p.name as product_name, p.sku, u.name as manager_name
    FROM stock_adjustments sa
    JOIN products p ON sa.product_id = p.id
    JOIN users u ON sa.adjusted_by = u.id
    ORDER BY sa.created_at DESC
    LIMIT 10
");

$active = 'dashboard';
$breadcrumb = ['Dashboard'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – StoreHub</title>    <style>

    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    :root{--brown-dark:#6F4532;--brown-main:#6F4532;--brown-light:#BD7559;--brown-accent:#BD7559;--cream-bg:#F8EDEB;--cream-light:#F8EDEB;--white:#FFFFFF;--text-dark:#2D1A10;--text-mid:#6F4532;--text-light:#BD7559;--border:#F3D1CB;--green:#2D7A4F;--green-bg:#E8F5EE;--red:#C0392B;--red-bg:#FDECEA;--orange:#D4750A;--orange-bg:#FEF3E2;--shadow:0 2px 8px rgba(90,40,15,.10);--shadow-md:0 4px 16px rgba(90,40,15,.14);--radius:8px;--radius-lg:12px;--sidebar-w:220px;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Inter',sans-serif;background:var(--cream-bg);color:var(--text-dark);font-size:14px;line-height:1.5;}
    a{text-decoration:none;color:inherit;}
    .text-center{text-align:center;}.mt-4{margin-top:16px;}.mb-4{margin-bottom:16px;}.text-sm{font-size:13px;}.text-muted{color:var(--text-light);}.font-bold{font-weight:700;}.hidden{display:none;}

    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 20px;border-radius:var(--radius);font-family:'Inter',sans-serif;font-size:14px;font-weight:500;cursor:pointer;border:none;transition:all .2s;}
    .btn-primary{background:var(--brown-accent);color:#fff;}.btn-primary:hover{background:var(--brown-main);}
    .btn-secondary{background:var(--cream-bg);color:var(--text-dark);border:1.5px solid var(--border);}.btn-secondary:hover{background:var(--border);}
    .btn-danger{background:var(--red-bg);color:var(--red);border:1.5px solid #F5C6C0;}.btn-danger:hover{background:var(--red);color:#fff;}
    .btn-full{width:100%;padding:12px;}.btn-sm{padding:6px 14px;font-size:13px;}

    .form-group{margin-bottom:18px;}
    .form-group label{display:block;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--text-mid);margin-bottom:6px;}
    .form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:var(--radius);font-family:'Inter',sans-serif;font-size:14px;color:var(--text-dark);background:#fff;transition:border-color .2s;outline:none;}
    .form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--brown-accent);}
    .form-group input::placeholder,.form-group textarea::placeholder{color:var(--text-light);}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .form-check-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
    .form-check-row label{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-mid);cursor:pointer;}
    .form-check-row a{font-size:13px;color:var(--brown-accent);}
    .filter-bar{display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap;}
    .search-input-wrap{position:relative;flex:1;min-width:200px;max-width:320px;}
    .search-input-wrap input{width:100%;padding:9px 14px 9px 36px;border:1.5px solid var(--border);border-radius:var(--radius);font-size:13.5px;background:#fff;outline:none;font-family:'Inter',sans-serif;color:var(--text-dark);}
    .search-input-wrap input:focus{border-color:var(--brown-accent);}
    .search-icon{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-light);font-size:15px;}
    .filter-select{padding:9px 32px 9px 12px;border:1.5px solid var(--border);border-radius:var(--radius);font-size:13.5px;background:#fff;outline:none;font-family:'Inter',sans-serif;color:var(--text-dark);cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%23A08060' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;}
    .alert{padding:12px 16px;border-radius:var(--radius);font-size:13.5px;margin-bottom:16px;display:flex;align-items:center;gap:10px;}
    .alert-success{background:var(--green-bg);color:var(--green);border:1px solid #B8DFC9;}
    .alert-error{background:var(--red-bg);color:var(--red);border:1px solid #F5C6C0;}
    .alert-warning{background:var(--orange-bg);color:var(--orange);border:1px solid #F5D99A;}

    .app-layout{display:flex;min-height:100vh;}
    .sidebar{width:var(--sidebar-w);background:var(--brown-main);display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;z-index:100;}
    .sidebar-logo{display:flex;align-items:center;gap:10px;padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,.1);}
    .sidebar-logo-icon{width:34px;height:34px;background:rgba(255,255,255,.2);border-radius:7px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;color:#fff;}
    .sidebar-logo h2{font-size:16px;font-weight:700;color:#fff;}
    .sidebar-nav{flex:1;padding:16px 0;overflow-y:auto;}
    .sidebar-section-label{font-size:10px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.45);padding:12px 20px 6px;}
    .sidebar-nav a{display:flex;align-items:center;gap:10px;padding:9px 20px;color:rgba(255,255,255,.75);font-size:13.5px;font-weight:400;transition:all .2s;border-left:3px solid transparent;}
    .sidebar-nav a:hover{background:rgba(255,255,255,.08);color:#fff;}
    .sidebar-nav a.active{background:rgba(255,255,255,.12);color:#fff;font-weight:600;border-left-color:rgba(255,255,255,.6);}
    .sidebar-nav .nav-icon{font-size:16px;width:20px;text-align:center;}
    .sidebar-footer{padding:16px 20px;border-top:1px solid rgba(255,255,255,.1);display:flex;align-items:center;gap:10px;}
    .sidebar-avatar{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0;}
    .sidebar-user-info{flex:1;min-width:0;}
    .sidebar-user-info .name{font-size:13px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .sidebar-user-info .role{font-size:11px;color:rgba(255,255,255,.55);}
    .sidebar-logout{color:rgba(255,255,255,.5);font-size:16px;cursor:pointer;text-decoration:none;transition:color .2s;}
    .sidebar-logout:hover{color:#fff;}
    .main-content{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh;}
    .topbar{background:#fff;border-bottom:1px solid var(--border);padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;gap:20px;}
    .topbar-logo{display:flex;align-items:center;text-decoration:none;flex-shrink:0;}
    .topbar-logo-text{font-size:18px;font-weight:700;letter-spacing:.05em;color:var(--brown-accent);font-family:'Comfortaa',cursive;}
    .topbar-search-wrap{flex:1;max-width:480px;position:relative;display:flex;align-items:center;}
    .topbar-search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-light);pointer-events:none;}
    .topbar-search-input{width:100%;padding:9px 14px 9px 38px;border:1.5px solid var(--border);border-radius:var(--radius);font-size:14px;font-family:'Inter',sans-serif;color:var(--text-dark);background:#fff;outline:none;transition:border-color .2s;}
    .topbar-search-input:focus{border-color:var(--brown-accent);}
    .topbar-search-input::placeholder{color:var(--text-light);}
    .topbar-right{display:flex;align-items:center;gap:12px;flex-shrink:0;}
    .topbar-bell{cursor:pointer;color:var(--text-mid);display:flex;align-items:center;padding:6px;border-radius:6px;transition:background .2s;}
    .topbar-bell:hover{background:var(--cream-bg);}
    .topbar-avatar{width:34px;height:34px;border-radius:50%;background:var(--brown-main);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0;}
    .topbar-user-info{display:flex;flex-direction:column;}
    .topbar-user-name{font-size:13.5px;font-weight:600;color:var(--text-dark);line-height:1.2;}
    .topbar-user-role{font-size:11px;color:var(--brown-accent);font-weight:500;}
    .page-content{padding:28px;flex:1;}
    .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;}
    .page-header h1{font-size:24px;font-weight:700;color:var(--text-dark);}
    .page-header .subtitle{font-size:13px;color:var(--text-mid);margin-top:2px;}
    @media(max-width:900px){:root{--sidebar-w:0px;}.sidebar{display:none;}.main-content{margin-left:0;}.form-row{grid-template-columns:1fr;}}

    .table-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--border);overflow:hidden;margin-bottom:24px;}
    .table-card-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);}
    .table-card-header h3{font-size:15px;font-weight:600;}
    .table-card-header a{font-size:13px;color:var(--brown-accent);font-weight:500;}
    .data-table{width:100%;border-collapse:collapse;}
    .data-table th{background:var(--cream-bg);padding:10px 16px;text-align:left;font-size:11px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--text-mid);border-bottom:1px solid var(--border);}
    .data-table td{padding:12px 16px;border-bottom:1px solid var(--border);color:var(--text-dark);font-size:13.5px;vertical-align:middle;}
    .data-table tr:last-child td{border-bottom:none;}
    .data-table tr:hover td{background:var(--cream-bg);}
    .badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;}
    .badge-green{background:var(--green-bg);color:var(--green);}
    .badge-red{background:var(--red-bg);color:var(--red);}
    .badge-orange{background:var(--orange-bg);color:var(--orange);}
    .badge-gray{background:#F0F0F0;color:#666;}
    .change-positive{color:var(--green);font-weight:600;}
    .change-negative{color:var(--red);font-weight:600;}
    .pagination{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid var(--border);font-size:13px;color:var(--text-mid);}
    .pagination-btns{display:flex;gap:6px;}
    .pagination-btns a,.pagination-btns span{width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;font-size:13px;font-weight:500;border:1.5px solid var(--border);color:var(--text-mid);transition:all .2s;}
    .pagination-btns a:hover{border-color:var(--brown-accent);color:var(--brown-accent);}
    .pagination-btns .active{background:var(--brown-accent);border-color:var(--brown-accent);color:#fff;}

    .stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;}
    .stat-card{background:var(--cream-light);border-radius:var(--radius-lg);padding:20px 22px;border:1px solid var(--border);}
    .stat-card .label{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-mid);margin-bottom:8px;}
    .stat-card .number{font-size:32px;font-weight:700;color:var(--text-dark);line-height:1;}
    .stat-card .sub{font-size:12px;color:var(--text-light);margin-top:4px;}
    .stat-card.red .number{color:var(--red);}.stat-card.orange .number{color:var(--orange);}.stat-card.green .number{color:var(--green);}
    @media(max-width:900px){.stats-grid{grid-template-columns:1fr 1fr;}}
    @media(max-width:600px){.stats-grid{grid-template-columns:1fr;}}

    </style>
</head>
<body>
<div class="app-layout">

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include __DIR__ . '/../includes/topbar.php'; ?>

        <div class="page-content">
            <!-- Page title -->
            <div class="page-header">
                <div>
                    <h1>Dashboard</h1>
            
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="label">Total Products</div>
                    <div class="number"><?= $totalProducts ?></div>
                    <div class="sub">Across all categories</div>
                </div>
                <div class="stat-card red">
                    <div class="label">Out of Stock</div>
                    <div class="number"><?= $outOfStock ?></div>
                    <div class="sub">Needs restocking</div>
                </div>
                <div class="stat-card orange">
                    <div class="label">Low Stock</div>
                    <div class="number"><?= $lowStock ?></div>
                    <div class="sub">Below reorder level</div>
                </div>
                <div class="stat-card">
                    <div class="label">Total Clients</div>
                    <div class="number"><?= $totalClients ?></div>
                    <div class="sub">Registered accounts</div>
                </div>
                <div class="stat-card orange">
                    <div class="label">Pending Orders</div>
                    <div class="number"><?= $pendingOrders ?></div>
                    <div class="sub">Waiting processing</div>
                </div>
                <div class="stat-card green">
                    <div class="label">Active Suppliers</div>
                    <div class="number"><?= $activeSuppliers ?></div>
                    <div class="sub">Active suppliers</div>
                </div>
            </div>

            <!-- Recent Stock Adjustments table -->
            <div class="table-card">
                <div class="table-card-header">
                    <h3>Last 10 Stock Adjustments</h3>
                    <a href="/storehub/manager/audit_log.php">View full log →</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date / Time</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Change</th>
                            <th>Reason</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($adjustments) === 0): ?>
                        <tr><td colspan="6" class="text-center text-muted" style="padding:24px">No adjustments yet.</td></tr>
                    <?php else: ?>
                        <?php while ($adj = mysqli_fetch_assoc($adjustments)): ?>
                        <tr>
                            <td><?= date('M j, H:i', strtotime($adj['created_at'])) ?></td>
                            <td><strong><?= htmlspecialchars($adj['product_name']) ?></strong></td>
                            <td style="font-family:monospace;font-size:12px"><?= htmlspecialchars($adj['sku']) ?></td>
                            <td>
                                <?php if ($adj['change_amount'] > 0): ?>
                                    <span class="change-positive">+<?= $adj['change_amount'] ?></span>
                                <?php else: ?>
                                    <span class="change-negative"><?= $adj['change_amount'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $adj['reason']))) ?></td>
                            <td><?= htmlspecialchars($adj['manager_name']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
<script src="/storehub/assets/js/main.js"></script>
</body>
</html>
