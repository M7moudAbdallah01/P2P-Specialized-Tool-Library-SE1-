<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";
require_once __DIR__ . "/../../../Core/ModelFactory.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();

/* =========================================================
   ACTIONS — ADD CATEGORY
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $cat_name = $conn->real_escape_string(trim($_POST['cat_name']));
    $cat_desc = $conn->real_escape_string(trim($_POST['cat_description'] ?? ''));
    if ($cat_name !== '') {
        $conn->query("INSERT INTO category (name) VALUES ('$cat_name')");
    }
    header("Location: dashboard.php?section=categories&msg=cat_added");
    exit();
}

/* =========================================================
   ACTIONS — ADD COUPON
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code      = $conn->real_escape_string(trim($_POST['code']));
    $discount  = intval($_POST['discount_percent']);
    $cat_id    = $_POST['category_id'] !== '' ? intval($_POST['category_id']) : 'NULL';
    $start     = $conn->real_escape_string($_POST['start_date']);
    $end       = $conn->real_escape_string($_POST['end_date']);
    $cat_val   = ($cat_id === 'NULL') ? 'NULL' : $cat_id;
    if ($code !== '') {
        $conn->query("INSERT INTO coupons (code, discount_percent, category_id, start_date, end_date)
                      VALUES ('$code', $discount, $cat_val, '$start', '$end')");
    }
    header("Location: dashboard.php?section=campaigns&msg=coupon_added");
    exit();
}

/* =========================================================
   ACTIVE SECTION
========================================================= */
$active_section = $_GET['section'] ?? 'overview';

/* =========================================================
   STATS
========================================================= */
// ── Factory Pattern ──────────────────────────────
$zones        = ModelFactory::getZones();
$reportModel  = ModelFactory::create('report');
$totalRevenue = $reportModel->getTotalRevenue();
$zoneRevenue  = $reportModel->getRevenueByZone();

$r = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='client' OR role='technical'");
$total_users = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) AS c FROM tools");
$total_tools = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) AS c FROM reservations WHERE status='pending'");
$pending = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) AS c FROM dispute WHERE status='open'");
$disputes = $r->fetch_assoc()['c'];

/* =========================================================
   CATEGORIES
========================================================= */
$cat_result = $conn->query("SELECT * FROM category ORDER BY name");
$categories = $cat_result ? $cat_result->fetch_all(MYSQLI_ASSOC) : [];

