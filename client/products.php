<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireClient();

$search    = trim($_GET['search'] ?? '');
$catFilter = intval($_GET['cat'] ?? 0);
$isAjax    = isset($_GET['ajax']);

$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status='active' ORDER BY name");

$where  = "WHERE p.status = 'active'";
$params = [];
$types  = "";

if ($catFilter > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $catFilter;
    $types   .= "i";
}

if ($search !== '') {
    $where .= " AND (p.name LIKE ? OR p.brand LIKE ? OR p.model_number LIKE ?)";
    $like      = "%$search%";
    $params[]  = $like; $params[] = $like; $params[] = $like;
    $types    .= "sss";
}

$sql  = "SELECT p.*, c.name as cat_name
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         $where
         ORDER BY p.name";
$stmt = mysqli_prepare($conn, $sql);
if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$products = mysqli_stmt_get_result($stmt);
$productCount = mysqli_num_rows($products);

$catName = 'All Products';
if ($catFilter > 0) {
    $cr = mysqli_prepare($conn, "SELECT name FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($cr, "i", $catFilter);
    mysqli_stmt_execute($cr);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($cr));
    if ($row) $catName = $row['name'];
}

$success = $_GET['success'] ?? '';

if ($isAjax) {
    if ($productCount === 0) {
        echo '<div style="text-align:center;padding:60px;color:var(--text-light)">No products found.</div>';
    } else {
        echo '<div class="products-grid">';
        while ($p = mysqli_fetch_assoc($products)) {
            $imgHtml = !empty($p['image_url'])
                ? '<img src="'.htmlspecialchars($p['image_url']).'" alt="'.htmlspecialchars($p['name']).'" style="width:100%;height:100%;object-fit:cover">'
                : '<div class="product-img-placeholder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;opacity:.35"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg><span>No image</span></div>';
            if ($p['stock'] == 0) {
                $stockHtml = '<span class="badge badge-red" style="font-size:11px">Out of Stock</span>';
                $btnHtml   = '<button class="btn btn-secondary" disabled style="width:100%;opacity:0.5">Out of Stock</button>';
            } elseif ($p['stock'] <= $p['reorder_level']) {
                $stockHtml = '<span class="badge badge-orange" style="font-size:11px">Only '.$p['stock'].' left!</span>';
                $btnHtml   = '<form action="/storehub/backend/orders/add_to_cart.php" method="POST"><input type="hidden" name="product_id" value="'.$p['id'].'"><input type="hidden" name="quantity" value="1"><button type="submit" class="btn btn-primary" style="width:100%">Add to Cart</button></form>';
            } else {
                $stockHtml = '<div style="font-size:12px;color:var(--green)">✓ In Stock ('.$p['stock'].')</div>';
                $btnHtml   = '<form action="/storehub/backend/orders/add_to_cart.php" method="POST"><input type="hidden" name="product_id" value="'.$p['id'].'"><input type="hidden" name="quantity" value="1"><button type="submit" class="btn btn-primary" style="width:100%">Add to Cart</button></form>';
            }
            echo '<div class="product-card">
                <div class="product-img-wrap">'.$imgHtml.'</div>
                <div class="product-card-body">
                    <div class="product-card-brand">'.htmlspecialchars($p['brand'] ?? '').'</div>
                    <div class="product-card-name">'.htmlspecialchars($p['name']).'</div>
                    <div style="font-size:12px;color:var(--text-light);margin-bottom:6px">'.htmlspecialchars($p['cat_name'] ?? '').'</div>
                    <div class="product-card-price">Rs. '.number_format($p['selling_price'],2).'</div>
                    <div style="margin-bottom:10px">'.$stockHtml.'</div>
                    '.$btnHtml.'
                </div>
            </div>';
        }
        echo '</div>';
    }
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($catName) ?> – StoreHub</title><style>
        body { background: var(--cream-bg); }
        .client-wrap { max-width: 1200px; margin: 0 auto; padding: 28px; }

        .cat-pills { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:22px; }
        .cat-pill {
            padding: 7px 18px;
            border-radius: 20px;
            border: 1.5px solid var(--border);
            background: var(--white);
            color: var(--text-mid);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.18s;
            cursor: pointer;
        }
        .cat-pill:hover { border-color: var(--brown-accent); color: var(--brown-accent); }
        .cat-pill.active { background: var(--brown-accent); border-color: var(--brown-accent); color: white; }

        .product-img-wrap {
            height: 160px;
            background: var(--cream-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-bottom: 1px solid var(--border);
        }
        .product-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-img-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--text-light);
            font-size: 12px;
        }
        .product-img-placeholder svg {
            width: 48px;
            height: 48px;
            opacity: 0.35;
        }
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

    <?php if ($success): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Page heading -->
    <div style="margin-bottom:20px">
        <h1 style="font-size:22px;font-weight:700"><?= htmlspecialchars($catName) ?></h1>
        <p class="text-muted text-sm"><?= $productCount ?> product<?= $productCount != 1 ? 's' : '' ?> found</p>
    </div>

    <div class="cat-pills">
        <a href="/storehub/client/products.php<?= $search ? '?search='.urlencode($search) : '' ?>"
           class="cat-pill <?= $catFilter === 0 ? 'active' : '' ?>">All</a>

        <?php mysqli_data_seek($categories, 0); while ($cat = mysqli_fetch_assoc($categories)): ?>
            <a href="/storehub/client/products.php?cat=<?= $cat['id'] ?><?= $search ? '&search='.urlencode($search) : '' ?>"
               class="cat-pill <?= $catFilter === intval($cat['id']) ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <div id="products-grid-container">
    <?php if ($productCount === 0): ?>
        <div style="text-align:center;padding:80px 20px;background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border)">
            <div style="font-size:56px;margin-bottom:16px">📦</div>
            <h3 style="font-size:18px;color:var(--text-mid);margin-bottom:8px">No products found</h3>
            <p class="text-muted text-sm" style="margin-bottom:20px">
                <?= $catName !== 'All Products' ? 'No products in this category yet.' : 'Try a different search term.' ?>
            </p>
            <a href="/storehub/client/products.php" class="btn btn-primary">View All Products</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php while ($p = mysqli_fetch_assoc($products)): ?>
            <div class="product-card">

                <div class="product-img-wrap">
                    <?php if (!empty($p['image_url'])): ?>
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    <?php else: ?>
                        <div class="product-img-placeholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <path d="M21 15l-5-5L5 21"/>
                            </svg>
                            <span>No image</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="product-card-body">
                    <div class="product-card-brand"><?= htmlspecialchars($p['brand'] ?? '') ?></div>
                    <div class="product-card-name"><?= htmlspecialchars($p['name']) ?></div>
                    <div style="font-size:12px;color:var(--text-light);margin-bottom:6px">
                        <?= htmlspecialchars($p['cat_name'] ?? 'Uncategorized') ?>
                    </div>
                    <div class="product-card-price">Rs. <?= number_format($p['selling_price'], 2) ?></div>

                    <?php if ($p['stock'] == 0): ?>
                        <div style="margin-bottom:10px">
                            <span class="badge badge-red" style="font-size:11px">Out of Stock</span>
                        </div>
                        <button class="btn btn-secondary" disabled style="width:100%;opacity:0.5;cursor:not-allowed">
                            Out of Stock
                        </button>

                    <?php elseif ($p['stock'] <= $p['reorder_level']): ?>
                        <div style="margin-bottom:10px">
                            <span class="badge badge-orange" style="font-size:11px">Only <?= $p['stock'] ?> left!</span>
                        </div>
                        <form action="/storehub/backend/orders/add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-primary" style="width:100%">Add to Cart</button>
                        </form>

                    <?php else: ?>
                        <div style="font-size:12px;color:var(--green);margin-bottom:10px">
                            ✓ In Stock (<?= $p['stock'] ?>)
                        </div>
                        <form action="/storehub/backend/orders/add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-primary" style="width:100%">Add to Cart</button>
                        </form>
                    <?php endif; ?>
                </div>

            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
    </div>

</div>

<script src="/storehub/assets/js/main.js"></script>

</body>
</html>
