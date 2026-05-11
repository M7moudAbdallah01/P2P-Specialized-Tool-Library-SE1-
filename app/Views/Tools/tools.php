<?php
/* =========================================================
   1) SESSION + AUTH CHECK
========================================================= */
session_start();
require_once __DIR__ . "/../../../Core/database.php";

// must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/login.php");
    exit();
}

$role = $_SESSION['role']; // 'admin' | 'technical' | 'client'

/* =========================================================
   2) DATABASE CONNECTION
========================================================= */
$db = Database::getInstance();
$conn = $db->getConnection();

/* =========================================================
   3) ACTIONS — CONSUMABLES RESTOCK (admin / technical only)
========================================================= */
if (
    in_array($role, ['admin', 'technical']) &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'restock'
) {
    $restock_id = intval($_POST['item_id']);
    $conn->query("UPDATE consumable SET quantity = quantity + 10 WHERE consumable_id = $restock_id");
    header("Location: tools.php?section=consumables");
    exit();
}

/* =========================================================
   4) ACTIONS — TOOLS (admin / technical)
========================================================= */

// Delete tool (admin only)
if ($role === 'admin' && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM tools WHERE tool_id = $id");
    header("Location: tools.php?msg=deleted");
    exit();
}

// Toggle availability (admin + technical)
if (in_array($role, ['admin', 'technical']) && isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE tools SET availability = NOT availability WHERE tool_id = $id");
    header("Location: tools.php");
    exit();
}

/* =========================================================
   5) FILTERS (SEARCH + CATEGORY)
========================================================= */
$search     = trim($_GET['search'] ?? '');
$cat_filter = intval($_GET['category'] ?? 0);

$where = "WHERE 1=1";

if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $where .= " AND (t.name LIKE '%$s%' OR u.name LIKE '%$s%')";
}

if ($cat_filter > 0) {
    $where .= " AND t.category_id = $cat_filter";
}

/* =========================================================
   6) FETCH TOOLS
========================================================= */
$tools = $conn->query("
    SELECT 
        t.*, 
        u.name AS owner_name, 
        c.name AS category_name
    FROM tools t
    JOIN users u ON t.owner_id = u.user_id
    JOIN category c ON t.category_id = c.category_id
    $where
    ORDER BY t.created_at DESC
");

// categories (for filter dropdown)
$categories = $conn->query("SELECT * FROM category ORDER BY name");

// current category name
$current_cat_name = '';
if ($cat_filter > 0) {
    $res = $conn->query("SELECT name FROM category WHERE category_id = $cat_filter");
    if ($res && $row = $res->fetch_assoc()) {
        $current_cat_name = $row['name'];
    }
}

/* =========================================================
   7) FETCH CONSUMABLES (admin / technical only)
========================================================= */
$consumables = [];
if (in_array($role, ['admin', 'technical'])) {
    $result = $conn->query("SELECT * FROM consumable ORDER BY name");
    if ($result) {
        $consumables = $result->fetch_all(MYSQLI_ASSOC);
    }
}

/* =========================================================
   8) USER DASHBOARD COUNTS
========================================================= */
$uid = intval($_SESSION['user_id']);

$r = $conn->query("SELECT COUNT(*) AS cnt FROM tools WHERE owner_id = $uid");
$total_tools = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) AS cnt FROM reservations r JOIN tools t ON r.tool_id = t.tool_id WHERE t.owner_id = $uid AND r.status = 'confirmed'");
$active_res = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) AS cnt FROM reservations r JOIN tools t ON r.tool_id = t.tool_id WHERE t.owner_id = $uid AND r.status = 'pending'");
$pending_res = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) AS cnt FROM messages WHERE receiver_id = $uid AND is_read = 0");
$unread_msgs = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) AS cnt FROM dispute d JOIN reservations r ON d.rental_id = r.reservation_id JOIN tools t ON r.tool_id = t.tool_id WHERE t.owner_id = $uid AND d.status = 'open'");
$open_reports = $r->fetch_assoc()['cnt'];

