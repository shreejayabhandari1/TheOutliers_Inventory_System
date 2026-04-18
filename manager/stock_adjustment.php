<?php
// ============================================
// Manager – Stock Adjustment Page
// Matches design image 7 exactly
// ============================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireManager();

$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';

// Pre-select product if passed via URL
$selectedProductId = intval($_GET['product_id'] ?? 0);

// Fetch all active products for dropdown
$products = mysqli_query($conn, "SELECT id, name, sku, stock FROM products WHERE status='active' ORDER BY name");

// Fetch recent adjustments for right panel
$recent = mysqli_query($conn, "
    SELECT sa.*, p.name as product_name, p.sku
    FROM stock_adjustments sa
    JOIN products p ON sa.product_id = p.id
    ORDER BY sa.created_at DESC
    LIMIT 5
");

$active = 'stock';
$breadcrumb = ['Operations', 'Stock Adjustment'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Adjustment – StoreHub</title>    <style>

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

    .adjustment-layout{display:grid;grid-template-columns:1fr 320px;gap:20px;}
    .adj-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--border);padding:24px;}
    .adj-card h3{font-size:16px;font-weight:700;margin-bottom:20px;}
    .adj-type-btns{display:flex;gap:10px;margin-bottom:16px;}
    .adj-type-btn{flex:1;padding:10px;border:2px solid var(--border);border-radius:var(--radius);background:#fff;cursor:pointer;font-family:'Inter',sans-serif;font-size:13.5px;font-weight:500;color:var(--text-mid);transition:all .2s;text-align:center;}
    .adj-type-btn.selected{border-color:var(--brown-accent);background:#FEF3ED;color:var(--brown-accent);}
    .reason-chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;}
    .reason-chip{padding:5px 12px;border-radius:20px;border:1.5px solid var(--border);background:#fff;font-size:12px;font-weight:500;cursor:pointer;color:var(--text-mid);transition:all .2s;}
    .reason-chip:hover,.reason-chip.selected{border-color:var(--brown-accent);background:#FEF3ED;color:var(--brown-accent);}
    .stock-preview{background:var(--cream-bg);border-radius:var(--radius);padding:14px 16px;margin-top:16px;}
    .stock-preview-row{display:flex;justify-content:space-between;font-size:13.5px;padding:4px 0;color:var(--text-mid);}
    .stock-preview-row.total{border-top:1px solid var(--border);margin-top:6px;padding-top:10px;font-weight:700;font-size:15px;color:var(--text-dark);}
    @media(max-width:900px){.adjustment-layout{grid-template-columns:1fr;}}

    </style>
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../includes/topbar.php'; ?>
        <div class="page-content">

            <div class="page-header">
                <div>
                    <h1>Stock Adjustment</h1>
                    
                </div>
            </div>

            <?php if ($success): ?><div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

            <div class="adjustment-layout">

                <!-- LEFT: Form -->
                <div class="adj-card">
                    <h3>New Adjustment</h3>

                    <form action="/storehub/backend/stock/save_adjustment.php" method="POST"
                          onsubmit="return validateAdjustmentForm()">

                        <!-- Product Selector -->
                        <div class="form-group">
                            <label>Product *</label>
                            <select name="product_id" id="product-select" onchange="loadProductInfo(this)"
                                    style="width:100%">
                                <option value="">Select a product…</option>
                                <?php while ($p = mysqli_fetch_assoc($products)): ?>
                                    <option value="<?= $p['id'] ?>"
                                            data-stock="<?= $p['stock'] ?>"
                                            data-sku="<?= htmlspecialchars($p['sku']) ?>"
                                            <?= $selectedProductId == $p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['sku']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Product info card -->
                        <div id="product-info" style="display:none;background:var(--cream-bg);border-radius:var(--radius);padding:12px 14px;margin-bottom:16px;font-size:13.5px">
                            <strong id="info-name"></strong>
                            <span style="float:right" class="badge badge-green">● Active</span><br>
                            <span class="text-muted" id="info-sku"></span>
                            &nbsp;·&nbsp; Current stock: <strong id="info-stock"></strong> units
                        </div>

                        <!-- Adjustment Type Buttons -->
                        <div class="form-group">
                            <label>Adjustment Type *</label>
                            <div class="adj-type-btns">
                                <label class="adj-type-btn selected" id="btn-add">
                                    <input type="radio" name="adj_type" value="add" checked style="display:none"> ＋ Add Stock
                                </label>
                                <label class="adj-type-btn" id="btn-remove">
                                    <input type="radio" name="adj_type" value="remove" style="display:none"> － Remove Stock
                                </label>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="form-group">
                            <label>Quantity *</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1"
                                   placeholder="e.g. 20">
                        </div>

                        <!-- Reason chips -->
                        <div class="form-group">
                            <label>Reason *</label>
                            <input type="hidden" id="reason-value" name="reason" value="new_stock_received">
                            <div class="reason-chips">
                                <span class="reason-chip selected" data-value="new_stock_received">New stock received</span>
                                <span class="reason-chip" data-value="damaged_goods">Damaged goods</span>
                                <span class="reason-chip" data-value="lost_missing">Lost / missing</span>
                                <span class="reason-chip" data-value="supplier_delivery">Supplier delivery</span>
                                <span class="reason-chip" data-value="return_from_client">Return from client</span>
                                <span class="reason-chip" data-value="other">Other</span>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="form-group">
                            <label>Additional Notes</label>
                            <textarea name="notes" rows="3" placeholder="Optional – add more detail…"></textarea>
                        </div>

                        <!-- Live stock preview -->
                        <div class="stock-preview">
                            <div class="stock-preview-row">
                                <span>Current stock</span>
                                <span id="stock-before" data-value="0">0 units</span>
                            </div>
                            <div class="stock-preview-row">
                                <span>Adjustment</span>
                                <span id="stock-change" class="change-positive">+0</span>
                            </div>
                            <div class="stock-preview-row total">
                                <span>New stock level</span>
                                <span id="stock-after">0 units</span>
                            </div>
                        </div>

                        <div style="display:flex;gap:10px;margin-top:20px">
                            <a href="/storehub/manager/stock_adjustment.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Adjustment</button>
                        </div>

                       
                    </form>
                </div>

                <!-- RIGHT: Recent Adjustments -->
                <div>
                    <div class="adj-card">
                        <h3>Recent Adjustments</h3>
                        <table class="data-table" style="font-size:12.5px">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Product</th>
                                    <th>±</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($adj = mysqli_fetch_assoc($recent)): ?>
                                <tr>
                                    <td><?= date('M j, H:i', strtotime($adj['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($adj['product_name']) ?></td>
                                    <td>
                                        <?php if ($adj['change_amount'] > 0): ?>
                                            <span class="change-positive">+<?= $adj['change_amount'] ?></span>
                                        <?php else: ?>
                                            <span class="change-negative"><?= $adj['change_amount'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= ucfirst(str_replace('_',' ',$adj['reason'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div style="padding:12px 0 0;text-align:center">
                            <a href="/storehub/manager/audit_log.php" style="font-size:13px;color:var(--brown-accent)">View full audit log →</a>
                        </div>
                    </div>
                </div>

            </div><!-- /adjustment-layout -->
        </div>
    </div>
</div>

<script src="/storehub/assets/js/main.js"></script>
<script>
// ============================================
// Stock Adjustment Page Specific JS
// ============================================

// When manager selects a product, show info card + update preview
function loadProductInfo(select) {
    const opt = select.options[select.selectedIndex];
    const stock = parseInt(opt.dataset.stock) || 0;
    const sku   = opt.dataset.sku || '';
    const name  = opt.text || '';

    // Show info card
    document.getElementById('product-info').style.display = 'block';
    document.getElementById('info-name').textContent  = name.split('(')[0].trim();
    document.getElementById('info-sku').textContent   = sku;
    document.getElementById('info-stock').textContent = stock;

    // Set stock before value for preview
    const beforeEl = document.getElementById('stock-before');
    beforeEl.textContent  = stock + ' units';
    beforeEl.dataset.value = stock;

    // Trigger preview update
    initStockAdjustment();
    document.getElementById('quantity').dispatchEvent(new Event('input'));
}

// Toggle add/remove button styles
document.querySelectorAll('input[name="adj_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('btn-add').classList.toggle('selected', this.value === 'add');
        document.getElementById('btn-remove').classList.toggle('selected', this.value === 'remove');
    });
});

document.getElementById('btn-add').addEventListener('click', function() {
    document.querySelector('input[value="add"]').checked = true;
    document.querySelector('input[value="add"]').dispatchEvent(new Event('change'));
});

document.getElementById('btn-remove').addEventListener('click', function() {
    document.querySelector('input[value="remove"]').checked = true;
    document.querySelector('input[value="remove"]').dispatchEvent(new Event('change'));
});

// Form validation
function validateAdjustmentForm() {
    const product = document.getElementById('product-select').value;
    const qty     = document.getElementById('quantity').value;
    const reason  = document.getElementById('reason-value').value;

    if (!product) { alert('Please select a product.'); return false; }
    if (!qty || parseInt(qty) < 1) { alert('Quantity must be at least 1.'); return false; }
    if (!reason) { alert('Please select a reason.'); return false; }
    return true;
}

// Auto-load product if pre-selected via URL
window.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('product-select');
    if (sel.value) loadProductInfo(sel);
});
</script>
</body>
</html>
