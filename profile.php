<?php
// ============================================
// Client – Profile Page (View + Edit + Photo)
// ============================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireClient();

$clientId = $_SESSION['user_id'];
$success  = '';
$error    = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $newPass = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($newPass && $newPass !== $confirm) {
        $error = 'New passwords do not match.';
    } elseif ($newPass && strlen($newPass) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $chk = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($chk, "si", $email, $clientId);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            $error = 'This email is already in use by another account.';
        } else {
            if ($newPass) {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $upd  = mysqli_prepare($conn, "UPDATE users SET name=?, email=?, phone=?, password=? WHERE id=?");
                mysqli_stmt_bind_param($upd, "ssssi", $name, $email, $phone, $hash, $clientId);
            } else {
                $upd = mysqli_prepare($conn, "UPDATE users SET name=?, email=?, phone=? WHERE id=?");
                mysqli_stmt_bind_param($upd, "sssi", $name, $email, $phone, $clientId);
            }
            mysqli_stmt_execute($upd);
            $_SESSION['name'] = $name;
            $success = 'Profile updated successfully!';
        }
    }
}

// Fetch current user data
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $clientId);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Order stats
$stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total_orders,
           COALESCE(SUM(total_amount),0) as total_spent,
           SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
           SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending
    FROM orders WHERE client_id = $clientId
"));

