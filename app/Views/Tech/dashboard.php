<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technical') {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();
$uid  = intval($_SESSION['user_id']);


$r = $conn->query("SELECT COUNT(*) AS c FROM maintenance_logs");
$total_jobs = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) AS c FROM maintenance_logs WHERE action LIKE '%External%'");
$external_repairs = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) AS c FROM messages WHERE receiver_id = $uid AND is_read = 0");
$unread_msgs = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) AS c FROM tools");
$total_tools = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) AS c FROM tools WHERE availability = 1");
$available_tools = $r->fetch_assoc()['c'];

$recent_logs = $conn->query("
    SELECT ml.*, t.name AS tool_name
    FROM maintenance_logs ml
    LEFT JOIN tools t ON ml.tool_id = t.tool_id
    ORDER BY ml.id DESC
    LIMIT 6
");

$r = $conn->query("SELECT COUNT(*) AS c FROM repair_requests WHERE status = 'pending'");
$pending_damage = $r->fetch_assoc()['c'];


$team_members = [];
$max_jobs     = 1;

$team_result = $conn->query("SELECT user_id, name FROM users WHERE role = 'technical' ORDER BY name LIMIT 5");
if ($team_result) {
    while ($row = $team_result->fetch_assoc()) {
        $row['jobs_done'] = 0;
        $team_members[] = $row;
    }
}

$cols_result = $conn->query("SHOW COLUMNS FROM maintenance_logs");
$ml_columns  = [];
if ($cols_result) {
    while ($c = $cols_result->fetch_assoc()) $ml_columns[] = $c['Field'];
}

$user_col = null;
foreach (['technician_id','user_id','performed_by','done_by','tech_id','assigned_to'] as $candidate) {
    if (in_array($candidate, $ml_columns)) { $user_col = $candidate; break; }
}

if ($user_col) {
    foreach ($team_members as &$member) {
        $uid_m = intval($member['user_id']);
        $cr = $conn->query("SELECT COUNT(*) AS c FROM maintenance_logs WHERE $user_col = $uid_m");
        if ($cr) {
            $member['jobs_done'] = intval($cr->fetch_assoc()['c']);
            if ($member['jobs_done'] > $max_jobs) $max_jobs = $member['jobs_done'];
        }
    }
    unset($member);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Tech Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/tech.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="role-badge" style="background:#0ea5e9;color:#fff;">TECH</div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link active"><i class="fa fa-gauge"></i> Dashboard</a>
        <a href="repair_estimator.php" class="nav-link"><i class="fa fa-calculator"></i> Repair Estimator</a>
        <a href="external_repair.php" class="nav-link"><i class="fa fa-arrow-up-right-from-square"></i> External Repairs</a>
        <a href="reports.php" class="nav-link ">
            <i class="fa fa-screwdriver-wrench"></i> Repair Requests
            <?php if ($pending_damage > 0): ?>
                <span class="nav-count"><?= $pending_damage ?></span>
            <?php endif; ?>
        </a>
        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
            <?php if ($unread_msgs > 0): ?><span class="nav-count"><?= $unread_msgs ?></span><?php endif; ?>
        </a>
    </div>
</div>

<!-- RIGHT -->
<div class="layout-right">
    <div class="topbar">
        <button class="hamburger"><i class="fa fa-bars"></i></button>
        <div class="topbar-title">Dashboard</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#0ea5e9;color:#fff;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Tech</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn"><i class="fa fa-right-from-bracket"></i></a>
        </div>
    </div>

    <div class="main-content">

        <!-- WELCOME -->
        <div class="welcome-banner">
            <div class="welcome-icon"><i class="fa fa-screwdriver-wrench"></i></div>
            <div class="welcome-text">
                <h2>Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?>!</h2>
                <p>Here's an overview of current maintenance activity and tool status.</p>
            </div>
            <?php if ($pending_damage > 0): ?>
            <div style="margin-left:auto;background:rgba(230,57,70,.1);border:1px solid rgba(230,57,70,.3);border-radius:var(--radius);padding:10px 16px;text-align:center;">
                <div style="font-size:1.3rem;font-weight:700;color:var(--red);"><?= $pending_damage ?></div>
                <div style="font-size:.72rem;color:var(--text-muted);">Damage Reports<br>Pending</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- STATS -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa fa-clipboard-list"></i></div>
                <div class="stat-num"><?= $total_jobs ?></div>
                <div class="stat-lbl">Total Jobs</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa fa-arrow-up-right-from-square"></i></div>
                <div class="stat-num"><?= $external_repairs ?></div>
                <div class="stat-lbl">External Repairs</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa fa-wrench"></i></div>
                <div class="stat-num"><?= $total_tools ?></div>
                <div class="stat-lbl">Total Tools</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa fa-circle-check"></i></div>
                <div class="stat-num" style="color:#2dbe6c;"><?= $available_tools ?></div>
                <div class="stat-lbl">Available</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa fa-comments"></i></div>
                <div class="stat-num" style="color:<?= $unread_msgs > 0 ? 'var(--red)' : 'inherit' ?>;"><?= $unread_msgs ?></div>
                <div class="stat-lbl">Unread Msgs</div>
            </div>
        </div>

        <div class="two-col">

            <!-- RECENT MAINTENANCE LOGS -->
            <div class="card" style="width: 1250px;">
                <div class="card-title">
                    <i class="fa fa-clock-rotate-left" style="color:var(--red);"></i>
                    Recent Maintenance Logs
                </div>
                <?php if (!$recent_logs || $recent_logs->num_rows === 0): ?>
                    <div style="text-align:center;padding:30px;color:var(--text-muted);">
                        <i class="fa fa-clipboard" style="font-size:2rem;opacity:.2;"></i>
                        <p style="margin-top:10px;">No maintenance records yet.</p>
                    </div>
                <?php else: ?>
                    <?php while ($log = $recent_logs->fetch_assoc()): ?>
                    <div class="log-item">
                        <div class="log-dot"></div>
                        <div class="log-body">
                            <div class="log-tool"><?= htmlspecialchars($log['tool_name'] ?? 'Tool #'.$log['tool_id']) ?></div>
                            <div class="log-action">
                                <?= htmlspecialchars($log['action'] ?? 'Maintenance') ?>
                                <?php if ($log['issue'] ?? null): ?> — <?= htmlspecialchars($log['issue']) ?><?php endif; ?>
                            </div>
                        </div>
                        <div class="log-date"><?= $log['date'] ? date('M d', strtotime($log['date'])) : '—' ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>


    </div><!-- /main-content -->
</div><!-- /layout-right -->

<script>
document.querySelector('.hamburger')?.addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('open');
});
</script>
</body>
</html>