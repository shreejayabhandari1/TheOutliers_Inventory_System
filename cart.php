<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireClient();

$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// BUG06 FIX: Validate cart items against DB on each load.
// Remove deactivated or out-of-stock products and warn the user.
$removedItems = [];
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $pid => $item) {
        $vStmt = mysqli_prepare($conn, "SELECT status, stock FROM products WHERE id = ?");
        mysqli_stmt_bind_param($vStmt, "i", $pid);
        mysqli_stmt_execute($vStmt);
        $vRow = mysqli_fetch_assoc(mysqli_stmt_get_result($vStmt));
        if (!$vRow || $vRow['status'] !== 'active') {
            $removedItems[] = htmlspecialchars($item['name']) . ' (no longer available)';
            unset($_SESSION['cart'][$pid]);
        } elseif ($vRow['stock'] == 0) {
            $removedItems[] = htmlspecialchars($item['name']) . ' (out of stock)';
            unset($_SESSION['cart'][$pid]);
        }
    }
}
if (!empty($removedItems)) {
    $error = 'Some items were removed from your cart: ' . implode(', ', $removedItems) . '.';
}

$cart  = $_SESSION['cart'];
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart – StoreHub</title>
    <link rel="stylesheet" href="/storehub/assets/css/animations.css"><style>
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
        --radius:8px;--radius-lg:12px;--sidebar-w:220px;
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
    .text-center{
        text-align:center;
    }
    .mt-4{
        margin-top:16px;}
    .mb-4{
        margin-bottom:16px;
    }
    .text-sm{
        font-size:13px;
    }
    .text-muted{
        color:var(--text-light);
    }
    .font-bold{
        font-weight:700;
    }
    .hidden{
        display:none;
    }

    .btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        padding:10px 20px;
        border-radius:var(--radius);
        font-family:'Inter',sans-serif;
        font-size:14px;
        font-weight:500;
        cursor:pointer;
        border:none;
        transition:all .2s;
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
        border:1.5px solid var(--border);}.btn-secondary:hover{background:var(--border);}
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
        width:100%;padding:12px;
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
    .form-group input,.form-group select,.form-group textarea{
        width:100%;
        padding:10px 14px;
        border:1.5px solid var(--border);
        border-radius:var(--radius);
        font-family:'Inter',sans-serif;
        font-size:14px;
        color:var(--text-dark);
        background:#fff;
        transition:border-color .2s;
        outline:none;
    }
    .form-group input:focus,.form-group select:focus,.form-group textarea:focus{
        border-color:var(--brown-accent);
    }
    .form-group input::placeholder,.form-group textarea::placeholder{
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
        cursor:pointer;
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
        font-size:13.5px;
        background:#fff;
        outline:none;
        font-family:'Inter',sans-serif;
        color:var(--text-dark);
    }
    .search-input-wrap input:focus{
        border-color:var(--brown-accent);
    }
    .search-icon{
        position:absolute;
        left:11px;top:50%;
        transform:translateY(-50%);
        color:var(--text-light);
        font-size:15px;
    }
    .filter-select{
        padding:9px 32px 9px 12px;
        border:1.5px solid var(--border);
        border-radius:var(--radius);font-size:13.5px;
        background:#fff;
        outline:none;
        font-family:'Inter',sans-serif;
        color:var(--text-dark);
        cursor:pointer;
        appearance:none;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%23A08060' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;
    }
    .alert{
        padding:12px 16px;
        border-radius:var(--radius);
        font-size:13.5px;
        margin-bottom:16px;
        display:flex;
        align-items:center;gap:10px;
    }
    .alert-success{
        background:var(--green-bg);
        color:var(--green);
        border:1px solid #B8DFC9;
    }
    .alert-error{
        background:var(--red-bg);
        color:var(--red);
        border:1px solid #F5C6C0;
    }
    .alert-warning{
        background:var(--orange-bg);
        color:var(--orange);
        border:1px solid #F5D99A;
    }

    .client-wrap{max-width:1200px;margin:0 auto;padding:28px;}
    .cat-pills{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:22px;}
    .cat-pill{padding:7px 18px;border-radius:20px;border:1.5px solid var(--border);background:#fff;color:var(--text-mid);font-size:13px;font-weight:500;text-decoration:none;transition:all .18s;cursor:pointer;}
    .cat-pill:hover{border-color:var(--brown-accent);color:var(--brown-accent);}
    .cat-pill.active{background:var(--brown-accent);border-color:var(--brown-accent);color:#fff;}
    .products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:18px;}
    .product-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--border);overflow:hidden;transition:box-shadow .2s,transform .2s;}
    .product-card:hover{box-shadow:var(--shadow-md);transform:translateY(-2px);}
    .product-card-body{padding:14px 16px;}
    .product-card-brand{font-size:11px;color:var(--text-light);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;}
    .product-card-name{font-size:14px;font-weight:600;color:var(--text-dark);margin-bottom:4px;}
    .product-card-price{font-size:18px;font-weight:700;color:var(--brown-accent);margin-bottom:10px;}
    .cart-summary{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--border);padding:20px;}
    .cart-item{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border);}
    .cart-item:last-child{border-bottom:none;}
    .cart-total{display:flex;justify-content:space-between;font-size:16px;font-weight:700;padding-top:12px;color:var(--text-dark);}
    @media(max-width:700px){.client-wrap{padding:16px;}.products-grid{grid-template-columns:1fr 1fr;}}
    @media(max-width:420px){.products-grid{grid-template-columns:1fr;}}

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

    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/client_nav.php'; ?>

