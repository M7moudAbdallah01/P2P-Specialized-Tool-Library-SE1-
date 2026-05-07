<?php
/* =========================================================
   1) SESSION + AUTH
========================================================= */
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();
$uid  = intval($_SESSION['user_id']);

/* =========================================================
   2) SUBMIT REPORT / DISPUTE
========================================================= */
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    $tool_id          = intval($_POST['tool_id'] ?? 0);
    $reported_user_id = intval($_POST['reported_user_id'] ?? 0);
    $reason           = trim($conn->real_escape_string($_POST['reason'] ?? ''));

    if (!$tool_id || !$reason) {
        $error = 'Please fill all required fields.';
    } else {
        $conn->query("
            INSERT INTO dispute (reporter_id, reported_user_id, tool_id, reason, status, created_at)
            VALUES ($uid, $reported_user_id, $tool_id, '$reason', 'open', NOW())
        ");
        $success = 'Your report has been submitted. Our admin team will review it shortly.';
    }
}

/* =========================================================
   3) LOAD USER'S RESERVATIONS (to pick which tool)
========================================================= */
$reservations = $conn->query("
    SELECT r.*, t.name AS tool_name, t.tool_id, t.owner_id,
           u.name AS owner_name
    FROM reservations r
    JOIN tools t ON r.tool_id  = t.tool_id
    JOIN users u ON t.owner_id = u.user_id
    WHERE r.user_id = $uid AND r.status IN ('completed','confirmed')
    ORDER BY r.reservation_id DESC
");

/* =========================================================
   4) LOAD MY EXISTING DISPUTES
========================================================= */
$my_disputes = $conn->query("
    SELECT d.*, t.name AS tool_name,
           rp.name AS reported_name
    FROM dispute d
    LEFT JOIN tools t  ON d.tool_id          = t.tool_id
    LEFT JOIN users rp ON d.reported_user_id  = rp.user_id
    WHERE d.reporter_id = $uid
    ORDER BY d.created_at DESC
");

/* =========================================================
   5) UNREAD MESSAGES COUNT
========================================================= */
$r = $conn->query("SELECT COUNT(*) AS c FROM messages WHERE receiver_id = $uid AND is_read = 0");
$unread_msgs = $r->fetch_assoc()['c'];

$r2 = $conn->query("SELECT COUNT(*) AS c FROM reservations WHERE user_id = $uid AND status = 'pending'");
$pending_res = $r2->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Tool Report</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<style>
.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 1.3rem; font-weight: 700; }
.page-header p  { font-size: .85rem; color: var(--text-muted); margin-top: 4px; }

.two-col { display: grid; grid-template-columns: 1fr 1.2fr; gap: 24px; }
@media(max-width:760px){ .two-col { grid-template-columns: 1fr; } }

.form-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; }
.card-title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 8px; }

.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: .75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 7px; }
.form-group select, .form-group textarea {
    width: 100%; background: var(--surface2); border: 1px solid var(--border);
    color: var(--text); padding: 10px 14px; border-radius: var(--radius);
    font-size: .88rem; outline: none; transition: border-color .2s;
}
.form-group select:focus, .form-group textarea:focus { border-color: var(--red); }
.form-group textarea { resize: vertical; min-height: 120px; }
.form-group .hint { font-size: .75rem; color: var(--text-dim); margin-top: 4px; }