/* =========================================================
   9) ACTIVE SECTION (tools | consumables)
========================================================= */
$active_section = $_GET['section'] ?? 'tools';
if (!in_array($role, ['admin', 'technical'])) {
    $active_section = 'tools'; // clients can't access consumables
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<title>Tool Hub - Tools</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">

<style>
    /* ── Section Tabs ── */
    .section-tabs {
        display: flex;
        gap: 4px;
        margin-bottom: 20px;
        border-bottom: 2px solid #eee;
        padding-bottom: 0;
    }
    .section-tab {
        padding: 10px 20px;
        border: none;
        background: none;
        font-family: inherit;
        font-size: 14px;
        font-weight: 600;
        color: #888;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        text-decoration: none;
        transition: color .2s, border-color .2s;
    }
    .section-tab:hover { color: #333; }
    .section-tab.active {
        color: #c62828;
        border-bottom-color: #c62828;
    }
    .section-tab i { margin-right: 6px; }

    /* ── Consumables Table ── */
    .status-pill {
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .pill-out    { background: #ffebee; color: #c62828; }
    .pill-low    { background: #fff3e0; color: #e65100; }
    .pill-stable { background: #e8f5e9; color: #2e7d32; }

    .restock-btn {
        color: #1a73e8;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        font-family: inherit;
        text-decoration: underline;
        padding: 0;
    }
    .restock-btn:hover { color: #0d47a1; }

    /* hide sections via JS */
    .page-section { display: none; }
    .page-section.visible { display: block; }
</style>

</head>
<body>

<!-- =========================================================
   SIDEBAR
========================================================= -->
<div class="sidebar">

    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <?php if ($role === 'admin'): ?>
        <div class="role-badge role-admin">ADMIN</div>
    <?php elseif ($role === 'technical'): ?>
        <div class="role-badge role-technical">TECHNICAL</div>
    <?php else: ?>
        <div class="role-badge role-user">CLIENT</div>
    <?php endif; ?>

    <div class="sidebar-nav">

        <?php if ($role === 'client'): ?>
            <a href="../Client/dashboard.php" class="nav-link">
                <i class="fa fa-gauge"></i> Dashboard
            </a>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
            <a href="../Admin/dashboard.php" class="nav-link">
                <i class="fa fa-gauge"></i> Dashboard
            </a>
        <?php endif; ?>

        <a href="tools.php" class="nav-link active">
            <i class="fa fa-wrench"></i> Tools
        </a>

        <?php if ($role === 'client'): ?>
            <a href="../Client/ToolSpecification.php" class="nav-link">
                <i class="fa fa-plus"></i> Add Tool
            </a>
        <?php endif; ?>

        <a href="categories.php" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" />
                <line x1="8" y1="21" x2="16" y2="21" />
                <line x1="12" y1="17" x2="12" y2="21" />
            </svg> Categories
        </a>

        <?php if ($role === 'admin'): ?>
            <a href="../Admin/members.php" class="nav-link">
                <i class="fa fa-users"></i> Members
            </a>
            <a href="../Admin/reservations.php" class="nav-link">
                <i class="fa fa-calendar"></i> Reservations
            </a>
            <a href="../Admin/chat.php" class="nav-link">
                <i class="fa fa-comments"></i> Chat
            </a>
            <a class="nav-link" href="../Admin/reports.php">
                <i class="fa fa-scale-balanced"></i> Disputes &amp; Reports
            </a>
        <?php elseif ($role === 'technical'): ?>
            <a href="reservations.php" class="nav-link">
                <i class="fa fa-calendar"></i> Reservations
            </a>
            <a class="nav-link" href="#">
                <i class="fa fa-scale-balanced"></i> Reports
            </a>
        <?php else: ?>
            <a href="../Client/my-reservations.php" class="nav-link">
                <i class="fa fa-calendar-check"></i> My Reservations
            </a>
            <a href="../Client/my-reports.php" class="nav-link">
                <i class="fa fa-calendar-check"></i> My Reports
            </a>
            <a href="../Client/chat.php" class="nav-link">
                <i class="fa fa-comments"></i> Chat
                <?php if ($unread_msgs > 0): ?>
                    <span class="nav-count"><?= $unread_msgs ?></span>
                <?php endif; ?>
            </a>
            <a href="../Client/ToolCompatibility.php" class="nav-link">
                <i class="fa fa-circle-check"></i> Compatibility Checker
            </a>
            <a href="../Client/DamageDeclaration.php" class="nav-link">
                <i class="fa fa-triangle-exclamation"></i> Damage Report
            </a>
        <?php endif; ?>

    </div>

</div>

<!-- =========================================================
   TOPBAR
========================================================= -->
<div class="layout-right">

    <div class="topbar">
        <div class="topbar-title">
            Tools
            <?php if ($current_cat_name !== ''): ?>
                <span class="topbar-breadcrumb">&rsaquo; <?= htmlspecialchars($current_cat_name) ?></span>
            <?php endif; ?>
        </div>

        <div class="topbar-right">
            <button class="avatar-btn <?= $role === 'admin' ? 'admin-avatar' : ($role === 'technical' ? 'technical-avatar' : 'user-avatar') ?>">
                <?= htmlspecialchars($_SESSION['name']) ?>
                <span><?= ucfirst($role) ?></span>
            </button>
            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <!-- =========================================================
       MAIN CONTENT
    ========================================================= -->
    <div class="main-content">

        <!-- SUCCESS ALERT -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success">Tool deleted successfully.</div>
        <?php endif; ?>

        <!-- ── TABS (admin / technical only) ── -->
        <?php if (in_array($role, ['admin', 'technical'])): ?>
        <div class="section-tabs">
            <a href="tools.php?section=tools<?= $cat_filter ? '&category='.$cat_filter : '' ?>"
               class="section-tab <?= $active_section === 'tools' ? 'active' : '' ?>">
                <i class="fa fa-wrench"></i> Tools
            </a>
            <a href="tools.php?section=consumables"
               class="section-tab <?= $active_section === 'consumables' ? 'active' : '' ?>">
                <i class="fa fa-boxes-stacked"></i> Consumables
            </a>
        </div>
        <?php endif; ?>

        <!-- ══════════════════════════════════════════
             SECTION 1 : TOOLS TABLE
        ══════════════════════════════════════════ -->
        <div class="page-section <?= $active_section === 'tools' ? 'visible' : '' ?>" id="section-tools">

            <!-- FILTERS -->
            <form method="GET" class="filters">
                <input type="hidden" name="section" value="tools">
                <input type="text"
                       name="search"
                       placeholder="Search..."
                       value="<?= htmlspecialchars($search) ?>">

                <select name="category">
                    <option value="0">All Categories</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['category_id'] ?>"
                            <?= $cat_filter == $cat['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button class="btn btn-red">Search</button>
                <a href="tools.php" class="btn btn-ghost">Reset</a>
            </form>

            <!-- TABLE -->
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Tool</th>
                    <th>Category</th>
                    <th>Client</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($t = $tools->fetch_assoc()): ?>
                <tr>
                    <td><?= $t['tool_id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($t['name']) ?></strong><br>
                        <small><?= htmlspecialchars(substr($t['description'], 0, 40)) ?>...</small>
                    </td>
                    <td><?= htmlspecialchars($t['category_name']) ?></td>
                    <td><?= htmlspecialchars($t['owner_name']) ?></td>
                    <td>$<?= $t['base_price'] ?></td>
                    <td><?= htmlspecialchars($t['state']) ?></td>
                    <td>
                        <span class="badge <?= $t['availability'] ? 'badge-success' : 'badge-danger' ?>">
                            <?= $t['availability'] ? 'Yes' : 'No' ?>
                        </span>
                    </td>
                    <td class="actions-cell">
                        <?php if ($role === 'admin'): ?>
                            <a href="tool_Details.php?id=<?= $t['tool_id'] ?>" class="btn-action btn-view" title="View">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="?toggle=<?= $t['tool_id'] ?><?= $cat_filter ? '&category='.$cat_filter : '' ?>" class="btn-action btn-toggle" title="Toggle Availability">
                                <i class="fa fa-toggle-on"></i>
                            </a>
                            <a href="?delete=<?= $t['tool_id'] ?><?= $cat_filter ? '&category='.$cat_filter : '' ?>"
                               class="btn-action btn-delete"
                               title="Delete"
                               onclick="return confirm('Delete this tool?')">
                                <i class="fa fa-trash"></i>
                            </a>
                        <?php elseif ($role === 'technical'): ?>
                            <a href="tool_Details.php?id=<?= $t['tool_id'] ?>" class="btn-action btn-view" title="View">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="?toggle=<?= $t['tool_id'] ?><?= $cat_filter ? '&category='.$cat_filter : '' ?>" class="btn-action btn-toggle" title="Toggle Availability">
                                <i class="fa fa-toggle-on"></i>
                            </a>
                            <a href="report.php?tool_id=<?= $t['tool_id'] ?>" class="btn-action btn-report" title="Report Issue">
                                <i class="fa fa-flag"></i>
                            </a>
                        <?php else: ?>
                            <a href="tool_Details.php?id=<?= $t['tool_id'] ?>" class="btn-action btn-view" title="View Details">
                                <i class="fa fa-eye"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

        </div><!-- /#section-tools -->


        <!-- ══════════════════════════════════════════
             SECTION 2 : CONSUMABLES TABLE
             (admin / technical only — already guarded above)
        ══════════════════════════════════════════ -->
        <?php if (in_array($role, ['admin', 'technical'])): ?>
        <div class="page-section <?= $active_section === 'consumables' ? 'visible' : '' ?>" id="section-consumables">

            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Consumable Item</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($consumables): foreach ($consumables as $item): ?>
                <tr>
                    <td><?= intval($item['consumable_id']) ?></td>
                    <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                    <td><?= intval($item['quantity']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="item_id" value="<?= intval($item['consumable_id']) ?>">
                            <input type="hidden" name="action"  value="restock">
                            <button type="submit" class="restock-btn">
                                <i class="fa fa-rotate-right"></i> Restock +10
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4">No consumable items found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>

        </div><!-- /#section-consumables -->
        <?php endif; ?>

    </div><!-- /.main-content -->
</div><!-- /.layout-right -->

</body>
</html>