<div class="client-wrap">
    <div class="page-header">
        <div>
            <h1>Your Cart</h1>
            <p class="subtitle"><?= count($cart) ?> item<?= count($cart) !== 1 ? 's' : '' ?></p>
        </div>
        <a href="/storehub/client/products.php" class="btn btn-secondary">← Continue Shopping</a>
    </div>

    <?php if ($success): ?><div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if (empty($cart)): ?>
        <!-- Empty cart state -->
        <div style="text-align:center;padding:80px 20px;background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border)">
            <div style="font-size:64px;margin-bottom:20px">🛒</div>
            <h2 style="font-size:20px;color:var(--text-mid);margin-bottom:10px">Your cart is empty</h2>
            <p class="text-muted text-sm" style="margin-bottom:24px">Browse our catalog and add products to get started.</p>
            <a href="/storehub/client/products.php" class="btn btn-primary">Browse Products</a>
        </div>

    <?php else: ?>
        <div style="display:grid;grid-template-columns:1fr 300px;gap:20px">

            <!-- Cart Items -->
            <div class="table-card">
                <div class="table-card-header">
                    <h3>Cart Items</h3>
                    <div style="display:flex;align-items:center;gap:14px">
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-mid);cursor:pointer">
                            <input type="checkbox" id="selectAll" style="accent-color:var(--brown-accent);width:15px;height:15px" checked>
                            Select All
                        </label>
                        <a href="/storehub/backend/orders/clear_cart.php" style="color:var(--red);font-size:13px">Clear all</a>
                    </div>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px"></th>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart as $productId => $item): ?>
                        <tr class="cart-row" data-pid="<?= $productId ?>" data-price="<?= $item['price'] ?>" data-qty="<?= $item['quantity'] ?>">
                            <td>
                                <input type="checkbox" class="item-check"
                                       data-pid="<?= $productId ?>"
                                       data-price="<?= $item['price'] ?>"
                                       data-qty="<?= $item['quantity'] ?>"
                                       style="accent-color:var(--brown-accent);width:15px;height:15px" checked>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                            </td>
                            <td>Rs. <?= number_format($item['price'], 2) ?></td>
                            <td>
                                <form action="/storehub/backend/orders/update_cart.php" method="POST"
                                      style="display:flex;align-items:center;gap:6px">
                                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                                    <input type="number" name="quantity"
                                           value="<?= $item['quantity'] ?>"
                                           min="1"
                                           style="width:90px;padding:5px 8px;border:1.5px solid var(--border);border-radius:6px;font-family:Inter,sans-serif">
                                    <button type="submit" class="btn btn-secondary btn-sm">↻</button>
                                </form>
                            </td>
                            <td><strong class="row-subtotal">Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
                            <td>
                                <a href="/storehub/backend/orders/remove_from_cart.php?id=<?= $productId ?>"
                                   style="color:var(--red);font-size:18px;text-decoration:none"
                                   title="Remove">✕</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>


            <div>
                <div class="cart-summary">
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">Order Summary</h3>

                    <div id="summaryItems">
                    <?php foreach ($cart as $pid => $item): ?>
                    <div class="cart-item summary-item" data-pid="<?= $pid ?>">
                        <span style="font-size:13px"><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                        <span style="font-size:13px;font-weight:600">Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                    </div>

                    <div class="cart-total">
                        <span>Selected Total</span>
                        <span id="dynamicTotal">Rs. <?= number_format($total, 2) ?></span>
                    </div>
                    <p id="noSelectionWarn" style="display:none;color:var(--red);font-size:12px;margin-top:6px">⚠ Please select at least one item.</p>

                    <div style="margin-top:20px">
                        <!-- Notes -->
                        <div class="form-group">
                            <label style="font-size:12px;font-weight:600;color:var(--text-mid);text-transform:uppercase;letter-spacing:.06em">
                                Order Notes (optional)
                            </label>
                            <textarea id="sharedNotes" rows="2"
                                      placeholder="Any special requests…"
                                      style="width:100%;padding:10px;border:1.5px solid var(--border);border-radius:var(--radius);font-family:Inter,sans-serif;font-size:13.5px;margin-bottom:14px;resize:vertical"></textarea>
                        </div>

                        <!-- Payment Method -->
                        <div style="margin-bottom:14px">
                            <label style="font-size:12px;font-weight:600;color:var(--text-mid);text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:10px">
                                Payment Method
                            </label>
                            <div style="display:flex;gap:10px">
                                <label id="lblCod" onclick="selectPayment('cod')"
                                       style="flex:1;border:2px solid var(--brown-accent);border-radius:8px;padding:10px 12px;cursor:pointer;display:flex;align-items:center;gap:8px;background:#fff5f2;transition:all .2s">
                                    <input type="radio" name="payMethod" value="cod" checked style="accent-color:var(--brown-accent)">
                                    <span style="font-size:13px;font-weight:600">💵 Cash on Delivery</span>
                                </label>
                                <label id="lblEsewa" onclick="selectPayment('esewa')"
                                       style="flex:1;border:2px solid var(--border);border-radius:8px;padding:10px 12px;cursor:pointer;display:flex;align-items:center;gap:8px;background:#fff;transition:all .2s">
                                    <input type="radio" name="payMethod" value="esewa" style="accent-color:#60bb46">
                                    <span style="font-size:13px;font-weight:600;color:#60bb46">🟢 eSewa</span>
                                </label>
                            </div>
                        </div>

                        <!-- COD Form -->
                        <form action="/storehub/backend/orders/place_order.php" method="POST" id="placeOrderForm">
                            <input type="hidden" name="notes" id="codNotes">
                            <input type="hidden" name="selected_items" id="codSelectedItems">
                            <button type="button" class="btn btn-primary" id="placeOrderBtn"
                                    style="width:100%;padding:13px;position:relative;overflow:hidden;transition:all .3s"
                                    onclick="submitOrder()">
                                <span id="poText">🛒 Place Order</span>
                                <span id="poSpinner" style="display:none">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite;vertical-align:middle"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                                    Processing…
                                </span>
                            </button>
                        </form>

                        <!-- eSewa Form -->
                        <form action="/storehub/backend/orders/esewa_initiate.php" method="POST" id="esewaForm" style="display:none">
                            <input type="hidden" name="notes" id="esewaNotesInput">
                            <input type="hidden" name="selected_items" id="esewaSelectedItems">
                            <button type="button"
                                    style="width:100%;padding:13px;border:none;border-radius:var(--radius);background:#60bb46;color:#fff;font-family:Inter,sans-serif;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .2s"
                                    onmouseover="this.style.background='#4ea336';this.style.animation='none'" onmouseout="this.style.background='#60bb46';this.style.animation='esewaGlow 1.8s ease infinite'"
                                    style="animation:esewaGlow 1.8s ease infinite"
                                    onclick="submitEsewa()">
                                🟢 Pay with eSewa (Test Gateway)
                            </button>

                            <!-- Divider -->
                            <div style="display:flex;align-items:center;gap:10px;margin:12px 0">
                                <div style="flex:1;height:1px;background:var(--border)"></div>
                                <span style="font-size:11px;color:var(--text-light);white-space:nowrap">or for offline demo</span>
                                <div style="flex:1;height:1px;background:var(--border)"></div>
                            </div>

                            <!-- Simulate button (demo only) -->
                            <button type="button"
                                    onclick="submitEsewaSimulate()"
                                    style="width:100%;padding:11px;border:1.5px dashed #60bb46;border-radius:var(--radius);background:#f0faf0;color:#3a8a2a;font-family:Inter,sans-serif;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .2s"
                                    onmouseover="this.style.background='#d8f5d8'" onmouseout="this.style.background='#f0faf0'">
                                🧪 Simulate eSewa Payment <span style="font-size:10px;font-weight:400;opacity:.7">(demo — no real gateway)</span>
                            </button>
                        </form>

                        <!-- Hidden simulate form -->
                        <form action="/storehub/backend/orders/esewa_simulate.php" method="POST" id="esewaSimulateForm" style="display:none">
                            <input type="hidden" name="notes" id="esewaSimulateNotesInput">
                            <input type="hidden" name="selected_items" id="esewaSimulateSelectedItems">
                        </form>

                        <p style="font-size:11px;color:var(--text-light);text-align:center;margin-top:10px">
                            Test credentials: ID <strong>9806800001</strong> · Pass <strong>Nepal@123</strong> · OTP <strong>123456</strong>
                        </p>
                    </div>

                    <!-- Order Placement Overlay Animation -->
                    <!-- Enhanced Order Placement Overlay with Confetti -->
                    <div id="orderOverlay" style="display:none;position:fixed;inset:0;background:rgba(248,237,235,.96);z-index:9999;backdrop-filter:blur(10px);flex-direction:column;align-items:center;justify-content:center;gap:24px">
                        <canvas id="confettiCanvas" style="position:absolute;inset:0;pointer-events:none;width:100%;height:100%;"></canvas>
                        <div id="orderAnim" style="width:140px;height:140px;position:relative;z-index:1">
                            <svg viewBox="0 0 120 120" style="width:140px;height:140px;filter:drop-shadow(0 4px 16px rgba(111,69,50,.18))">
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#F3D1CB" stroke-width="6"/>
                                <circle cx="60" cy="60" r="54" fill="none" stroke="var(--brown-accent)" stroke-width="6"
                                    stroke-dasharray="339.3" stroke-dashoffset="339.3"
                                    stroke-linecap="round" transform="rotate(-90 60 60)"
                                    id="orderProgressCircle" style="transition:stroke-dashoffset 1.8s cubic-bezier(.4,0,.2,1)"/>
                                <text x="60" y="68" text-anchor="middle" font-size="36" id="orderEmoji">&#x1F6CD;&#xFE0F;</text>
                            </svg>
                        </div>
                        <div id="orderSteps" style="display:flex;gap:12px;position:relative;z-index:1;align-items:center">
                            <div class="order-step active" id="step1"><div class="step-dot"></div><span>Verifying</span></div>
                            <div class="step-line"></div>
                            <div class="order-step" id="step2"><div class="step-dot"></div><span>Processing</span></div>
                            <div class="step-line"></div>
                            <div class="order-step" id="step3"><div class="step-dot"></div><span>Confirmed</span></div>
                        </div>
                        <div style="text-align:center;position:relative;z-index:1">
                            <div id="orderStatusText" style="font-size:22px;font-weight:700;color:var(--text-dark);transition:all .4s ease">Placing your order…</div>
                            <div id="orderSubText" style="font-size:14px;color:var(--text-mid);margin-top:8px;transition:all .4s ease">Checking stock &amp; confirming items</div>
                        </div>
                        <div style="display:flex;gap:8px;position:relative;z-index:1" id="orderDots">
                            <div style="width:9px;height:9px;border-radius:50%;background:var(--brown-accent);animation:dotBounce 1.2s ease-in-out infinite"></div>
                            <div style="width:9px;height:9px;border-radius:50%;background:var(--brown-accent);animation:dotBounce 1.2s ease-in-out .2s infinite"></div>
                            <div style="width:9px;height:9px;border-radius:50%;background:var(--brown-accent);animation:dotBounce 1.2s ease-in-out .4s infinite"></div>
                        </div>
                    </div>
                    <style>
                    @keyframes spin{to{transform:rotate(360deg)}}
                    @keyframes dotBounce{0%,80%,100%{transform:scale(.6);opacity:.5}40%{transform:scale(1);opacity:1}}
                    @keyframes checkPop{0%{opacity:0;transform:scale(0) rotate(-20deg)}70%{transform:scale(1.2) rotate(5deg)}100%{opacity:1;transform:scale(1) rotate(0deg)}}
                    @keyframes stepPop{0%{transform:scale(0)}70%{transform:scale(1.3)}100%{transform:scale(1)}}
                    .order-step{display:flex;flex-direction:column;align-items:center;gap:5px;font-size:11px;font-weight:600;color:rgba(111,69,50,.35);transition:color .4s;}
                    .order-step.active{color:var(--brown-accent);}
                    .order-step.done{color:var(--green);}
                    .step-dot{width:12px;height:12px;border-radius:50%;background:rgba(111,69,50,.2);transition:background .4s,transform .4s;}
                    .order-step.active .step-dot{background:var(--brown-accent);animation:stepPop .4s ease;}
                    .order-step.done .step-dot{background:var(--green);animation:stepPop .4s ease;}
                    .step-line{width:32px;height:2px;background:rgba(111,69,50,.15);border-radius:2px;margin-bottom:16px;}
                    </style>
                </div>
            </div>

        </div>
    <?php endif; ?>

