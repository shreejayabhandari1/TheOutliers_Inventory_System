<?php

if (!isset($products) || mysqli_num_rows($products) === 0): ?>
    <div style="text-align:center;padding:64px 20px">
        <div style="font-size:48px;margin-bottom:16px">📦</div>
        <h3 style="font-size:18px;margin-bottom:8px;color:var(--text-mid)">No products found</h3>
        <p style="font-size:14px;color:var(--text-light)">Try a different search or category.</p>
    </div>
<?php else: ?>
    <div class="products-grid">
        <?php while ($p = mysqli_fetch_assoc($products)):
            $imgFile = __DIR__ . '/../assets/images/products/' . $p['id'] . '.jpg';
            $hasImg  = file_exists($imgFile);
            $imgSrc  = $hasImg
                ? '/storehub/assets/images/products/' . $p['id'] . '.jpg'
                : '/storehub/assets/images/products/placeholder.php?id=' . $p['id'] . '&name=' . urlencode($p['name']);
        ?>
        <div class="product-card">
            <div style="height:160px;overflow:hidden;background:var(--cream-bg);border-bottom:1px solid var(--border)">
                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                     style="width:100%;height:100%;object-fit:<?= $hasImg ? 'cover' : 'contain' ?>;padding:<?= $hasImg ? '0' : '4px' ?>">
            </div>
            <div class="product-card-body">
                <div class="product-card-brand"><?= htmlspecialchars($p['brand'] ?? '') ?></div>
                <div class="product-card-name"><?= htmlspecialchars($p['name']) ?></div>
                <div style="font-size:12px;color:var(--text-light);margin-bottom:6px"><?= htmlspecialchars($p['cat_name'] ?? 'Uncategorized') ?></div>
                <div class="product-card-price">Rs. <?= number_format($p['selling_price'], 2) ?></div>
                <?php if ($p['stock'] == 0): ?>
                    <div style="margin-bottom:10px"><span class="badge badge-red" style="font-size:11px">Out of Stock</span></div>
                    <button class="btn btn-secondary" disabled style="width:100%;opacity:0.5">Out of Stock</button>
                <?php elseif ($p['stock'] <= $p['reorder_level']): ?>
                    <div style="margin-bottom:10px"><span class="badge badge-orange" style="font-size:11px">Only <?= $p['stock'] ?> left!</span></div>
                    <form action="/storehub/backend/orders/add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-primary" style="width:100%">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <div style="font-size:12px;color:var(--green);margin-bottom:10px">✓ In Stock (<?= $p['stock'] ?>)</div>
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