/* =========================================================
   COUPONS  (LEFT JOIN to get category name)
========================================================= */
$coup_result = $conn->query("
    SELECT cp.*, c.name AS category_name
    FROM coupons cp
    LEFT JOIN category c ON cp.category_id = c.category_id
    ORDER BY cp.end_date DESC
");
$coupons = $coup_result ? $coup_result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tool Hub - Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
</head>
<body>

<!-- ═══════════════════════
     SIDEBAR
═══════════════════════ -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="role-badge role-admin">ADMIN</div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link active">
            <i class="fa fa-gauge"></i> Dashboard
        </a>
        <a href="../Tools/tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
        </a>
        <a href="../Tools/categories.php" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg> Categories
        </a>
        <a href="members.php" class="nav-link">
            <i class="fa fa-users"></i> Members
        </a>
        <a href="reservations.php" class="nav-link">
            <i class="fa fa-calendar"></i> Reservations
        </a>
        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
        </a>
        <a href="reports.php" class="nav-link">
            <i class="fa fa-scale-balanced"></i> Reports
        </a>
    </div>
</div>

<!-- ═══════════════════════
     RIGHT SIDE
═══════════════════════ -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <button class="hamburger"><i class="fa fa-bars"></i></button>
        <div class="topbar-title">Dashboard</div>
        <div class="topbar-right">
            <button class="avatar-btn admin-avatar">
                <?= htmlspecialchars($_SESSION['name']) ?>
                <span>Admin</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- SUCCESS ALERTS -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cat_added'): ?>
            <div class="alert-success"><i class="fa fa-check-circle"></i> Category added successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'coupon_added'): ?>
            <div class="alert-success"><i class="fa fa-check-circle"></i> Campaign created successfully.</div>
        <?php endif; ?>

        <!-- ── TABS ── -->
        <div class="section-tabs">
            <a href="dashboard.php?section=overview"
               class="section-tab <?= $active_section === 'overview'   ? 'active' : '' ?>">
                <i class="fa fa-gauge"></i> Overview
            </a>
            <a href="dashboard.php?section=categories"
               class="section-tab <?= $active_section === 'categories' ? 'active' : '' ?>">
                <i class="fa fa-tags"></i> Categories
            </a>
            <a href="dashboard.php?section=campaigns"
               class="section-tab <?= $active_section === 'campaigns'  ? 'active' : '' ?>">
                <i class="fa fa-bullhorn"></i> Campaigns
            </a>
        </div>


        <!-- ══════════════════════════════════════
             SECTION 1 : OVERVIEW  (original content)
        ══════════════════════════════════════ -->
        <div class="page-section <?= $active_section === 'overview' ? 'visible' : '' ?>" id="section-overview">

            <div class="card">
                <h2 style="margin-bottom:6px;">
                    Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋
                </h2>
                <p style="color: var(--text-muted); font-size: 12.5px;">
                    Here's a quick overview — <?= date('l, d M Y') ?>
                </p>
            </div>

            <!-- STATS -->
            <div class="panels-grid">
                <div class="card panel">
                    <div class="panel-title">Users</div>
                    <div style="font-size:22px; font-weight:700;"><?= $total_users ?></div>
                </div>
                <div class="card panel">
                    <div class="panel-title">Tools</div>
                    <div style="font-size:22px; font-weight:700;"><?= $total_tools ?></div>
                </div>
                <div class="card panel">
                    <div class="panel-title">Pending</div>
                    <div style="font-size:22px; font-weight:700;"><?= $pending ?></div>
                </div>
                <div class="card panel">
                    <div class="panel-title">Disputes</div>
                    <div style="font-size:22px; font-weight:700;"><?= $disputes ?></div>
                </div>
            </div>

            <!-- FINANCIAL -->
            <div class="welcome" style="text-align:center; background-color:#4d1b1f;">
                <h1>Financial Dashboard</h1>
                <p>Real-time revenue tracking and zone performance.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">$<?= number_format($totalRevenue, 2) ?></div>
                </div>
                <div class="stat-card" style="border-left-color: var(--accent-yellow);">
                    <div class="stat-label">Active Zones</div>
                    <div class="stat-value"><?= count($zoneRevenue) ?></div>
                    <div class="stat-label">Monitored Locations</div>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-top:0; color:#fff;">Revenue by Zone</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Zone Name</th>
                            <th style="text-align:right;">Total Revenue</th>
                            <th style="text-align:right;">Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($zoneRevenue)): foreach ($zoneRevenue as $zone): ?>
                            <tr>
                                <td><span class="tool-name"><?= htmlspecialchars($zone['zone_name']) ?></span></td>
                                <td style="text-align:right; color:var(--accent-green); font-weight:600;">
                                    $<?= number_format($zone['revenue'], 2) ?>
                                </td>
                                <td style="text-align:right;">
                                    <div class="progress-bar-bg">
                                        <?php $pct = ($totalRevenue > 0) ? ($zone['revenue'] / $totalRevenue) * 100 : 0; ?>
                                        <div class="progress-bar-fill" style="width:<?= $pct ?>%;"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="3" style="text-align:center; padding:20px;">No data available yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- RENTAL ESCALATION -->
            <div class="card" style="text-align:center; background-color:#4d1b1f;">
                <h2>Rental Escalation Tracking</h2>
                <p>Monitoring late returns and applying penalty tiers automatically.</p>
            </div>

            <table class="card">
                <thead>
                    <tr>
                        <th>Rental ID</th>
                        <th>User Name</th>
                        <th>Tool Name</th>
                        <th>Due Date</th>
                        <th>Penalty Fee</th>
                        <th>Escalation Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rentals)): foreach ($rentals as $rental): ?>
                        <tr>
                            <td>#<?= $rental['rental_id'] ?></td>
                            <td><?= htmlspecialchars($rental['user_name'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($rental['tool_name'] ?? 'Unknown') ?></td>
                            <td><?= $rental['return_date'] ?></td>
                            <td class="level-<?= $rental['escalation_level'] ?>">
                                $<?= number_format($rental['penalty_fee'], 2) ?>
                            </td>
                            <td>
                                <span class="level-<?= $rental['escalation_level'] ?>">
                                    Level <?= $rental['escalation_level'] ?>
                                </span>
                            </td>
                            <td><span class="status-badge"><?= strtoupper($rental['status']) ?></span></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="7" style="text-align:center; padding:50px;">No rental records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- ZONE MANAGEMENT -->
            <div class="welcome" style="text-align:center; background-color:#4d1b1f;">
                <h1>Zone Management</h1>
                <p>Add and organize service delivery areas for your library.</p>
            </div>

            <div class="card">
                <form method="POST" action="../../Controllers/ZoneController.php" class="filters">
                    <input type="text" name="zone_name" placeholder="Enter zone name (e.g., Cairo, Giza)..." required>
                    <input type="hidden" name="action" value="add">
                    <button type="submit" class="btn btn-red">Add Zone</button>
                </form>
            </div>

            <div class="card">
                <h3 style="margin-top:0; margin-bottom:20px;">All Registered Zones</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Zone Name</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($zones) && is_array($zones)): foreach ($zones as $zone): ?>
                            <tr>
                                <td><?= htmlspecialchars($zone['zone_id']) ?></td>
                                <td><?= htmlspecialchars($zone['zone_name']) ?></td>
                                <td style="text-align:right;">
                                    <div style="display:flex; gap:8px; justify-content:flex-end;">
                                        <a href="edit_zone.php?id=<?= $zone['zone_id'] ?>"
                                           class="btn btn-ghost btn-sm"
                                           style="text-decoration:none; line-height:2;">Edit</a>
                                        <a href="../../Controllers/ZoneController.php?action=delete&id=<?= $zone['zone_id'] ?>"
                                           class="btn btn-red btn-sm"
                                           style="background:rgba(230,57,70,0.1); color:#e63946; border:1px solid rgba(230,57,70,0.2); text-decoration:none; line-height:2;"
                                           onclick="return confirm('Are you sure you want to delete this zone?')">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="3" style="text-align:center; padding:40px;" class="tool-sub">No zones found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /#section-overview -->


        <!-- ══════════════════════════════════════
             SECTION 2 : CATEGORIES
        ══════════════════════════════════════ -->
        <div class="page-section <?= $active_section === 'categories' ? 'visible' : '' ?>" id="section-categories">

            <!-- Add Category Form -->
            <div class="form-card">
                <h3><i class="fa fa-plus" style="color:#c62828;"></i> Add New Category</h3>
                <form method="POST">
                    <input type="hidden" name="add_category" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category Name</label>
                            <input type="text" name="cat_name" placeholder="e.g. Power Tools" required style="width:200px;">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="cat_description" placeholder="Short description..." style="width:300px;">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-red">Add Category</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Categories Table -->
            <div class="card">
                <h3 style="margin-top:0; margin-bottom:16px;">
                    Tool Taxonomy &amp; Category Mapping
                </h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= intval($cat['category_id']) ?></td>
                                <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                <td><?= htmlspecialchars($cat['description'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="3" style="text-align:center; padding:20px;">No categories found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /#section-categories -->


        <!-- ══════════════════════════════════════
             SECTION 3 : CAMPAIGNS
        ══════════════════════════════════════ -->
        <div class="page-section <?= $active_section === 'campaigns' ? 'visible' : '' ?>" id="section-campaigns">

            <!-- Create Coupon Form -->
            <div class="form-card">
                <h3><i class="fa fa-bullhorn" style="color:#c62828;"></i> Create New Campaign</h3>
                <form method="POST">
                    <input type="hidden" name="add_coupon" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Coupon Code</label>
                            <input type="text" name="code" placeholder="SUMMER2026" required style="width:140px; text-transform:uppercase;">
                        </div>
                        <div class="form-group">
                            <label>Discount (%)</label>
                            <input type="number" name="discount_percent" min="1" max="100" required style="width:80px;">
                        </div>
                        <div class="form-group">
                            <label>Category Target</label>
                            <select name="category_id" style="width:180px;">
                                <option value="">Apply to All (Global)</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= intval($cat['category_id']) ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-red">Create Campaign</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Campaigns Table -->
            <div class="card">
                <h3 style="margin-top:0; margin-bottom:16px;">Existing Campaigns</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Target Category</th>
                            <th>Validity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($coupons)): foreach ($coupons as $coupon): ?>
                            <?php
                                $today = date('Y-m-d');
                                if ($today > $coupon['end_date'])        { $pill = 'pill-expired'; $label = 'Expired'; }
                                elseif ($today < $coupon['start_date'])  { $pill = 'pill-pending'; $label = 'Pending'; }
                                else                                      { $pill = 'pill-active';  $label = 'Active';  }
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($coupon['code']) ?></strong></td>
                                <td><?= intval($coupon['discount_percent']) ?>%</td>
                                <td><?= htmlspecialchars($coupon['category_name'] ?? 'Global') ?></td>
                                <td><?= htmlspecialchars($coupon['start_date']) ?> → <?= htmlspecialchars($coupon['end_date']) ?></td>
                                <td><span class="pill <?= $pill ?>"><?= $label ?></span></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" style="text-align:center; padding:20px;">No campaigns found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /#section-campaigns -->

    </div><!-- /.main-content -->
</div><!-- /.layout-right -->

</body>
</html>