</div>

<script src="/storehub/assets/js/main.js"></script>
<script>
// ── Confetti engine ──
function launchConfetti() {
    var canvas = document.getElementById('confettiCanvas');
    if (!canvas) return;
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    var ctx = canvas.getContext('2d');
    var colors = ['#BD7559','#6F4532','#F3D1CB','#2D7A4F','#4A72E8','#FFD700','#FF6B6B'];
    var pieces = [];
    for (var i = 0; i < 120; i++) {
        pieces.push({
            x: Math.random() * canvas.width,
            y: Math.random() * -canvas.height * 0.5,
            w: Math.random() * 10 + 5,
            h: Math.random() * 5 + 3,
            color: colors[Math.floor(Math.random() * colors.length)],
            rot: Math.random() * Math.PI * 2,
            rotSpeed: (Math.random() - 0.5) * 0.15,
            vx: (Math.random() - 0.5) * 4,
            vy: Math.random() * 3 + 2,
            opacity: 1
        });
    }
    var startTime = Date.now();
    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        var elapsed = Date.now() - startTime;
        var alive = false;
        pieces.forEach(function(p) {
            p.x += p.vx;
            p.y += p.vy;
            p.rot += p.rotSpeed;
            p.vy += 0.08;
            if (elapsed > 1200) p.opacity = Math.max(0, p.opacity - 0.012);
            if (p.y < canvas.height + 20 && p.opacity > 0) alive = true;
            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rot);
            ctx.globalAlpha = p.opacity;
            ctx.fillStyle = p.color;
            ctx.fillRect(-p.w/2, -p.h/2, p.w, p.h);
            ctx.restore();
        });
        if (alive) requestAnimationFrame(draw);
        else ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
    draw();
}

