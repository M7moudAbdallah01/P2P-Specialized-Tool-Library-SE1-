<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";
require_once __DIR__ . "/../../Models/Report.php";
$reportModel = new Report();
$totalRevenue = $reportModel->getTotalRevenue();
$zoneRevenue = $reportModel->getRevenueByZone();
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}

 
$db = Database::getInstance();
$conn = $db->getConnection();


$db = Database::getInstance();
$conn = $db->getConnection();
 
$r = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'client' or role = 'technical'");
$total_users = $r->fetch_assoc()['c'];
 
$r = $conn->query("SELECT COUNT(*) AS c FROM tools");
$total_tools = $r->fetch_assoc()['c'];
 
$r = $conn->query("SELECT COUNT(*) AS c FROM reservations WHERE status = 'pending'");
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

    <div class="role-badge role-admin">
        ADMIN
    </div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link active">
            <i class="fa fa-gauge"></i> Dashboard
        </a>

        <a href="../Tools/tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
        </a>

        <a href="../Tools/categories.php" class="nav-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg>Categories</a>

        <a href="members.php" class="nav-link">
            <i class="fa fa-users"></i> Members
        </a>

        <a href="reservations.php" class="nav-link">
            <i class="fa fa-calendar"></i> Reservations
        </a>

        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
        </a>

        <a class="nav-link" href="reports.php">
            <i class="fa fa-scale-balanced"></i> Disputes & Reports
        </a>
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



            <div class="welcome">
        <h1>Financial Dashboard</h1>
        <p>Real-time revenue tracking and zone performance.</p>
    </div>

    <!-- كروت الإحصائيات -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">$<?php echo number_format($totalRevenue, 2); ?></div>
        </div>
        
        <div class="stat-card" style="border-left-color: var(--accent-yellow);">
            <div class="stat-label">Active Zones</div>
            <div class="stat-value"><?php echo count($zoneRevenue); ?></div>
            <div class="stat-label">Monitored Locations</div>
        </div>
    </div>

    <!-- جدول أرباح المناطق -->
    <div class="card">
        <h3 style="margin-top:0; color: #fff;">Revenue by Zone</h3>
        <table>
            <thead>
                <tr>
                    <th>Zone Name</th>
                    <th style="text-align: right;">Total Revenue</th>
                    <th style="text-align: right;">Performance</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($zoneRevenue)): ?>
                    <?php foreach($zoneRevenue as $zone): ?>
                        <tr>
                            <td><span class="tool-name"><?php echo htmlspecialchars($zone['zone_name']); ?></span></td>
                            <td style="text-align: right; color: var(--accent-green); font-weight: 600;">
                                $<?php echo number_format($zone['revenue'], 2); ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="progress-bar-bg">
                                    <?php 
                                        $percentage = ($totalRevenue > 0) ? ($zone['revenue'] / $totalRevenue) * 100 : 0; 
                                    ?>
                                    <div class="progress-bar-fill" style="width: <?php echo $percentage; ?>%;"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center; padding:20px;">No data available yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Rental Escalation Tracking </h2>
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
            <?php if (!empty($rentals)): ?>
                <?php foreach($rentals as $rental): ?>
                <tr>
                    <td>#<?php echo $rental['rental_id']; ?></td>
                    <td><?php echo htmlspecialchars($rental['user_name'] ?? 'Unknown'); ?></td>
                    <td><?php echo htmlspecialchars($rental['tool_name'] ?? 'Unknown'); ?></td>
                    <td><?php echo $rental['return_date']; ?></td>
                    <td class="level-<?php echo $rental['escalation_level']; ?>">
                        $<?php echo number_format($rental['penalty_fee'], 2); ?>
                    </td>
                    <td>
                        <span class="level-<?php echo $rental['escalation_level']; ?>">
                            Level <?php echo $rental['escalation_level']; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge"><?php echo strtoupper($rental['status']); ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding: 50px;">No rental records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>




    </div>




</div>

</body>
</html>
