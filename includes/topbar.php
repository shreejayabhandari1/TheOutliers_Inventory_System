<?php
require_once __DIR__ . '/session.php';
$initials     = getUserInitials();
$userName     = getUserName();
$currentSearch = htmlspecialchars($_GET['search'] ?? '');
$basePath      = strtok($_SERVER['REQUEST_URI'], '?');
?>
<link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&display=swap" rel="stylesheet">
<div class="topbar">

    <a href="/storehub/manager/dashboard.php" class="topbar-logo">
        <span class="topbar-logo-text">STOREHUB</span>
    </a>

    <div class="topbar-search-wrap">
        <svg class="topbar-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="topbar-search-input" class="topbar-search-input"
               placeholder="Search" value="<?= $currentSearch ?>"
               autocomplete="off" data-base="<?= htmlspecialchars($basePath) ?>"/>
    </div>

    <div class="topbar-right">
        <div class="topbar-bell" title="Notifications">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
        </div>
        <div class="topbar-avatar"><?= htmlspecialchars($initials) ?></div>
        <div class="topbar-user-info">
            <div class="topbar-user-name"><?= htmlspecialchars($userName) ?></div>
            <div class="topbar-user-role">Admin</div>
        </div>
    </div>
</div>
<script>
(function(){
    var input = document.getElementById('topbar-search-input');
    if (!input) return;
    var timer;
    input.addEventListener('input', function(){
        clearTimeout(timer);
        var q = input.value.trim();
        var base = input.dataset.base;
        timer = setTimeout(function(){
            fetch(base + '?search=' + encodeURIComponent(q) + '&ajax=1')
                .then(function(r){ return r.text(); })
                .then(function(html){
                    var tbody = document.getElementById('ajax-table-body');
                    if (tbody) tbody.innerHTML = html;
                })
                .catch(function(){
                    window.location.href = base + '?search=' + encodeURIComponent(q);
                });
        }, 350);
    });
    input.addEventListener('keydown', function(e){
        if (e.key === 'Enter') {
            clearTimeout(timer);
            window.location.href = input.dataset.base + '?search=' + encodeURIComponent(input.value.trim());
        }
    });
})();
</script>