function setStep(num) {
    for (var i = 1; i <= 3; i++) {
        var el = document.getElementById('step' + i);
        if (!el) continue;
        el.classList.remove('active', 'done');
        if (i < num) el.classList.add('done');
        else if (i === num) el.classList.add('active');
    }
}

// ── Payment method toggle ────────────────────────────────────────
function selectPayment(method) {
    var lblCod   = document.getElementById('lblCod');
    var lblEsewa = document.getElementById('lblEsewa');
    var codForm  = document.getElementById('placeOrderForm');
    var esewaForm= document.getElementById('esewaForm');
    if (method === 'esewa') {
        lblEsewa.style.border = '2px solid #60bb46';
        lblEsewa.style.background = '#f0fce8';
        lblCod.style.border = '2px solid var(--border)';
        lblCod.style.background = '#fff';
        codForm.style.display   = 'none';
        esewaForm.style.display = '';
    } else {
        lblCod.style.border = '2px solid var(--brown-accent)';
        lblCod.style.background = '#fff5f2';
        lblEsewa.style.border = '2px solid var(--border)';
        lblEsewa.style.background = '#fff';
        codForm.style.display   = '';
        esewaForm.style.display = 'none';
    }
}

// ── Checkbox selection logic ──
function getSelectedIds() {
    return Array.from(document.querySelectorAll('.item-check:checked')).map(cb => cb.getAttribute('data-pid'));
}

