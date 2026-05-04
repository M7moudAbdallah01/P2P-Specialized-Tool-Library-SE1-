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

$role = $_SESSION['role']; // 'admin' | 'technical' | 'user'

/* =========================================================
   2) DATABASE CONNECTION
========================================================= */
$db = Database::getInstance();
$conn = $db->getConnection();

/* =========================================================
   3) ACTIONS — admin only
========================================================= */

// Delete tool (admin only)
if ($role === 'admin' && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM tools WHERE tool_id = $id");
    header("Location: tools.php?msg=deleted");
    exit();
}

// Toggle availability (admin only)
if ($role === 'admin' && isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE tools SET availability = NOT availability WHERE tool_id = $id");
    header("Location: tools.php");
    exit();
}

// Toggle availability (technical)
if ($role === 'technical' && isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE tools SET availability = NOT availability WHERE tool_id = $id");
    header("Location: tools.php");
    exit();
}

/* =========================================================
   4) FILTERS (SEARCH + CATEGORY)
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
   5) FETCH DATA
========================================================= */

// tools
$tools = $conn->query("
    SELECT t.*, u.name AS owner_name, c.name AS category_name
    FROM tools t
    JOIN users u ON t.owner_id = u.user_id
    JOIN category c ON t.category_id = c.category_id
    $where
    ORDER BY t.created_at DESC
");

// categories (for filter dropdown)
$categories = $conn->query("SELECT * FROM category ORDER BY name");

// current category name (for breadcrumb when coming from categories page)
$current_cat_name = '';
if ($cat_filter > 0) {
    $res = $conn->query("SELECT name FROM category WHERE category_id = $cat_filter");
    if ($res && $row = $res->fetch_assoc()) {
        $current_cat_name = $row['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

<!-- =========================================================
   HEAD (META + CSS)
========================================================= -->
<meta charset="UTF-8">
<title>Tool Hub - Tools</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Main CSS -->
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">

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

    <!-- Role badge changes per role -->
    <?php if ($role === 'admin'): ?>
        <div class="role-badge role-admin">ADMIN</div>
    <?php elseif ($role === 'technical'): ?>
        <div class="role-badge role-technical">TECHNICAL</div>
    <?php else: ?>
        <div class="role-badge role-user">USER</div>
    <?php endif; ?>

    <div class="sidebar-nav">

        <?php if ($role === 'admin'): ?>
            <a href="../Admin/dashboard.php" class="nav-link">
                <i class="fa fa-gauge"></i> Dashboard
            </a>
        <?php endif; ?>

        <a href="tools.php" class="nav-link active">
            <i class="fa fa-wrench"></i> Tools
        </a>

        <a href="categories.php" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" />
                <line x1="8" y1="21" x2="16" y2="21" />
                <line x1="12" y1="17" x2="12" y2="21" />
            </svg> Categories
        </a>

        <?php if ($role === 'admin'): ?>
            <a href="members.php" class="nav-link">
                <i class="fa fa-users"></i> Members
            </a>
            <a href="reservations.php" class="nav-link">
                <i class="fa fa-calendar"></i> Reservations
            </a>
            <a href="chat.php" class="nav-link">
                <i class="fa fa-comments"></i> Chat
            </a>
            <a class="nav-link" href="#">
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
            <a href="reservations.php" class="nav-link">
                <i class="fa fa-calendar"></i> My Reservations
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
                <span class="topbar-breadcrumb">
                    &rsaquo; <?= htmlspecialchars($current_cat_name) ?>
                </span>
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
            <div class="alert alert-success">
                Tool deleted successfully.
            </div>
        <?php endif; ?>

        <!-- FILTERS -->
        <form method="GET" class="filters">

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

                <!-- =============================================
                     ROLE-BASED ACTION BUTTONS
                ============================================= -->
                <td class="actions-cell">

                    <?php if ($role === 'admin'): ?>
                        <!-- ADMIN: View + Toggle + Delete -->
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
                        <!-- TECHNICAL: View + Toggle + Report -->
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
                        <!-- USER: View only -->
                        <a href="tool_Details.php?id=<?= $t['tool_id'] ?>" class="btn-action btn-view" title="View Details">
                            <i class="fa fa-eye"></i>
                        </a>

                    <?php endif; ?>

                </td>

            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

    </div><!-- /.main-content -->
</div><!-- /.layout-right -->

</body>
</html>