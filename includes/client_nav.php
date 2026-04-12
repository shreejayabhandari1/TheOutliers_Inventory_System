<?php
require_once __DIR__ . '/session.php';
$userName  = getUserName();
$initials  = getUserInitials();
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) $cartCount += $item['quantity'];
}
$currentSearch = htmlspecialchars($_GET['search'] ?? '');
?>
<link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&display=swap" rel="stylesheet">
<style>
.client-topbar {
  background: #fff;
  border-bottom: 1px solid var(--border);
  padding: 0 32px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 100;
  gap: 20px;
}

.client-topbar .topbar-logo {
  display: flex;
  align-items: center;
  text-decoration: none;
  flex-shrink: 0;
}

.client-topbar .topbar-logo-text {
  font-size: 18px;
  font-weight: 700;
  letter-spacing: .05em;
  color: var(--brown-accent);
  font-family: 'Comfortaa', cursive;
}

.client-topbar .topbar-search-wrap {
  flex: 1;
  max-width: 480px;
  position: relative;
  display: flex;
  align-items: center;
}

.client-topbar .topbar-search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-light);
  pointer-events: none;
}

.client-topbar .topbar-search-input {
  width: 100%;
  padding: 9px 14px 9px 38px;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: var(--text-dark);
  background: #fff;
  outline: none;
  transition: border-color .2s;
}

.client-topbar .topbar-search-input:focus {
  border-color: var(--brown-accent);
}

.client-topbar .topbar-search-input::placeholder {
  color: var(--text-light);
}

.client-topbar-right {
  display: flex;
  align-items: center;
  gap: 16px;
}
</style>
<nav class="client-topbar">

    <a href="/storehub/client/products.php" class="topbar-logo">
        <span class="topbar-logo-text">STOREHUB</span>
    </a>

    <div class="topbar-search-wrap">
        <svg class="topbar-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="client-search-input" class="topbar-search-input"
               placeholder="Search products…" value="<?= $currentSearch ?>" autocomplete="off"/>
    </div>

    <div class="client-topbar-right">
        <a href="/storehub/client/products.php" style="font-size:14px;color:<?= strpos($_SERVER['REQUEST_URI'],'products') ? 'var(--brown-accent)' : 'var(--text-mid)' ?>;font-weight:500">Products</a>
        <a href="/storehub/client/orders.php"   style="font-size:14px;color:<?= strpos($_SERVER['REQUEST_URI'],'orders')   ? 'var(--brown-accent)' : 'var(--text-mid)' ?>;font-weight:500">My Orders</a>

        <a href="/storehub/client/cart.php" style="position:relative;text-decoration:none">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-mid);display:block">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <?php if ($cartCount > 0): ?>
            <span style="position:absolute;top:-6px;right:-8px;background:var(--brown-accent);color:#fff;font-size:10px;font-weight:700;width:17px;height:17px;border-radius:50%;display:flex;align-items:center;justify-content:center"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>

        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:32px;height:32px;border-radius:50%;background:var(--brown-main);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:white">
                <?= htmlspecialchars($initials) ?>
            </div>
            <div>
                <div style="font-size:13.5px;font-weight:600;color:var(--text-dark);line-height:1.2"><?= htmlspecialchars($userName) ?></div>
                <div style="font-size:11px;color:var(--brown-accent);font-weight:500">Client</div>
            </div>
        </div>

        <a href="/storehub/backend/auth/logout.php" style="font-size:13px;color:var(--text-mid);font-weight:500">Logout</a>
    </div>
</nav>
<script>
(function(){
    var input = document.getElementById('client-search-input');
    if (!input) return;
    var timer;
    input.addEventListener('input', function(){
        clearTimeout(timer);
        var q = input.value.trim();
        timer = setTimeout(function(){
            fetch('/storehub/client/products.php?search=' + encodeURIComponent(q) + '&ajax=1')
                .then(function(r){ return r.text(); })
                .then(function(html){
                    var grid = document.getElementById('products-grid-container');
                    if (grid) grid.innerHTML = html;
                });
        }, 350);
    });
    input.addEventListener('keydown', function(e){
        if (e.key === 'Enter') {
            clearTimeout(timer);
            window.location.href = '/storehub/client/products.php?search=' + encodeURIComponent(input.value.trim());
        }
    });
})();
</script>
