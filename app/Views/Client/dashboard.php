<?php
/* =========================================================
   1) SESSION + AUTH CHECK
========================================================= */
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../Auth/login.php");
    exit();
}

/* =========================================================
   2) DATABASE CONNECTION
========================================================= */
$db   = Database::getInstance();
$conn = $db->getConnection();
$uid  = intval($_SESSION['user_id']);

/* =========================================================
   3) STATS
========================================================= */

$r = $conn->query("SELECT COUNT(*) AS cnt FROM tools WHERE owner_id = $uid");
$total_tools = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) AS cnt FROM reservations r
                   JOIN tools t ON r.tool_id = t.tool_id
                   WHERE t.owner_id = $uid AND r.status = 'active'");
$active_res = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) AS cnt FROM reservations r
                   JOIN tools t ON r.tool_id = t.tool_id
                   WHERE t.owner_id = $uid AND r.status = 'pending'");
$pending_res = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) AS cnt FROM messages
                   WHERE receiver_id = $uid AND is_read = 0");
$unread_msgs = $r->fetch_assoc()['cnt'];

$r = $conn->query("
    SELECT COUNT(*) AS cnt
    FROM dispute d
    JOIN reservations r ON d.rental_id = r.reservation_id
    JOIN tools t ON r.tool_id = t.tool_id
    WHERE t.owner_id = $uid AND d.status = 'open'
");
$open_reports = $r->fetch_assoc()['cnt'];

// $r = $conn->query("SELECT COALESCE(SUM(total_price),0) AS total FROM reservations r
//                    JOIN tools t ON r.tool_id = t.tool_id
//                    WHERE t.owner_id = $uid AND r.status = 'completed'");
// $total_earned = $r->fetch_assoc()['total'];

/* =========================================================
   4) MY TOOLS (latest 6)
========================================================= */
$my_tools = $conn->query("
    SELECT t.*, c.name AS category_name
    FROM tools t
    JOIN category c ON t.category_id = c.category_id
    WHERE t.owner_id = $uid
    ORDER BY t.created_at DESC
    LIMIT 6
");

/* =========================================================
   5) RECENT RESERVATIONS (latest 5)
========================================================= */
// $recent_res = $conn->query("
//     SELECT r.*, t.name AS tool_name, u.name AS renter_name
//     FROM reservations r
//     JOIN tools t ON r.tool_id = t.tool_id
//     JOIN users u ON r.user_id = u.user_id
//     WHERE t.owner_id = $uid
//     ORDER BY r.created_at DESC
//     LIMIT 5
// ");

// /* =========================================================
//    6) RECENT MESSAGES (latest 5)
// ========================================================= */
// $recent_msgs = $conn->query("
//     SELECT m.*, u.name AS sender_name
//     FROM messages m
//     JOIN users u ON m.sender_id = u.user_id
//     WHERE m.receiver_id = $uid
//     ORDER BY m.created_at DESC
//     LIMIT 5
// ");

// /* =========================================================
//    7) RECENT REPORTS (latest 5)
// ========================================================= */
// $recent_reports = $conn->query("
//     SELECT rp.*, t.name AS tool_name, u.name AS reporter_name
//     FROM reports rp
//     LEFT JOIN tools t  ON rp.tool_id     = t.tool_id
//     LEFT JOIN users u  ON rp.reporter_id = u.user_id
//     WHERE rp.reported_user_id = $uid OR rp.reporter_id = $uid
//     ORDER BY rp.created_at DESC
//     LIMIT 5
// ");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tool Hub - Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

    <div class="role-badge" style="background:#6366f1;color:#fff;">CLIENT</div>

    <div class="sidebar-nav">

        <a href="dashboard.php" class="nav-link active">
            <i class="fa fa-gauge"></i> Dashboard
        </a>

        <a href="my-tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> My Tools
            <?php if ($total_tools > 0): ?>
                <span class="nav-count"><?= $total_tools ?></span>
            <?php endif; ?>
        </a>

        <a href="ToolSpecification.php" class="nav-link">
            <i class="fa fa-plus"></i> Add Tool
        </a>

        <a href="../Tools/categories.php" class="nav-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg> Categories
        </a>

        <a href="reservations.php" class="nav-link">
            <i class="fa fa-calendar"></i> Reservations
            <?php if ($pending_res > 0): ?>
                <span class="nav-count"><?= $pending_res ?></span>
            <?php endif; ?>
        </a>

        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Messages
            <?php if ($unread_msgs > 0): ?>
                <span class="nav-count"><?= $unread_msgs ?></span>
            <?php endif; ?>
        </a>

        <a href="reports.php" class="nav-link">
            <i class="fa fa-scale-balanced"></i> Reports
            <?php if ($open_reports > 0): ?>
                <span class="nav-count"><?= $open_reports ?></span>
            <?php endif; ?>
        </a>

    </div>
</div>

<!-- =========================================================
   LAYOUT RIGHT
========================================================= -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-title">Dashboard</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#6366f1;color:#fff;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Client</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- ================================================
             STATS
        ================================================ -->
        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon si-red"><i class="fa fa-wrench"></i></div>
                <div>
                    <div class="stat-value"><?= $total_tools ?></div>
                    <div class="stat-label">My Tools</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon si-blue"><i class="fa fa-circle-check"></i></div>
                <div>
                    <div class="stat-value"><?= $active_res ?></div>
                    <div class="stat-label">Active Reservations</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon si-yellow"><i class="fa fa-clock"></i></div>
                <div>
                    <div class="stat-value"><?= $pending_res ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon si-green"><i class="fa fa-envelope"></i></div>
                <div>
                    <div class="stat-value"><?= $unread_msgs ?></div>
                    <div class="stat-label">Unread Messages</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon si-purple"><i class="fa fa-scale-balanced"></i></div>
                <div>
                    <div class="stat-value"><?= $open_reports ?></div>
                    <div class="stat-label">Open Reports</div>
                </div>
            </div>
<!-- 
            <div class="stat-card">
                <div class="stat-icon si-orange"><i class="fa fa-dollar-sign"></i></div>
                <div>
                    <div class="stat-value">$<?= number_format($total_earned, 0) ?></div>
                    <div class="stat-label">Total Earned</div>
                </div>
            </div> -->

        </div>

        <!-- ================================================
             MY TOOLS — full width cards
        ================================================ -->
        <div class="section-block">
            <div class="section-header">
                <div class="sh-left">
                    <i class="fa fa-wrench" style="color:#ef4444;"></i>
                    My Tools
                </div>
                <a href="tools.php">View All &rarr;</a>
            </div>

            <?php if ($my_tools->num_rows === 0): ?>
                <div class="tools-empty">
                    You have no tools yet.
                    <a href="add_tool_page.php" style="color:#ef4444;">Add your first tool</a>.
                </div>
            <?php else: ?>
                <div class="tools-cards">
                    <?php while ($t = $my_tools->fetch_assoc()): ?>
                    <div class="tool-mini-card">
                        <div class="tmc-name"><?= htmlspecialchars($t['name']) ?></div>
                        <div class="tmc-cat"><?= htmlspecialchars($t['category_name']) ?></div>
                        <div class="tmc-foot">
                            <span class="tmc-price">$<?= $t['base_price'] ?></span>
                            <span class="badge <?= $t['availability'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $t['availability'] ? 'Available' : 'Unavailable' ?>
                            </span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ================================================
             RESERVATIONS + MESSAGES side by side
        ================================================ -->
        

    </div><!-- /.main-content -->
</div><!-- /.layout-right -->

</body>
</html>