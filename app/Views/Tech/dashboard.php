<?php
/* =========================================================
   1) SESSION + AUTH
========================================================= */
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technical') {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();
$uid  = intval($_SESSION['user_id']);

/* =========================================================
   2) STATS
========================================================= */
// Total maintenance jobs assigned (all maintenance logs)
$r = $conn->query("SELECT COUNT(*) AS c FROM maintenance_logs");
$total_jobs = $r->fetch_assoc()['c'];

// Tools with external repair action
$r = $conn->query("SELECT COUNT(*) AS c FROM maintenance_logs WHERE action LIKE '%External%'");
$external_repairs = $r->fetch_assoc()['c'];

// Unread messages
$r = $conn->query("SELECT COUNT(*) AS c FROM messages WHERE receiver_id = $uid AND is_read = 0");
$unread_msgs = $r->fetch_assoc()['c'];

// Total tools in system
$r = $conn->query("SELECT COUNT(*) AS c FROM tools");
$total_tools = $r->fetch_assoc()['c'];

// Tools available
$r = $conn->query("SELECT COUNT(*) AS c FROM tools WHERE availability = 1");
$available_tools = $r->fetch_assoc()['c'];

// Recent maintenance logs
$recent_logs = $conn->query("
    SELECT ml.*, t.name AS tool_name
    FROM maintenance_logs ml
    LEFT JOIN tools t ON ml.tool_id = t.tool_id
    ORDER BY ml.id DESC
    LIMIT 6
");

// Damage declarations pending review
$r = $conn->query("SELECT COUNT(*) AS c FROM damage_declarations WHERE status = 'pending'");
$pending_damage = $r->fetch_assoc()['c'];
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
<style>
.stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 28px; }
.stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px; position: relative; overflow: hidden; }
.stat-card::before { content: ''; position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; border-radius: 50%; background: rgba(230,57,70,.05); pointer-events: none; }
.stat-icon { width: 38px; height: 38px; border-radius: 9px; background: rgba(230,57,70,.1); display: flex; align-items: center; justify-content: center; color: var(--red); font-size: 1rem; margin-bottom: 12px; }
.stat-num  { font-size: 1.8rem; font-weight: 700; line-height: 1; }
.stat-lbl  { font-size: .75rem; color: var(--text-muted); margin-top: 5px; text-transform: uppercase; }

.two-col { display: grid; grid-template-columns: 1.2fr 1fr; gap: 22px; }
@media(max-width:800px){ .two-col { grid-template-columns: 1fr; } }

.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px; }
.card-title { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 8px; }

.log-item { display: flex; gap: 12px; align-items: flex-start; padding: 12px 0; border-bottom: 1px solid var(--border); }
.log-item:last-child { border-bottom: none; padding-bottom: 0; }
.log-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--red); flex-shrink: 0; margin-top: 5px; }
.log-body { flex: 1; }
.log-tool { font-weight: 600; font-size: .88rem; }
.log-action { font-size: .8rem; color: var(--text-muted); margin-top: 2px; }
.log-date { font-size: .75rem; color: var(--text-dim); white-space: nowrap; }