function updateSummary() {
    var checks = document.querySelectorAll('.item-check');
    var total = 0;
    checks.forEach(function(cb) {
        var pid = cb.getAttribute('data-pid');
        var summaryItem = document.querySelector('.summary-item[data-pid="' + pid + '"]');
        var row = document.querySelector('.cart-row[data-pid="' + pid + '"]');
        if (cb.checked) {
            total += parseFloat(cb.getAttribute('data-price')) * parseInt(cb.getAttribute('data-qty'));
            if (summaryItem) summaryItem.style.display = '';
            if (row) row.style.opacity = '1';
        } else {
            if (summaryItem) summaryItem.style.display = 'none';
            if (row) row.style.opacity = '0.45';
        }
    });
    document.getElementById('dynamicTotal').textContent = 'Rs. ' + total.toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2});
    var warn = document.getElementById('noSelectionWarn');
    if (warn) warn.style.display = getSelectedIds().length === 0 ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Select All toggle
    var selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.item-check').forEach(cb => cb.checked = this.checked);
            updateSummary();
        });
    }
    document.querySelectorAll('.item-check').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var allChecked = Array.from(document.querySelectorAll('.item-check')).every(c => c.checked);
            if (selectAll) selectAll.checked = allChecked;
            updateSummary();
        });
    });
    updateSummary();
});

