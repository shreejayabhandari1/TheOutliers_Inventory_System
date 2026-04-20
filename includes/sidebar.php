<?php

require_once __DIR__ . '/session.php';
$initials = getUserInitials();
$userName = getUserName();
?>
<aside class="sidebar">

    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Overview</div>
        <a href="/storehub/manager/dashboard.php" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">⊞</span> Dashboard
        </a>

        <div class="sidebar-section-label">Inventory</div>
        <a href="/storehub/manager/products.php" class="<?= ($active ?? '') === 'products' ? 'active' : '' ?>">
            <span class="nav-icon">📦</span> Products
        </a>
        <a href="/storehub/manager/categories.php" class="<?= ($active ?? '') === 'categories' ? 'active' : '' ?>">
            <span class="nav-icon">🏷</span> Categories
        </a>
        <a href="/storehub/manager/suppliers.php" class="<?= ($active ?? '') === 'suppliers' ? 'active' : '' ?>">
            <span class="nav-icon">🏭</span> Suppliers
        </a>

        <div class="sidebar-section-label">Operations</div>
        <a href="/storehub/manager/stock_adjustment.php" class="<?= ($active ?? '') === 'stock' ? 'active' : '' ?>">
            <span class="nav-icon">↕</span> Stock Adjustment
        </a>
        <a href="/storehub/manager/audit_log.php" class="<?= ($active ?? '') === 'audit' ? 'active' : '' ?>">
            <span class="nav-icon">📋</span> Audit Log
        </a>

        <div class="sidebar-section-label">Clients</div>
        <a href="/storehub/manager/orders.php" class="<?= ($active ?? '') === 'orders' ? 'active' : '' ?>">
            <span class="nav-icon">🛒</span> Orders
        </a>
        <a href="/storehub/manager/clients.php" class="<?= ($active ?? '') === 'clients' ? 'active' : '' ?>">
            <span class="nav-icon">👥</span> Clients
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-avatar"><?= htmlspecialchars($initials) ?></div>
        <div class="sidebar-user-info">
            <div class="name"><?= htmlspecialchars($userName) ?></div>
            <div class="role">Administrator</div>
        </div>
        <a href="/storehub/backend/auth/logout.php" class="sidebar-logout" title="Logout">Logout</a>
    </div>
</aside>