.quick-links { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.ql-btn { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; text-align: center; text-decoration: none; color: var(--text); transition: all .2s; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.ql-btn:hover { border-color: var(--red); background: rgba(230,57,70,.06); }
.ql-btn i { font-size: 1.4rem; color: var(--red); }
.ql-btn span { font-size: .82rem; font-weight: 600; }
.ql-btn small { font-size: .72rem; color: var(--text-muted); }

.welcome-banner { background: linear-gradient(135deg, #161616 0%, #1a1a2e 100%); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px 28px; margin-bottom: 24px; display: flex; align-items: center; gap: 20px; }
.welcome-icon { width: 56px; height: 56px; border-radius: 50%; background: rgba(14,165,233,.12); display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: 1.5rem; flex-shrink: 0; }
.welcome-text h2 { font-size: 1.1rem; font-weight: 700; }
.welcome-text p  { font-size: .83rem; color: var(--text-muted); margin-top: 4px; }
</style>
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
        <a href="dashboard.php" class="nav-link active">
            <i class="fa fa-gauge"></i> Dashboard
        </a>
        <a href="../Tools/tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
        </a>
        <a href="repair_estimator.php" class="nav-link">
            <i class="fa fa-calculator"></i> Repair Estimator
        </a>
        <a href="external_repair.php" class="nav-link">
            <i class="fa fa-arrow-up-right-from-square"></i> External Repairs
        </a>
        <a href="cost-of-parts.php" class="nav-link">
            <i class="fa fa-coins"></i> Parts Cost
        </a>
        <a href="request-parts.php" class="nav-link">
            <i class="fa fa-box"></i> Request Parts
        </a>
        <a href="tech-tips.php" class="nav-link">
            <i class="fa fa-lightbulb"></i> Tech Tips
        </a>
        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
            <?php if ($unread_msgs > 0): ?>
                <span class="nav-count"><?= $unread_msgs ?></span>
            <?php endif; ?>
        </a>
        <a href="profile.php" class="nav-link">
            <i class="fa fa-user"></i> Profile
        </a>
    </div>
</div>

<!-- RIGHT -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <button class="hamburger"><i class="fa fa-bars"></i></button>
        <div class="topbar-title">Dashboard</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#0ea5e9;color:#fff;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Tech</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <!-- MAIN -->
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
            <div class="card">
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
                                <?php if ($log['issue'] ?? null): ?>
                                    — <?= htmlspecialchars($log['issue']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="log-date"><?= $log['date'] ? date('M d', strtotime($log['date'])) : '—' ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <!-- QUICK LINKS -->
            <div>
                <div class="card">
                    <div class="card-title">
                        <i class="fa fa-bolt" style="color:var(--red);"></i>
                        Quick Actions
                    </div>
                    <div class="quick-links">
                        <a href="repair_estimator.php" class="ql-btn">
                            <i class="fa fa-calculator"></i>
                            <span>Repair Estimator</span>
                            <small>Calculate repair costs</small>
                        </a>
                        <a href="external_repair.php" class="ql-btn">
                            <i class="fa fa-truck"></i>
                            <span>External Repairs</span>
                            <small>Track sent tools</small>
                        </a>
                        <a href="cost-of-parts.php" class="ql-btn">
                            <i class="fa fa-coins"></i>
                            <span>Parts Cost</span>
                            <small>View parts catalog</small>
                        </a>
                        <a href="request-parts.php" class="ql-btn">
                            <i class="fa fa-box-open"></i>
                            <span>Request Parts</span>
                            <small>Order replacement parts</small>
                        </a>
                        <a href="../Tools/tools.php" class="ql-btn">
                            <i class="fa fa-wrench"></i>
                            <span>View Tools</span>
                            <small>Browse tool inventory</small>
                        </a>
                        <a href="tech-tips.php" class="ql-btn">
                            <i class="fa fa-lightbulb"></i>
                            <span>Tech Tips</span>
                            <small>Maintenance guides</small>
                        </a>
                    </div>
                </div>

                <div class="card" style="margin-top:16px;">
                    <div class="card-title">
                        <i class="fa fa-chart-simple" style="color:var(--red);"></i>
                        Tool Availability
                    </div>
                    <div style="margin-bottom:10px;font-size:.82rem;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                            <span style="color:var(--text-muted);">Available</span>
                            <span style="color:#2dbe6c;font-weight:700;"><?= $available_tools ?> / <?= $total_tools ?></span>
                        </div>
                        <div style="height:8px;background:var(--border);border-radius:10px;overflow:hidden;">
                            <div style="height:100%;background:#2dbe6c;border-radius:10px;width:<?= $total_tools > 0 ? round(($available_tools/$total_tools)*100) : 0 ?>%;transition:width .6s;"></div>
                        </div>
                    </div>
                    <div style="font-size:.82rem;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                            <span style="color:var(--text-muted);">Unavailable</span>
                            <span style="color:var(--red);font-weight:700;"><?= $total_tools - $available_tools ?> / <?= $total_tools ?></span>
                        </div>
                        <div style="height:8px;background:var(--border);border-radius:10px;overflow:hidden;">
                            <div style="height:100%;background:var(--red);border-radius:10px;width:<?= $total_tools > 0 ? round((($total_tools - $available_tools)/$total_tools)*100) : 0 ?>%;transition:width .6s;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /two-col -->

    </div><!-- /main-content -->
</div><!-- /layout-right -->

<script>
document.querySelector('.hamburger')?.addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('open');
});
</script>
</body>
</html>