.btn-submit { display: inline-flex; align-items: center; gap: 6px; padding: 11px 22px; background: var(--red); color: #fff; border: none; border-radius: var(--radius); font-size: .85rem; font-weight: 600; cursor: pointer; transition: background .2s; width: 100%; justify-content: center; }
.btn-submit:hover { background: var(--red-dim); }

.alert-msg { padding: 12px 16px; border-radius: var(--radius); font-size: .85rem; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
.alert-success { background: rgba(45,190,108,.1); border: 1px solid rgba(45,190,108,.3); color: #2dbe6c; }
.alert-error   { background: rgba(230,57,70,.1);  border: 1px solid rgba(230,57,70,.3);  color: #e63946; }

/* DISPUTES LIST */
.dispute-item { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--radius); padding: 14px 16px; margin-bottom: 10px; display: flex; flex-direction: column; gap: 6px; }
.dispute-item:last-child { margin-bottom: 0; }
.di-head { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.di-id   { font-size: .78rem; color: var(--text-muted); }
.di-tool { font-weight: 600; font-size: .9rem; flex: 1; }
.dpill { display: inline-flex; padding: 2px 9px; border-radius: 20px; font-size: .7rem; font-weight: 700; text-transform: uppercase; }
.dpill-open     { background: rgba(230,57,70,.12); color: #e63946; }
.dpill-resolved { background: rgba(45,190,108,.12); color: #2dbe6c; }
.dpill-closed   { background: rgba(107,114,128,.12); color: #9ca3af; }
.di-reason  { font-size: .83rem; color: var(--text-muted); line-height: 1.5; background: var(--surface); padding: 8px 10px; border-radius: 6px; border: 1px solid var(--border); }
.di-meta    { font-size: .75rem; color: var(--text-dim); display: flex; gap: 14px; flex-wrap: wrap; }

.info-box { background: rgba(99,102,241,.06); border: 1px solid rgba(99,102,241,.2); border-radius: var(--radius); padding: 14px 16px; margin-bottom: 20px; font-size: .83rem; color: var(--text-muted); display: flex; gap: 10px; }
.info-box i { color: #6366f1; font-size: 1rem; flex-shrink: 0; margin-top: 2px; }

.empty-msg { text-align: center; padding: 40px 20px; color: var(--text-muted); }
.empty-msg i { font-size: 2rem; display: block; margin-bottom: 10px; opacity: .3; }
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

    <div class="role-badge" style="background:#6366f1;color:#fff;">CLIENT</div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link">
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
        <a href="tool-report.php" class="nav-link active">
            <i class="fa fa-scale-balanced"></i> Tool Report
        </a>
        <a href="profile.php" class="nav-link">
            <i class="fa fa-user"></i> My Profile
        </a>
    </div>
</div>

<!-- RIGHT -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-title">Tool Report</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#6366f1;color:#fff;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Client</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content">

        <div class="page-header">
            <h1><i class="fa fa-scale-balanced" style="color:var(--red);margin-right:8px;"></i>Report an Issue</h1>
            <p>Submit a dispute or report a problem related to a tool or another user.</p>
        </div>

        <!-- ALERTS -->
        <?php if ($success): ?>
            <div class="alert-msg alert-success"><i class="fa fa-circle-check"></i> <?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-msg alert-error"><i class="fa fa-circle-xmark"></i> <?= $error ?></div>
        <?php endif; ?>

        <div class="two-col">

            <!-- SUBMIT FORM -->
            <div>
                <div class="info-box">
                    <i class="fa fa-circle-info"></i>
                    <div>Reports are reviewed by our admin team within 24–48 hours. Provide as much detail as possible to help us resolve the issue quickly.</div>
                </div>

                <div class="form-card">
                    <div class="card-title">
                        <i class="fa fa-flag" style="color:var(--red);"></i>
                        Submit a Report
                    </div>
                    <form method="POST">

                        <div class="form-group">
                            <label>Select Tool / Reservation <span style="color:var(--red);">*</span></label>
                            <select name="tool_id" id="tool_select" required onchange="fillOwner(this)">
                                <option value="">-- Select a reservation --</option>
                                <?php if ($reservations): while ($r = $reservations->fetch_assoc()): ?>
                                <option value="<?= $r['tool_id'] ?>"
                                        data-owner="<?= $r['owner_id'] ?>"
                                        data-oname="<?= htmlspecialchars($r['owner_name']) ?>">
                                    <?= htmlspecialchars($r['tool_name']) ?> (#<?= $r['reservation_id'] ?>) — <?= date('M d, Y', strtotime($r['start_date'])) ?>
                                </option>
                                <?php endwhile; endif; ?>
                            </select>
                            <div class="hint">Only completed or confirmed reservations are listed.</div>
                        </div>

                        <div class="form-group">
                            <label>Reported User</label>
                            <input type="hidden" name="reported_user_id" id="reported_user_id" value="0">
                            <div id="owner_display" style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius);padding:10px 14px;font-size:.88rem;color:var(--text-muted);">
                                Will be filled automatically after selecting a tool.
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Reason / Description <span style="color:var(--red);">*</span></label>
                            <textarea name="reason" placeholder="Describe the issue in detail. What happened? When? Any evidence?" required><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" name="submit_report" class="btn-submit">
                            <i class="fa fa-paper-plane"></i> Submit Report
                        </button>
                    </form>
                </div>
            </div>

            <!-- MY DISPUTES -->
            <div>
                <div class="form-card">
                    <div class="card-title">
                        <i class="fa fa-list-check" style="color:var(--red);"></i>
                        My Reports
                    </div>

                    <?php if (!$my_disputes || $my_disputes->num_rows === 0): ?>
                        <div class="empty-msg">
                            <i class="fa fa-scale-balanced"></i>
                            You have no reports yet.
                        </div>
                    <?php else: ?>
                        <?php while ($d = $my_disputes->fetch_assoc()):
                            $pill = match($d['status']) {
                                'resolved' => 'dpill-resolved',
                                'closed'   => 'dpill-closed',
                                default    => 'dpill-open'
                            };
                        ?>
                        <div class="dispute-item">
                            <div class="di-head">
                                <span class="di-id">#<?= $d['dispute_id'] ?></span>
                                <span class="di-tool"><i class="fa fa-wrench" style="color:var(--red);font-size:.75rem;margin-right:3px;"></i><?= htmlspecialchars($d['tool_name'] ?? '—') ?></span>
                                <span class="dpill <?= $pill ?>"><?= strtoupper($d['status']) ?></span>
                            </div>
                            <div class="di-reason"><?= nl2br(htmlspecialchars($d['reason'])) ?></div>
                            <div class="di-meta">
                                <span><i class="fa fa-user-slash" style="color:#888;"></i> Against: <?= htmlspecialchars($d['reported_name'] ?? 'Unknown') ?></span>
                                <span><i class="fa fa-clock"></i> <?= date('M d, Y', strtotime($d['created_at'])) ?></span>
                            </div>
                            <?php if ($d['status'] === 'resolved' && $d['admin_note']): ?>
                            <div style="font-size:.78rem;background:rgba(45,190,108,.06);border:1px solid rgba(45,190,108,.2);border-radius:6px;padding:8px 10px;color:#2dbe6c;">
                                <i class="fa fa-circle-check"></i> Admin note: <?= htmlspecialchars($d['admin_note']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /two-col -->

    </div><!-- /main-content -->
</div><!-- /layout-right -->

<script>
function fillOwner(select) {
    const opt = select.options[select.selectedIndex];
    const oid  = opt.dataset.owner  || '0';
    const name = opt.dataset.oname  || '—';
    document.getElementById('reported_user_id').value = oid;
    document.getElementById('owner_display').textContent = oid !== '0'
        ? '👤 ' + name + ' (ID #' + oid + ')'
        : 'Will be filled automatically after selecting a tool.';
}
</script>
</body>
</html>