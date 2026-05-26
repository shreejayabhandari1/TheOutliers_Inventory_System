<?php
// ============================================
// Manager – Products Page
// List, search, add, edit, delete products
// ============================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireManager();

// --- Handle messages from backend ---
$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';

// --- Pagination ---
$perPage = 10;
$page    = max(1, intval($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// --- Filters ---
$search   = trim($_GET['search'] ?? '');
$isAjax   = isset($_GET['ajax']);
$catFilter = intval($_GET['cat'] ?? 0);
$statFilter = trim($_GET['status'] ?? '');
$supFilter  = intval($_GET['sup'] ?? 0);

// Build WHERE clause
$where = "WHERE 1=1";
$params = [];
$types  = "";

if ($search !== '') {
    $where .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.brand LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}
if ($catFilter > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $catFilter;
    $types .= "i";
}
if ($statFilter !== '') {
    $where .= " AND p.status = ?";
    $params[] = $statFilter;
    $types .= "s";
}
if ($supFilter > 0) {
    $where .= " AND p.supplier_id = ?";
    $params[] = $supFilter;
    $types .= "i";
}

// Total count for pagination
$countSql = "SELECT COUNT(*) as c FROM products p $where";
$stmt = mysqli_prepare($conn, $countSql);
if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$totalRows = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];
$totalPages = ceil($totalRows / $perPage);

// Fetch products
$sql = "SELECT p.*, c.name as cat_name, s.name as sup_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN suppliers s  ON p.supplier_id  = s.id
        $where
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?";
$params[] = $perPage; $params[] = $offset;
$types .= "ii";
$stmt = mysqli_prepare($conn, $sql);
if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$products = mysqli_stmt_get_result($stmt);

// Fetch categories and suppliers for dropdowns
$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status='active' ORDER BY name");
$suppliers  = mysqli_query($conn, "SELECT * FROM suppliers WHERE status='active' ORDER BY name");

$active = 'products';
$breadcrumb = ['Inventory', 'Products'];
?>
<?php
// AJAX: return only tbody rows
if ($isAjax) {
    if (mysqli_num_rows($products) === 0) {
        echo '<tr><td colspan="9" class="text-center text-muted" style="padding:32px">No products found.</td></tr>';
    } else {
        while ($p = mysqli_fetch_assoc($products)) {
            $imgCell = !empty($p['image_url'])
                ? '<img src="'.htmlspecialchars($p['image_url']).'" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">'
                : '<div style="width:40px;height:40px;background:var(--cream-bg);border-radius:6px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:18px">📦</div>';
            if ($p['status'] === 'inactive') $badge='<span class="badge badge-gray">Inactive</span>';
            elseif ($p['stock']==0) $badge='<span class="badge badge-red">Out of Stock</span>';
            elseif($p['stock']<=$p['reorder_level']) $badge='<span class="badge badge-orange">Low Stock</span>';
            else $badge='<span class="badge badge-green">● Active</span>';
            $editData = json_encode(['id'=>$p['id'],'name'=>$p['name'],'brand'=>$p['brand'],'model_number'=>$p['model_number'],'sku'=>$p['sku'],'category_id'=>$p['category_id'],'supplier_id'=>$p['supplier_id'],'cost_price'=>$p['cost_price'],'selling_price'=>$p['selling_price'],'stock'=>$p['stock'],'reorder_level'=>$p['reorder_level'],'reorder_quantity'=>$p['reorder_quantity'],'warranty_period'=>$p['warranty_period'],'status'=>$p['status'],'description'=>$p['description'],'image_url'=>$p['image_url']??'']);
            echo '<tr>
                <td>'.$imgCell.'</td>
                <td><strong>'.htmlspecialchars($p['name']).'</strong><br><span class="text-muted text-sm">'.htmlspecialchars($p['brand'].' · '.$p['model_number']).'</span></td>
                <td style="font-family:monospace;font-size:12px">'.htmlspecialchars($p['sku']).'</td>
                <td>'.htmlspecialchars($p['cat_name']??'—').'</td>
                <td>'.htmlspecialchars($p['sup_name']??'—').'</td>
                <td><strong>Rs. '.number_format($p['selling_price'],2).'</strong></td>
                <td>'.intval($p['stock']).'</td>
                <td>'.$badge.'</td>
                <td><div style="display:flex;gap:8px;white-space:nowrap">
                    <button class="btn btn-secondary btn-sm" onclick="editProduct('.$editData.')">Edit</button>
                    <a href="/storehub/backend/products/toggle_product_status.php?id='.$p['id'].'" class="btn '.($p['status']==='active'?'btn-danger':'btn-secondary').' btn-sm" onclick="return confirm(\''.($p['status']==='active'?'Deactivate':'Reactivate').' '.htmlspecialchars(addslashes($p['name'])).'?\')">'.($p['status']==='active'?'Deactivate':'Reactivate').'</a>
                </div></td>
            </tr>';
        }
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products – StoreHub</title>
    <link rel="stylesheet" href="/storehub/assets/css/animations.css">    <style>

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

    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(30,15,5,.5);z-index:1000;align-items:center;justify-content:center;}
    .modal-overlay.open{display:flex;}
    .modal{background:#fff;border-radius:var(--radius-lg);width:580px;max-width:95vw;max-height:90vh;overflow-y:auto;box-shadow:var(--shadow-md);animation:modalIn .2s ease;}
    @keyframes modalIn{from{opacity:0;transform:translateY(-16px) scale(.98);}to{opacity:1;transform:translateY(0) scale(1);}}
    .modal-header{display:flex;align-items:center;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid var(--border);}
    .modal-header h2{font-size:17px;font-weight:700;}
    .modal-close{background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-light);padding:2px 6px;border-radius:4px;line-height:1;transition:background .2s;}
    .modal-close:hover{background:var(--cream-bg);color:var(--text-dark);}
    .modal-body{padding:20px 24px;}
    .modal-section-label{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-light);margin:16px 0 12px;}
    .modal-section-label:first-child{margin-top:0;}
    .modal-footer{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid var(--border);background:var(--cream-bg);border-radius:0 0 var(--radius-lg) var(--radius-lg);}
    .modal-warning{font-size:12px;color:var(--orange);padding:10px 24px 4px;display:flex;align-items:center;gap:6px;}

    </style>
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include __DIR__ . '/../includes/topbar.php'; ?>

        <div class="page-content">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Products</h1>
                </div>
                <div style="display:flex;gap:10px;align-items:center">
                    <a href="/storehub/backend/products/export_csv.php"
                       class="btn btn-secondary btn-sm"
                       style="display:inline-flex;align-items:center;gap:6px;font-weight:600">
                        ⬇ Export CSV
                    </a>
                    <button class="btn btn-primary" onclick="openModal('add-product-modal')">
                        + Add Product
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Filter bar -->
            <div class="filter-bar" style="margin-bottom:18px;">
                <div class="search-input-wrap">
                    <span class="search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="text" id="page-search-input" placeholder="Search products, SKU, brand…"
                           value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                </div>
                <select id="cat-filter" class="filter-select">
                    <option value="0">All Categories</option>
                    <?php mysqli_data_seek($categories, 0); while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= $cat['id'] ?>" <?= $catFilter == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <select id="status-filter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $statFilter==='active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $statFilter==='inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <select id="sup-filter" class="filter-select">
                    <option value="0">All Suppliers</option>
                    <?php mysqli_data_seek($suppliers, 0); while ($sup = mysqli_fetch_assoc($suppliers)): ?>
                        <option value="<?= $sup['id'] ?>" <?= $supFilter == $sup['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sup['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Products Table -->
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Selling Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body">
                    <?php if (mysqli_num_rows($products) === 0): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted" style="padding:32px">
                                No products found. Try a different search or add a new product.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($p = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td>
                                <?php if (!empty($p['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($p['image_url']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">
                                <?php else: ?>
                                    <div style="width:40px;height:40px;background:var(--cream-bg);border-radius:6px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:18px">📦</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                                <span class="text-muted text-sm"><?= htmlspecialchars($p['brand'] . ' · ' . $p['model_number']) ?></span>
                            </td>
                            <td style="font-family:monospace;font-size:12px"><?= htmlspecialchars($p['sku']) ?></td>
                            <td><?= htmlspecialchars($p['cat_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($p['sup_name'] ?? '—') ?></td>
                            <td><strong>Rs. <?= number_format($p['selling_price'], 2) ?></strong></td>
                            <td><?= intval($p['stock']) ?></td>
                            <td>
                                <?php if ($p['status'] === 'inactive'): ?>
                                    <span class="badge badge-gray">Inactive</span>
                                <?php elseif ($p['stock'] == 0): ?>
                                    <span class="badge badge-red">Out of Stock</span>
                                <?php elseif ($p['stock'] <= $p['reorder_level']): ?>
                                    <span class="badge badge-orange">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-green">● Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;white-space:nowrap">
                                    <button class="btn btn-secondary btn-sm"
                                        onclick='editProduct(<?= json_encode([
                                            "id"               => $p["id"],
                                            "name"             => $p["name"],
                                            "brand"            => $p["brand"],
                                            "model_number"     => $p["model_number"],
                                            "sku"              => $p["sku"],
                                            "category_id"      => $p["category_id"],
                                            "supplier_id"      => $p["supplier_id"],
                                            "cost_price"       => $p["cost_price"],
                                            "selling_price"    => $p["selling_price"],
                                            "stock"            => $p["stock"],
                                            "reorder_level"    => $p["reorder_level"],
                                            "reorder_quantity" => $p["reorder_quantity"],
                                            "warranty_period"  => $p["warranty_period"],
                                            "status"           => $p["status"],
                                            "description"      => $p["description"],
                                            "image_url"        => $p["image_url"] ?? "",
                                        ]) ?>)'>Edit</button>
                                    <a href="/storehub/backend/products/toggle_product_status.php?id=<?= $p['id'] ?>"
                                       class="btn <?= $p['status']==='active' ? 'btn-danger' : 'btn-secondary' ?> btn-sm"
                                       onclick="return confirm('<?= $p['status']==='active' ? 'Deactivate' : 'Reactivate' ?> <?= htmlspecialchars(addslashes($p['name']), ENT_QUOTES) ?>?')">
                                        <?= $p['status']==='active' ? 'Deactivate' : 'Reactivate' ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    <div class="pagination-btns">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= $catFilter ?>&status=<?= $statFilter ?>"
                               class="<?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&cat=<?= $catFilter ?>&status=<?= $statFilter ?>">›</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div><!-- /page-content -->
    </div><!-- /main-content -->
</div><!-- /app-layout -->

<!-- ============================================
     ADD PRODUCT MODAL
     ============================================ -->
<div class="modal-overlay" id="add-product-modal">
    <div class="modal">
        <div class="modal-header">
            <h2>Add New Product</h2>
            <button class="modal-close" onclick="closeModal('add-product-modal')">✕</button>
        </div>

        <form action="/storehub/backend/products/add_product.php" method="POST" enctype="multipart/form-data"
              onsubmit="return validateProductForm()">
            <div class="modal-body">

                <div class="modal-section-label">Basic Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" id="name" name="name" placeholder="e.g. Dell XPS 15">
                    </div>
                    <div class="form-group">
                        <label>SKU *</label>
                        <input type="text" id="sku" name="sku" placeholder="e.g. DL-XPS15-001">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Brand *</label>
                        <input type="text" name="brand" placeholder="e.g. Dell">
                    </div>
                    <div class="form-group">
                        <label>Model Number</label>
                        <input type="text" name="model_number" placeholder="e.g. XPS-15-9520">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select id="category_id" name="category_id">
                            <option value="">Select category…</option>
                            <?php
                            mysqli_data_seek($categories, 0);
                            while ($cat = mysqli_fetch_assoc($categories)):
                            ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <select name="supplier_id">
                            <option value="">Select supplier…</option>
                            <?php
                            mysqli_data_seek($suppliers, 0);
                            while ($sup = mysqli_fetch_assoc($suppliers)):
                            ?>
                                <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-section-label">Pricing & Stock Levels</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Cost Price (Rs. )</label>
                        <input type="number" name="cost_price" step="0.01" value="0.00" min="0">
                    </div>
                    <div class="form-group">
                        <label>Selling Price (Rs. ) *</label>
                        <input type="number" id="selling_price" name="selling_price" step="0.01" value="0.00" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Reorder Level *</label>
                        <input type="number" name="reorder_level" value="5" min="0">
                    </div>
                    <div class="form-group">
                        <label>Reorder Quantity *</label>
                        <input type="number" name="reorder_quantity" value="10" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Warranty Period</label>
                        <input type="text" name="warranty_period" placeholder="e.g. 12 months">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Product description…"></textarea>
                </div>
            </div>

            <div class="modal-body" style="padding-top:0">
                <div style="font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-light);margin-bottom:10px">Product Image</div>
                <div class="form-group">
                    <label>Upload Image (JPG/PNG)</label>
                    <input type="file" name="product_image" accept="image/jpeg,image/png,image/webp" onchange="previewImg(this,'add-img-preview')">
                    <img id="add-img-preview" style="display:none;width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border);margin-top:8px">
                </div>
                <div class="form-group">
                    <label>Or Image URL</label>
                    <input type="text" name="image_url" placeholder="https://example.com/image.jpg">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('add-product-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================
     EDIT PRODUCT MODAL
     ============================================ -->
<div class="modal-overlay" id="edit-product-modal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Product</h2>
            <button class="modal-close" onclick="closeModal('edit-product-modal')">✕</button>
        </div>

        <form action="/storehub/backend/products/edit_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="edit_id" name="id">
            <div class="modal-body">
                <div class="modal-section-label">Basic Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" id="edit_name" name="name" placeholder="Product name">
                    </div>
                    <div class="form-group">
                        <label>SKU *</label>
                        <input type="text" id="edit_sku" name="sku" placeholder="SKU">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Brand</label>
                        <input type="text" id="edit_brand" name="brand">
                    </div>
                    <div class="form-group">
                        <label>Model Number</label>
                        <input type="text" id="edit_model_number" name="model_number">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select id="edit_category_id" name="category_id">
                            <option value="">Select category…</option>
                            <?php
                            mysqli_data_seek($categories, 0);
                            while ($cat = mysqli_fetch_assoc($categories)):
                            ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <select id="edit_supplier_id" name="supplier_id">
                            <option value="">Select supplier…</option>
                            <?php
                            mysqli_data_seek($suppliers, 0);
                            while ($sup = mysqli_fetch_assoc($suppliers)):
                            ?>
                                <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-section-label">Pricing & Stock</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Cost Price (Rs. )</label>
                        <input type="number" id="edit_cost_price" name="cost_price" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Selling Price (Rs. ) *</label>
                        <input type="number" id="edit_selling_price" name="selling_price" step="0.01" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Reorder Level</label>
                        <input type="number" id="edit_reorder_level" name="reorder_level" min="0">
                    </div>
                    <div class="form-group">
                        <label>Reorder Quantity</label>
                        <input type="number" id="edit_reorder_quantity" name="reorder_quantity" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Warranty Period</label>
                        <input type="text" id="edit_warranty_period" name="warranty_period">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="edit_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="edit_description" name="description" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-body" style="padding-top:0">
                <div style="font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-light);margin-bottom:10px">Product Image</div>
                <div style="margin-bottom:10px"><img id="edit-img-preview" style="display:none;width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border)"></div>
                <div class="form-group">
                    <label>Upload new image (JPG/PNG)</label>
                    <input type="file" name="product_image" accept="image/jpeg,image/png,image/webp" onchange="previewImg(this,'edit-img-preview')">
                </div>
                <div class="form-group">
                    <label>Or Image URL</label>
                    <input type="text" id="edit_image_url" name="image_url" placeholder="https://example.com/image.jpg">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('edit-product-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>

<script src="/storehub/assets/js/main.js"></script>
<script>
(function(){
    var searchTimer;
    var tbody = document.getElementById('products-table-body');

    function fetchProducts() {
        var q      = document.getElementById('page-search-input').value.trim();
        var cat    = document.getElementById('cat-filter').value;
        var status = document.getElementById('status-filter').value;
        var sup    = document.getElementById('sup-filter').value;
        var url    = '/storehub/manager/products.php?ajax=1'
                   + '&search=' + encodeURIComponent(q)
                   + '&cat='    + encodeURIComponent(cat)
                   + '&status=' + encodeURIComponent(status)
                   + '&sup='    + encodeURIComponent(sup);
        tbody.style.opacity = '0.5';
        fetch(url)
            .then(function(r){ return r.text(); })
            .then(function(html){
                tbody.innerHTML = html;
                tbody.style.opacity = '1';
            });
    }

    document.getElementById('page-search-input').addEventListener('input', function(){
        clearTimeout(searchTimer);
        searchTimer = setTimeout(fetchProducts, 320);
    });
    document.getElementById('cat-filter').addEventListener('change', fetchProducts);
    document.getElementById('status-filter').addEventListener('change', fetchProducts);
    document.getElementById('sup-filter').addEventListener('change', fetchProducts);
})();
</script>
</body>
</html>