// Recent orders
$recentOrders = mysqli_query($conn, "
    SELECT o.id, o.total_amount, o.status, o.created_at, COUNT(oi.id) as items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.client_id = $clientId
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
");

// Monthly spending (last 12 months)
$monthlySpend = mysqli_query($conn, "
    SELECT DATE_FORMAT(created_at,'%b %Y') as month_label,
           DATE_FORMAT(created_at,'%Y-%m') as month_key,
           COALESCE(SUM(total_amount),0) as total
    FROM orders
    WHERE client_id = $clientId AND status != 'cancelled'
      AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY month_key
    ORDER BY month_key ASC
");
$spendLabels = [];
$spendData   = [];
while ($row = mysqli_fetch_assoc($monthlySpend)) {
    $spendLabels[] = $row['month_label'];
    $spendData[]   = (float)$row['total'];
}

$hasPhoto = !empty($user['profile_photo']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – StoreHub</title>
    <link rel="stylesheet" href="/storehub/assets/css/animations.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    :root{
        --brown-dark:#6F4532;--brown-main:#6F4532;--brown-light:#BD7559;--brown-accent:#BD7559;
        --cream-bg:#F8EDEB;--cream-light:#F5E8E4;--white:#FFFFFF;
        --text-dark:#2D1A10;--text-mid:#6F4532;--text-light:#BD7559;--border:#F3D1CB;
        --green:#2D7A4F;--green-bg:#E8F5EE;--red:#C0392B;--red-bg:#FDECEA;
        --orange:#D4750A;--orange-bg:#FEF3E2;--blue:#4A72E8;--blue-bg:#EEF3FF;
        --shadow:0 2px 8px rgba(90,40,15,.10);--shadow-md:0 4px 16px rgba(90,40,15,.14);
        --radius:8px;--radius-lg:14px;
    }
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Inter',sans-serif;background:var(--cream-bg);color:var(--text-dark);font-size:14px;line-height:1.5;}
    a{text-decoration:none;color:inherit;}
    .hidden{display:none;}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 20px;border-radius:var(--radius);font-family:'Inter',sans-serif;font-size:14px;font-weight:500;cursor:pointer;border:none;transition:all .2s;}
    .btn-primary{background:var(--brown-accent);color:#fff;}.btn-primary:hover{background:var(--brown-main);transform:translateY(-1px);box-shadow:0 4px 12px rgba(111,69,50,.25);}
    .btn-secondary{background:#fff;color:var(--text-dark);border:1.5px solid var(--border);}.btn-secondary:hover{border-color:var(--brown-accent);color:var(--brown-accent);}
    .btn-sm{padding:6px 14px;font-size:13px;}
    .form-group{margin-bottom:18px;}
    .form-group label{display:block;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--text-mid);margin-bottom:6px;}
    .form-group input{width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:var(--radius);font-family:'Inter',sans-serif;font-size:14px;color:var(--text-dark);background:#fff;transition:border-color .2s,box-shadow .2s;outline:none;}
    .form-group input:focus{border-color:var(--brown-accent);box-shadow:0 0 0 3px rgba(189,117,89,.12);}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .alert{padding:12px 16px;border-radius:var(--radius);font-size:13.5px;margin-bottom:16px;display:flex;align-items:center;gap:10px;}
    .alert-success{background:var(--green-bg);color:var(--green);border:1px solid #B8DFC9;animation:slideDown .35s ease;}
    .alert-error{background:var(--red-bg);color:var(--red);border:1px solid #F5C6C0;animation:slideDown .35s ease;}
    @keyframes slideDown{0%{opacity:0;transform:translateY(-10px)}100%{opacity:1;transform:translateY(0)}}
    .badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;}
    .badge-green{background:var(--green-bg);color:var(--green);}
    .badge-orange{background:var(--orange-bg);color:var(--orange);}
    .badge-red{background:var(--red-bg);color:var(--red);}
    .badge-gray{background:#F0F0F0;color:#666;}
    .badge-blue{background:var(--blue-bg);color:var(--blue);}

    /* ── Page Layout ── */
    .client-wrap{max-width:960px;margin:0 auto;padding:32px 24px;}
    .page-header{margin-bottom:28px;animation:fadeUp .4s ease;}
    @keyframes fadeUp{0%{opacity:0;transform:translateY(16px)}100%{opacity:1;transform:translateY(0)}}
    .profile-grid{display:grid;grid-template-columns:340px 1fr;gap:22px;align-items:start;}
    @media(max-width:800px){.profile-grid{grid-template-columns:1fr;}.form-row{grid-template-columns:1fr;}}

    /* ── Profile Card ── */
    .profile-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--border);overflow:hidden;animation:fadeUp .45s ease .05s both;}
    .profile-card-hero{background:linear-gradient(135deg,var(--brown-main),var(--brown-accent));padding:32px 24px 20px;text-align:center;position:relative;overflow:hidden;}
    .profile-card-hero::before{content:'';position:absolute;top:-30px;right:-30px;width:120px;height:120px;background:rgba(255,255,255,.08);border-radius:50%;}
    .profile-card-hero::after{content:'';position:absolute;bottom:-20px;left:-20px;width:80px;height:80px;background:rgba(255,255,255,.06);border-radius:50%;}

    /* ── Avatar with photo upload ── */
    .avatar-wrap{position:relative;width:88px;height:88px;margin:0 auto 14px;z-index:1;}
    .profile-big-avatar{width:88px;height:88px;border-radius:50%;background:rgba(255,255,255,.2);border:3px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:#fff;overflow:hidden;transition:transform .3s,box-shadow .3s;}
    .profile-big-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
    .avatar-edit-btn{position:absolute;bottom:0;right:0;width:28px;height:28px;border-radius:50%;background:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.18);transition:all .2s;font-size:13px;}
    .avatar-edit-btn:hover{background:var(--brown-accent);transform:scale(1.12);}
    .avatar-edit-btn:hover span{filter:brightness(10);}
    .avatar-upload-input{display:none;}
    .avatar-upload-progress{position:absolute;inset:0;border-radius:50%;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.5);}
    .avatar-upload-progress svg{animation:spin 1s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg)}}
    .avatar-ring{position:absolute;inset:-3px;border-radius:50%;border:3px solid transparent;transition:border-color .3s;}
    .avatar-wrap:hover .avatar-ring{border-color:rgba(255,255,255,.5);animation:ringPulse 1.5s ease infinite;}
    @keyframes ringPulse{0%,100%{opacity:.5;transform:scale(1)}50%{opacity:1;transform:scale(1.04)}}

    .profile-hero-name{font-size:18px;font-weight:700;color:#fff;margin-bottom:4px;position:relative;z-index:1;}
    .profile-hero-email{font-size:12.5px;color:rgba(255,255,255,.75);position:relative;z-index:1;}
    .profile-hero-badge{margin-top:10px;position:relative;z-index:1;}
    .profile-hero-badge span{background:rgba(255,255,255,.2);color:#fff;font-size:11px;font-weight:600;padding:4px 12px;border-radius:20px;backdrop-filter:blur(4px);}
    .profile-stats{display:grid;grid-template-columns:1fr 1fr;gap:0;border-top:1px solid var(--border);}
    .pstat{padding:16px 14px;text-align:center;border-right:1px solid var(--border);}
    .pstat:last-child,.pstat:nth-child(2){border-right:none;}
    .pstat:nth-child(3),.pstat:nth-child(4){border-top:1px solid var(--border);}
    .pstat-val{font-size:22px;font-weight:700;color:var(--text-dark);line-height:1;}
    .pstat-val.green{color:var(--green);}
    .pstat-val.orange{color:var(--orange);}
    .pstat-label{font-size:11px;color:var(--text-light);margin-top:4px;font-weight:500;}
    .profile-joined{padding:14px 20px;border-top:1px solid var(--border);font-size:12.5px;color:var(--text-light);text-align:center;}

    /* ── Edit Form + History ── */
    .edit-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--border);padding:24px;animation:fadeUp .45s ease .1s both;}
    .edit-card h3{font-size:15px;font-weight:700;margin-bottom:20px;color:var(--text-dark);display:flex;align-items:center;gap:8px;}
    .section-divider{height:1px;background:var(--border);margin:22px 0;}
    .tab-row{display:flex;gap:4px;background:var(--cream-bg);border-radius:10px;padding:4px;margin-bottom:22px;}
    .tab-btn{flex:1;padding:8px;border-radius:7px;border:none;background:none;font-family:'Inter',sans-serif;font-size:13px;font-weight:500;color:var(--text-mid);cursor:pointer;transition:all .25s;}
    .tab-btn.active{background:#fff;color:var(--brown-accent);box-shadow:0 1px 4px rgba(90,40,15,.1);font-weight:600;}
    .tab-btn:not(.active):hover{background:rgba(255,255,255,.5);}
    .tab-panel{display:none;animation:fadeUp .25s ease;}
    .tab-panel.active{display:block;}

    /* ── Order History Table ── */
    .history-table{width:100%;border-collapse:collapse;margin-top:4px;}
    .history-table th{background:var(--cream-bg);padding:9px 14px;text-align:left;font-size:11px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--text-mid);border-bottom:1px solid var(--border);}
    .history-table td{padding:11px 14px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle;}
    .history-table tr:last-child td{border-bottom:none;}
    .history-table tr{transition:background .15s;}
    .history-table tr:hover td{background:var(--cream-bg);}

    /* ── Spending Chart ── */
    .chart-wrap{padding:16px 0 8px;position:relative;height:160px;display:flex;align-items:flex-end;gap:6px;}
    .chart-bar-wrap{flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;height:100%;}
    .chart-bar-outer{flex:1;display:flex;align-items:flex-end;width:100%;}
    .chart-bar{width:100%;border-radius:6px 6px 0 0;background:linear-gradient(180deg,var(--brown-accent),var(--brown-main));min-height:4px;transition:height .8s cubic-bezier(.22,.68,0,1.2);position:relative;cursor:pointer;}
    .chart-bar::after{content:attr(data-val);position:absolute;top:-22px;left:50%;transform:translateX(-50%);font-size:9px;font-weight:700;color:var(--text-mid);white-space:nowrap;opacity:0;transition:opacity .2s;}
    .chart-bar:hover{background:linear-gradient(180deg,#d4875e,var(--brown-accent));}
    .chart-bar:hover::after{opacity:1;}
    .chart-label{font-size:9.5px;color:var(--text-light);text-align:center;line-height:1.2;white-space:nowrap;overflow:hidden;max-width:40px;text-overflow:ellipsis;}
    .no-data{text-align:center;padding:40px;color:var(--text-light);font-size:13px;}

    /* ── Photo toast ── */
    .photo-toast{position:fixed;bottom:28px;right:28px;background:#fff;border:1px solid var(--border);border-radius:12px;padding:14px 20px;box-shadow:var(--shadow-md);display:flex;align-items:center;gap:12px;font-size:13.5px;font-weight:500;z-index:9999;transform:translateY(80px);opacity:0;transition:all .4s cubic-bezier(.22,.68,0,1.2);}
    .photo-toast.show{transform:translateY(0);opacity:1;}
    .photo-toast.success{border-left:4px solid var(--green);color:var(--green);}
    .photo-toast.error{border-left:4px solid var(--red);color:var(--red);}

    /* ── Extra micro-animations ── */
    .pstat{transition:background .2s;}
    .pstat:hover{background:var(--cream-bg);}
    .profile-joined{transition:background .2s;}
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/client_nav.php'; ?>

<div class="client-wrap">
    <div class="page-header">
        <h1 style="font-size:24px;font-weight:700;color:var(--text-dark)">My Profile</h1>
        <p style="font-size:13px;color:var(--text-mid);margin-top:3px">Manage your account and view your order history</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error">✕ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-grid">
        <!-- Left: Profile Summary Card -->
        <div class="profile-card">
            <div class="profile-card-hero">
                <!-- Avatar with photo upload -->
                <div class="avatar-wrap" id="avatarWrap" title="Click the ✏️ button to change photo">
                    <div class="avatar-ring" id="avatarRing"></div>
                    <div class="profile-big-avatar" id="avatarDisplay">
                        <?php if ($hasPhoto): ?>
                            <img src="<?= htmlspecialchars($user['profile_photo']) ?>?v=<?= time() ?>" alt="Profile photo" id="avatarImg">
                        <?php else: ?>
                            <span id="avatarInitials"><?= htmlspecialchars(getUserInitials()) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="avatar-upload-progress" id="avatarProgress">
                        <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    </div>
                    <input type="file" accept="image/*" class="avatar-upload-input" id="avatarInput">
                    <button class="avatar-edit-btn" type="button" onclick="document.getElementById('avatarInput').click()" title="Change photo">
                        <span>✏️</span>
                    </button>
                </div>
                <div class="profile-hero-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="profile-hero-email"><?= htmlspecialchars($user['email']) ?></div>
                <div class="profile-hero-badge"><span>✦ Client Member</span></div>
            </div>
            <div class="profile-stats">
                <div class="pstat">
                    <div class="pstat-val" id="psTotal" data-count="<?= $stats['total_orders'] ?>"><?= $stats['total_orders'] ?></div>
                    <div class="pstat-label">Total Orders</div>
                </div>
                <div class="pstat">
                    <div class="pstat-val green" id="psCompleted" data-count="<?= $stats['completed'] ?>"><?= $stats['completed'] ?></div>
                    <div class="pstat-label">Completed</div>
                </div>
                <div class="pstat">
                    <div class="pstat-val orange" id="psPending" data-count="<?= $stats['pending'] ?>"><?= $stats['pending'] ?></div>
                    <div class="pstat-label">Pending</div>
                </div>
                <div class="pstat">
                    <div class="pstat-val" style="font-size:15px">Rs.<?= number_format($stats['total_spent'], 0) ?></div>
                    <div class="pstat-label">Total Spent</div>
                </div>
            </div>
            <div class="profile-joined">
                🗓 Member since <?= date('F Y', strtotime($user['created_at'])) ?>
            </div>
        </div>

        <!-- Right: Tabs -->
        <div class="edit-card">
            <div class="tab-row">
                <button class="tab-btn active" onclick="switchTab('edit',this)">✏️ Edit Profile</button>
                <button class="tab-btn" onclick="switchTab('history',this)">📋 Order History</button>
                <button class="tab-btn" onclick="switchTab('spending',this)">📊 Spending</button>
            </div>

            <!-- Tab: Edit Profile -->
            <div class="tab-panel active" id="tab-edit">
                <form method="POST" id="profileForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+977 9800000000">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="section-divider"></div>
                    <p style="font-size:12px;color:var(--text-light);margin-bottom:14px;">Leave password fields blank to keep current password</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" placeholder="Min. 8 characters" autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" placeholder="Repeat password" autocomplete="new-password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%" id="saveProfileBtn">Save Changes →</button>
                </form>
            </div>

            <!-- Tab: Order History -->
            <div class="tab-panel" id="tab-history">
                <?php if (mysqli_num_rows($recentOrders) === 0): ?>
                    <div class="no-data">📋 No orders yet — <a href="/storehub/client/products.php" style="color:var(--brown-accent)">start shopping</a></div>
                <?php else: ?>
                <table class="history-table">
                    <thead>
                        <tr><th>Order</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php while ($o = mysqli_fetch_assoc($recentOrders)): ?>
                        <tr style="animation:fadeUp .3s ease">
                            <td><strong>#<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= $o['items'] ?></td>
                            <td><strong>Rs. <?= number_format($o['total_amount'], 2) ?></strong></td>
                            <td>
                                <?php $bmap=['pending'=>'badge-orange','processing'=>'badge-blue','completed'=>'badge-green','cancelled'=>'badge-red']; ?>
                                <span class="badge <?= $bmap[$o['status']] ?? 'badge-gray' ?>"><?= ucfirst($o['status']) ?></span>
                            </td>
                            <td style="color:var(--text-light)"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                            <td><a href="/storehub/client/order_detail.php?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <div style="text-align:center;padding:14px 0 4px">
                    <a href="/storehub/client/orders.php" class="btn btn-secondary btn-sm">View all orders →</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tab: Spending Chart -->
            <div class="tab-panel" id="tab-spending">
                <p style="font-size:13px;color:var(--text-mid);margin-bottom:16px;">Monthly spending over the last 12 months</p>
                <?php if (empty($spendLabels)): ?>
                    <div class="no-data">📊 No spending data yet</div>
                <?php else: ?>
                <div class="chart-wrap" id="spendChart"></div>
                <div style="text-align:center;margin-top:8px">
                    <span style="font-size:12px;color:var(--text-light)">Total all-time: <strong style="color:var(--brown-accent)">Rs. <?= number_format(array_sum($spendData), 2) ?></strong></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Photo toast notification -->
<div class="photo-toast" id="photoToast"></div>

<script src="/storehub/assets/js/main.js"></script>
<script>
// ── Tab switching ──
function switchTab(id, btn) {
    document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
    document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
    document.getElementById('tab-' + id).classList.add('active');
    btn.classList.add('active');
    if (id === 'spending') renderChart();
}

// ── Save button animation is handled in the photo upload submit block below ──

// ── Spending Bar Chart ──
var chartLabels = <?= json_encode($spendLabels) ?>;
var chartData   = <?= json_encode($spendData) ?>;
var chartRendered = false;

function renderChart() {
    if (chartRendered || !chartLabels.length) return;
    chartRendered = true;
    var container = document.getElementById('spendChart');
    if (!container) return;
    var max = Math.max.apply(null, chartData) || 1;
    container.innerHTML = '';
    chartLabels.forEach(function(label, i) {
        var val = chartData[i];
        var pct = (val / max) * 100;
        var wrap = document.createElement('div'); wrap.className = 'chart-bar-wrap';
        var outer = document.createElement('div'); outer.className = 'chart-bar-outer';
        var bar = document.createElement('div'); bar.className = 'chart-bar';
        bar.setAttribute('data-val', 'Rs.' + Math.round(val));
        bar.style.height = '0%'; bar.style.width = '100%';
        outer.appendChild(bar);
        var lbl = document.createElement('div'); lbl.className = 'chart-label'; lbl.textContent = label;
        wrap.appendChild(outer); wrap.appendChild(lbl);
        container.appendChild(wrap);
        setTimeout(function() { bar.style.height = pct + '%'; }, 80 + i * 60);
    });
}

// ── Animate stat counters ──
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('[data-count]').forEach(function(el){
        var target = parseInt(el.getAttribute('data-count'));
        if (!target) return;
        var start = 0, step = Math.ceil(900 / (1000/60));
        var inc = target / step;
        var timer = setInterval(function(){
            start = Math.min(start + inc, target);
            el.textContent = Math.round(start);
            if (start >= target) clearInterval(timer);
        }, 1000/60);
    });
});

// ── Photo upload ──
var pendingPhotoFile = null; // holds the selected file until Save Changes is clicked

function showToast(msg, type) {
    var t = document.getElementById('photoToast');
    t.textContent = (type === 'success' ? '✅ ' : '❌ ') + msg;
    t.className = 'photo-toast ' + type + ' show';
    setTimeout(function(){ t.classList.remove('show'); }, 3500);
}

document.getElementById('avatarInput').addEventListener('change', function(e){
    var file = e.target.files[0];
    if (!file) return;

    // Just preview — do NOT upload yet
    pendingPhotoFile = file;
    var reader = new FileReader();
    reader.onload = function(ev) {
        var display = document.getElementById('avatarDisplay');
        display.innerHTML = '<img src="' + ev.target.result + '" alt="Preview" id="avatarImg" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
    };
    reader.readAsDataURL(file);
});

// ── Intercept Save Changes to upload photo first if pending ──
document.getElementById('profileForm').addEventListener('submit', function(e){
    var btn = document.getElementById('saveProfileBtn');

    if (pendingPhotoFile) {
        e.preventDefault(); // hold the form submit until photo uploads

        btn.textContent = '⏳ Saving…';
        btn.style.opacity = '.75';

        var fd = new FormData();
        fd.append('photo', pendingPhotoFile);

        var progress = document.getElementById('avatarProgress');
        if (progress) progress.style.display = 'flex';

        fetch('/storehub/backend/auth/upload_photo.php', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(data) {
                if (progress) progress.style.display = 'none';
                pendingPhotoFile = null;
                if (data.ok) {
                    // Update displayed image with real URL
                    var img = document.getElementById('avatarImg');
                    if (img) img.src = data.url + '?v=' + Date.now();
                    // Now submit the rest of the profile form
                    document.getElementById('profileForm').submit();
                } else {
                    showToast(data.error || 'Photo upload failed.', 'error');
                    btn.textContent = 'Save Changes →';
                    btn.style.opacity = '1';
                }
            })
            .catch(function(){
                if (progress) progress.style.display = 'none';
                pendingPhotoFile = null;
                showToast('Network error uploading photo. Try again.', 'error');
                btn.textContent = 'Save Changes →';
                btn.style.opacity = '1';
            });
    } else {
        // No photo change — normal submit animation
        btn.textContent = '⏳ Saving…';
        btn.style.opacity = '.75';
    }
});
</script>
</body>
</html>
