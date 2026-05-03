<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}
 
$db = Database::getInstance();
$conn = $db->getConnection();


$db = Database::getInstance();
$conn = $db->getConnection();
 
$r = $conn->query("SELECT COUNT(*) AS c FROM users");
$total_users = $r->fetch_assoc()['c'];
 
$r = $conn->query("SELECT COUNT(*) AS c FROM tools");
$total_tools = $r->fetch_assoc()['c'];
 
$r = $conn->query("SELECT COUNT(*) AS c FROM reservation WHERE status = 'pending'");
$pending = $r->fetch_assoc()['c'];
 
$r = $conn->query("SELECT COUNT(*) AS c FROM dispute WHERE status = 'open'");
$disputes = $r->fetch_assoc()['c'];

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

<!-- ═══════════════════════════════
     SIDEBAR
═══════════════════════════════ -->
<div class="sidebar">

    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link active">
            <i class="fa fa-gauge"></i> Dashboard
        </a>

        <a href="tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
        </a>

        <a href="member-profile.php" class="nav-link">
            <i class="fa fa-users"></i> Members
        </a>

        <a href="reservations.php" class="nav-link">
            <i class="fa fa-calendar"></i> Reservations
        </a>

        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
        </a>
    </div>

    <div class="role-badge role-admin">
        ADMIN
    </div>

</div>


<!-- ═══════════════════════════════
     RIGHT SIDE (TOPBAR + CONTENT)
═══════════════════════════════ -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">

        <button class="hamburger">
            <i class="fa fa-bars"></i>
        </button>

        <div class="topbar-title">
            Dashboard
        </div>

        <div class="topbar-right">

            <div class="notif-wrap">
                <button class="icon-btn">
                    <i class="fa fa-bell"></i>
                </button>
                <div class="notif-dot">3</div>
            </div>

            <button class="avatar-btn admin-avatar">
                <?= htmlspecialchars($_SESSION['name']) ?>
                <span>Admin</span>
            </button>

            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>

        </div>
    </div>


    <div class="main-content">

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
                <div style="font-size:22px; font-weight:700;">
                    <?= $total_users ?>
                </div>
            </div>

            <div class="card panel">
                <div class="panel-title">Tools</div>
                <div style="font-size:22px; font-weight:700;">
                    <?= $total_tools ?>
                </div>
            </div>

            <div class="card panel">
                <div class="panel-title">Pending</div>
                <div style="font-size:22px; font-weight:700;">
                    <?= $pending ?>
                </div>
            </div>

            <div class="card panel">
                <div class="panel-title">Disputes</div>
                <div style="font-size:22px; font-weight:700;">
                    <?= $disputes ?>
                </div>
            </div>

        </div>

        <!-- QUICK ACTIONS -->
        <div class="card">
            <div class="panel-header">
                <div class="panel-title">Quick Actions</div>
            </div>

            <div class="links" style="margin-top:15px;">

                <a class="link-card" href="tools.php">
                    <i class="fa fa-wrench"></i> Manage Tools
                </a>

                <a class="link-card" href="member-profile.php">
                    <i class="fa fa-users"></i> Manage Members
                </a>

                <a class="link-card" href="reservations.php">
                    <i class="fa fa-calendar-check"></i> Reservations
                </a>

                <a class="link-card" href="tool_Details.php">
                    <i class="fa fa-search"></i> Tool Details
                </a>

                <a class="link-card" href="chat.php">
                    <i class="fa fa-comments"></i> Messages
                </a>

                <a class="link-card" href="#">
                    <i class="fa fa-scale-balanced"></i> Disputes
                </a>

            </div>
        </div>

    </div>

</div>

</body>
</html>