function submitOrder() {
    var selected = getSelectedIds();
    if (selected.length === 0) {
        var warn = document.getElementById('noSelectionWarn');
        if (warn) warn.style.display = '';
        return;
    }
    document.getElementById('codNotes').value = document.getElementById('sharedNotes').value;
    document.getElementById('codSelectedItems').value = selected.join(',');
    animatePlaceOrder(true);
}

function submitEsewa() {
    var selected = getSelectedIds();
    if (selected.length === 0) {
        var warn = document.getElementById('noSelectionWarn');
        if (warn) warn.style.display = '';
        return;
    }
    document.getElementById('esewaNotesInput').value = document.getElementById('sharedNotes').value;
    document.getElementById('esewaSelectedItems').value = selected.join(',');
    document.getElementById('esewaForm').submit();
}

function submitEsewaSimulate() {
    var selected = getSelectedIds();
    if (selected.length === 0) {
        var warn = document.getElementById('noSelectionWarn');
        if (warn) warn.style.display = '';
        return;
    }
    document.getElementById('esewaSimulateNotesInput').value = document.getElementById('sharedNotes').value;
    document.getElementById('esewaSimulateSelectedItems').value = selected.join(',');
    document.getElementById('esewaSimulateForm').submit();
}

function animatePlaceOrder(doSubmit) {
    var overlay = document.getElementById('orderOverlay');
    var btn = document.getElementById('placeOrderBtn');
    var poText = document.getElementById('poText');
    var poSpinner = document.getElementById('poSpinner');
    overlay.style.display = 'flex';
    poText.style.display = 'none';
    poSpinner.style.display = 'inline-flex';
    btn.disabled = true;
    setStep(1);

    // Start progress circle
    setTimeout(function(){
        var circle = document.getElementById('orderProgressCircle');
        if (circle) circle.style.strokeDashoffset = '0';
    }, 100);

    // Stage 2: Processing
    setTimeout(function(){
        setStep(2);
        document.getElementById('orderStatusText').textContent = 'Confirming your order…';
        document.getElementById('orderSubText').textContent = 'Updating inventory & creating record';
        document.getElementById('orderEmoji').textContent = '📦';
    }, 900);

    // Stage 3: Success + confetti
    setTimeout(function(){
        setStep(3);
        document.getElementById('orderStatusText').textContent = 'Order placed! 🎉';
        document.getElementById('orderSubText').textContent = 'Redirecting to your orders…';
        document.getElementById('orderEmoji').textContent = '✅';
        document.getElementById('orderDots').style.display = 'none';
        var circle = document.getElementById('orderProgressCircle');
        if (circle) { circle.style.stroke = '#2D7A4F'; }
        launchConfetti();
    }, 1900);

    // Submit
    setTimeout(function(){
        document.getElementById('placeOrderForm').submit();
    }, 3000);
}
</script>
</body>
</html>
