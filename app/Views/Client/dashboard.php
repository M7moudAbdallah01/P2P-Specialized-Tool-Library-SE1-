<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();
$uid  = intval($_SESSION['user_id']);


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

$r = $conn->query("SELECT trust_score FROM users WHERE user_id = $uid");
$trust = $r ? $r->fetch_assoc()['trust_score'] : 0;
$trust_score = $trust ?? 0;

$my_tools = $conn->query("
    SELECT t.*, c.name AS category_name
    FROM tools t
    JOIN category c ON t.category_id = c.category_id
    WHERE t.owner_id = $uid
    ORDER BY t.created_at DESC
    LIMIT 6
");

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

        <a href="../Tools/tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
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

        <a href="my-reservations.php" class="nav-link">
            <i class="fa fa-calendar-check"></i> My Reservations
        </a>

        <a href="my-reports.php" class="nav-link">
            <i class="fa fa-calendar-check"></i> My Reports
        </a>

        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
            <?php if ($unread_msgs > 0): ?>
                <span class="nav-count"><?= $unread_msgs ?></span>
            <?php endif; ?>
        </a>

        <a href="ToolCompatibility.php" class="nav-link">
            <i class="fa fa-circle-check"></i> Compatibility Checker
        </a>

        <a href="DamageDeclaration.php" class="nav-link">
            <i class="fa fa-triangle-exclamation"></i> Damage Report
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
                <div class="stat-icon si-blue"><i class="fa fa-star"></i></div>
                <div>
                    <div class="stat-value"><?= $trust_score ?></div>
                    <div class="stat-label">Trust Score</div>
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
                <a href="../Tools/tools.php">View All &rarr;</a>
            </div>

            <?php if ($my_tools->num_rows === 0): ?>
                <div class="tools-empty">
                    You have no tools yet.
                    <a href="ToolSpecification.php" style="color:#ef4444;">Add your first tool</a>.
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