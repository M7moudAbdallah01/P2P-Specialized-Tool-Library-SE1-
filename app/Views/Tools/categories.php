<?php
/* =========================================================
   1) SESSION + AUTH CHECK
========================================================= */
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}
$role = $_SESSION['role']; 
/* =========================================================
   2) DATABASE CONNECTION
========================================================= */
$db = Database::getInstance();
$conn = $db->getConnection();

/* =========================================================
   3) FETCH CATEGORIES WITH TOOL COUNT
========================================================= */
$categories = $conn->query("
    SELECT c.*, COUNT(t.tool_id) AS tool_count
    FROM category c
    LEFT JOIN tools t ON t.category_id = c.category_id
    GROUP BY c.category_id
    ORDER BY c.name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>

<!-- =========================================================
   HEAD (META + CSS)
========================================================= -->
<meta charset="UTF-8">
<title>Tool Hub - Categories</title>
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

        <a href="tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
        </a>

        <a href="categories.php" class="nav-link active">
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
        <div class="topbar-title">Categories</div>

        <div class="topbar-right">
            <button class="avatar-btn admin-avatar">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Admin</span>
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

        <!-- CATEGORIES GRID -->
        <div class="categories-grid">

            <?php while ($cat = $categories->fetch_assoc()): ?>
            <a href="tools.php?category=<?= $cat['category_id'] ?>" class="category-card">

                <div class="category-icon">
                    <i class="fa fa-layer-group"></i>
                </div>

                <div class="category-info">
                    <div class="category-name"><?= htmlspecialchars($cat['name']) ?></div>
                    <div class="category-count"><?= $cat['tool_count'] ?> Tools</div>
                </div>

                <div class="category-arrow">
                    <i class="fa fa-chevron-right"></i>
                </div>

            </a>
            <?php endwhile; ?>

        </div>

    </div>
</div>

</body>
</html>