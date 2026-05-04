<?php
require_once __DIR__ . "../../../../Core/database.php";

$db   = Database::getInstance();
$conn = $db->getConnection();

/* ── Stats ── */
$r           = $conn->query("SELECT COUNT(*) AS c FROM tools WHERE availability = 1");
$total_tools = $r->fetch_assoc()['c'];

$r           = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role IN ('client','technical')");
$total_users = $r->fetch_assoc()['c'];

$r            = $conn->query("SELECT COUNT(*) AS c FROM reservations");
$total_rents  = $r->fetch_assoc()['c'];

/* ── Categories ── */
$categories = $conn->query("
    SELECT c.*, COUNT(t.tool_id) AS tool_count
    FROM category c
    LEFT JOIN tools t ON t.category_id = c.category_id AND t.availability = 1
    GROUP BY c.category_id
    ORDER BY tool_count DESC
    LIMIT 4
");

/* ── Top Tools ── */
$top_tools = $conn->query("
    SELECT t.*, c.name AS cat_name
    FROM tools t
    JOIN category c ON t.category_id = c.category_id
    WHERE t.availability = 1
    ORDER BY t.created_at DESC
    LIMIT 3
");

$logged_in  = isset($_SESSION['user_id']);
$action_url = $logged_in ? '../' . $_SESSION['role'] . '/dashboard.php' : '../Auth/login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Home</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../app/assets/Css/home.css">
<link rel="stylesheet" href="../app/assets/Css/admin.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <img src="../app/assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="role-badge" style="background:#6b7280;color:#fff;">GUEST</div>

    <div class="sidebar-nav">
        <a href="../app/Client/home.php" class="nav-link active">
            <i class="fa fa-home"></i> Home
        </a>
        <a href="../app/Views/Auth/login.php" class="nav-link">
            <i class="fa fa-right-to-bracket"></i> Login
        </a>
        <a href="../app/Views/Auth/register.php" class="nav-link">
            <i class="fa fa-user-plus"></i> Register
        </a>
    </div>
</div>

<!-- RIGHT SIDE -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-title">Welcome</div>
        <div class="topbar-right">
            <a href="../app/Views/Auth/login.php"    class="btn btn-ghost btn-sm" style="margin-right:8px;">Log in</a>
            <a href="../app/Views/Auth/register.php" class="btn btn-red  btn-sm">Register</a>
        </div>
    </div>

    <div class="main-content">

        <!-- HERO -->
        <div class="hero">
            <h1>Welcome to <span>Tool Hub</span> 👋</h1>
            <p>Rent, share and discover specialized tools — easily and securely.</p>
            <a href="../app/Views/Auth/login.php" class="btn-main">
                <i class="fa fa-bolt"></i> Get Started
            </a>
        </div>

        <!-- FEATURES -->
        <div class="section-block">
            <h2>Why Tool Hub?</h2>
            <div class="stats-grid2">

                <div class="stat-card2">
                    <i class="fa fa-wrench"></i>
                    <h3>Share Tools</h3>
                    <p>List your tools and earn money</p>
                </div>

                <div class="stat-card2">
                    <i class="fa fa-calendar-check"></i>
                    <h3>Fast Booking</h3>
                    <p>Reserve tools instantly</p>
                </div>

                <div class="stat-card2">
                    <i class="fa fa-shield-halved"></i>
                    <h3>Trusted &amp; Secure</h3>
                    <p>Trust-score verified members</p>
                </div>

                <div class="stat-card2">
                    <i class="fa fa-comments"></i>
                    <h3>Built-in Chat</h3>
                    <p>Message owners directly</p>
                </div>

            </div>
        </div>


        <!-- FOOTER -->
        <div class="home-footer">
            &copy; <?= date('Y') ?> Tool Hub — All Rights Reserved.
        </div>

    </div><!-- /.main-content -->
</div><!-- /.layout-right -->

</body>
</html>