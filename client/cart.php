<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireClient();

$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
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
    <title>Cart – StoreHub</title><style>
        body { background: var(--cream-bg); }
        .client-wrap { max-width: 900px; margin: 0 auto; padding: 28px; }
    </style>
    <style>

    /* Import Font */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

/* Root Variables */
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

/* Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body */
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

/* Utility Classes */
.text-center { text-align: center; }
.mt-4 { margin-top: 16px; }
.mb-4 { margin-bottom: 16px; }

.text-sm { font-size: 13px; }
.text-muted { color: var(--text-light); }

.font-bold { font-weight: 700; }
.hidden { display: none; }

/* Buttons */
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

/* Forms */
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

/* Checkbox Row */
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

/* Filter Bar */
.filter-bar {
    display: flex;
    align-items: center;
    gap: 12px;

    margin-bottom: 18px;
    flex-wrap: wrap;
}

/* Search Input */
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

/* Alerts */
.alert {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 12px 16px;

    border-radius: var(--radius);

    font-size: 13.5px;
    margin-bottom: 16px;
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

/* Product Grid */
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

/* Product Cards */
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

/* Responsive */
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
                    <a href="/storehub/backend/orders/clear_cart.php" style="color:var(--red);font-size:13px">Clear all</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart as $productId => $item): ?>
                        <tr>
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
                                           min="1" max="<?= $item['max_stock'] ?>"
                                           style="width:60px;padding:5px 8px;border:1.5px solid var(--border);border-radius:6px;font-family:Inter,sans-serif">
                                    <button type="submit" class="btn btn-secondary btn-sm">↻</button>
                                </form>
                            </td>
                            <td><strong>Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
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

                    <?php foreach ($cart as $item): ?>
                    <div class="cart-item">
                        <span style="font-size:13px"><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                        <span style="font-size:13px;font-weight:600">Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>

                    <div class="cart-total">
                        <span>Total</span>
                        <span>Rs. <?= number_format($total, 2) ?></span>
                    </div>

                    <div style="margin-top:20px">
                        <div class="form-group">
                            <label style="font-size:12px;font-weight:600;color:var(--text-mid);text-transform:uppercase;letter-spacing:.06em">
                                Order Notes (optional)
                            </label>
                            <form action="/storehub/backend/orders/place_order.php" method="POST">
                                <textarea name="notes" rows="2"
                                          placeholder="Any special requests…"
                                          style="width:100%;padding:10px;border:1.5px solid var(--border);border-radius:var(--radius);font-family:Inter,sans-serif;font-size:13.5px;margin-bottom:12px;resize:vertical"></textarea>
                                <button type="submit" class="btn btn-primary" style="width:100%;padding:13px"
                                        onclick="return confirm('Place this order?')">
                                    Place Order →
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>

</div>

<script src="/storehub/assets/js/main.js"></script>
</body>
</html>
