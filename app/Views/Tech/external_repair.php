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

$r = $conn->query("SELECT COUNT(*) AS c FROM messages WHERE receiver_id = $uid AND is_read = 0");
$unread_msgs = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) AS c FROM repair_requests WHERE status = 'pending'");
$pending_damage = $r->fetch_assoc()['c'];
function tableExists($conn, $table) {
    $t = $conn->real_escape_string($table);
    $r = $conn->query("SHOW TABLES LIKE '$t'");
    return $r && $r->num_rows > 0;
}

$HAS_EXT_TABLE = tableExists($conn, 'external_repairs');

$tools_result = $conn->query("SELECT tool_id, name FROM tools ORDER BY name");
$tools = [];
if ($tools_result) while ($row = $tools_result->fetch_assoc()) $tools[] = $row;

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_repair'])) {
    $tool_id    = intval($_POST['tool_id'] ?? 0);
    $shop_name  = $conn->real_escape_string(trim($_POST['shop_name'] ?? ''));
    $issue      = $conn->real_escape_string(trim($_POST['issue'] ?? ''));
    $sent_date  = $conn->real_escape_string($_POST['sent_date'] ?? date('Y-m-d'));
    $est_return = $conn->real_escape_string($_POST['est_return'] ?? '');
    $cost       = floatval($_POST['cost'] ?? 0);
    $notes      = $conn->real_escape_string(trim($_POST['notes'] ?? ''));

    if ($tool_id && $shop_name && $issue) {
        $saved = false;

        if ($HAS_EXT_TABLE) {
            $stmt = $conn->prepare("
                INSERT INTO external_repairs (tool_id, shop_name, issue, sent_date, est_return_date, cost, notes, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'sent', ?)
            ");
            if ($stmt) {
                $stmt->bind_param("issssdsi", $tool_id, $shop_name, $issue, $sent_date, $est_return, $cost, $notes, $uid);
                if ($stmt->execute()) {
                    $conn->query("UPDATE tools SET availability = 0 WHERE tool_id = $tool_id");
                    $conn->query("INSERT INTO maintenance_logs (tool_id, action, issue, date) VALUES ($tool_id, 'External Repair — sent to $shop_name', '$issue', NOW())");
                    $success = "External repair recorded successfully!";
                    $saved = true;
                } else { $error = $stmt->error; }
                $stmt->close();
            }
        }

        if (!$saved && !$error) {
            $action = $conn->real_escape_string("External Repair — sent to $shop_name");
            $conn->query("INSERT INTO maintenance_logs (tool_id, action, issue, date) VALUES ($tool_id, '$action', '$issue', NOW())");
            $conn->query("UPDATE tools SET availability = 0 WHERE tool_id = $tool_id");
            $success = "Repair logged in maintenance records.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && $HAS_EXT_TABLE) {
    $repair_id = intval($_POST['repair_id'] ?? 0);
    $status    = $conn->real_escape_string($_POST['status'] ?? '');
    $tool_id   = intval($_POST['tool_id_upd'] ?? 0);

    if ($repair_id && in_array($status, ['sent','in_progress','returned','cancelled'])) {
        $conn->query("UPDATE external_repairs SET status='$status' WHERE id=$repair_id");
        if ($status === 'returned') {
            $conn->query("UPDATE tools SET availability = 1 WHERE tool_id = $tool_id");
        }
        $success = "Status updated!";
    }
}

$repairs = [];
if ($HAS_EXT_TABLE) {
    $repairs_result = $conn->query("
        SELECT er.*, t.name AS tool_name
        FROM external_repairs er
        LEFT JOIN tools t ON er.tool_id = t.tool_id
        ORDER BY er.sent_date DESC
    ");
    if ($repairs_result) while ($row = $repairs_result->fetch_assoc()) $repairs[] = $row;
}

if (empty($repairs)) {
    $fallback = $conn->query("
        SELECT ml.id, ml.tool_id, ml.action AS issue, ml.date AS sent_date,
               '' AS shop_name, 0 AS cost, '' AS notes, 'sent' AS status,
               t.name AS tool_name
        FROM maintenance_logs ml
        LEFT JOIN tools t ON ml.tool_id = t.tool_id
        WHERE ml.action LIKE '%External%'
        ORDER BY ml.id DESC
    ");
    if ($fallback) while ($r2 = $fallback->fetch_assoc()) $repairs[] = $r2;
}

// Stats
$stat_sent       = count(array_filter($repairs, fn($r) => ($r['status'] ?? '') === 'sent'));
$stat_progress   = count(array_filter($repairs, fn($r) => ($r['status'] ?? '') === 'in_progress'));
$stat_returned   = count(array_filter($repairs, fn($r) => ($r['status'] ?? '') === 'returned'));
$stat_total      = count($repairs);

$status_colors = [
    'sent'        => ['bg'=>'rgba(245,158,11,.12)','text'=>'#f59e0b','label'=>'Sent'],
    'in_progress' => ['bg'=>'rgba(14,165,233,.12)', 'text'=>'#0ea5e9','label'=>'In Progress'],
    'returned'    => ['bg'=>'rgba(45,190,108,.12)', 'text'=>'#2dbe6c','label'=>'Returned'],
    'cancelled'   => ['bg'=>'rgba(107,114,128,.12)','text'=>'#6b7280','label'=>'Cancelled'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - External Repairs</title>
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
        <div class="logo"><img src="../../assets/images/logo.png" alt="Tool Hub Logo"></div>
        <div class="brand-text">TOOL HUB</div>
    </div>
    <div class="role-badge" style="background:#0ea5e9;color:#fff;">TECH</div>
    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
        <a href="repair_estimator.php" class="nav-link"><i class="fa fa-calculator"></i> Repair Estimator</a>
        <a href="external_repair.php" class="nav-link active"><i class="fa fa-arrow-up-right-from-square"></i> External Repairs</a>
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
        <div class="topbar-title">External Repairs</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#0ea5e9;color:#fff;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Tech</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn"><i class="fa fa-right-from-bracket"></i></a>
        </div>
    </div>

    <div class="main-content">

        <div class="page-header">
            <div>
                <h1><i class="fa fa-arrow-up-right-from-square" style="color:var(--red);margin-right:8px;"></i>External Repair Integration</h1>
                <p>Manage tools sent to external repair shops and track their return.</p>
            </div>
            <button class="btn btn-red" onclick="toggleForm()">
                <i class="fa fa-plus"></i> New Repair
            </button>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-error"><i class="fa fa-circle-xmark"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <!-- STATS -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(245,158,11,.1);color:#f59e0b;"><i class="fa fa-truck"></i></div>
                <div class="stat-num"><?= $stat_sent ?></div>
                <div class="stat-lbl">Sent</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(14,165,233,.1);color:#0ea5e9;"><i class="fa fa-gear"></i></div>
                <div class="stat-num"><?= $stat_progress ?></div>
                <div class="stat-lbl">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(45,190,108,.1);color:#2dbe6c;"><i class="fa fa-circle-check"></i></div>
                <div class="stat-num"><?= $stat_returned ?></div>
                <div class="stat-lbl">Returned</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(230,57,70,.1);color:var(--red);"><i class="fa fa-clipboard-list"></i></div>
                <div class="stat-num"><?= $stat_total ?></div>
                <div class="stat-lbl">Total</div>
            </div>
        </div>

        <!-- ADD FORM (collapsible) -->
        <div id="add-form-wrap" style="display:none;margin-bottom:22px;">
            <div class="card">
                <div class="card-title"><i class="fa fa-plus-circle" style="color:var(--red);"></i> Send Tool for External Repair</div>
                <form method="POST">
                    <div class="two-col">
                        <div>
                            <div class="form-group">
                                <label>Tool *</label>
                                <select name="tool_id" class="form-control" required>
                                    <option value="">— Select Tool —</option>
                                    <?php foreach ($tools as $t): ?>
                                    <option value="<?= $t['tool_id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Repair Shop Name *</label>
                                <input type="text" name="shop_name" class="form-control" placeholder="e.g. Al-Sayed Electronics" required>
                            </div>
                            <div class="form-group">
                                <label>Issue Description *</label>
                                <textarea name="issue" class="form-control" placeholder="Describe the fault requiring external repair…" required></textarea>
                            </div>
                        </div>
                        <div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Sent Date</label>
                                    <input type="date" name="sent_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Expected Return</label>
                                    <input type="date" name="est_return" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Estimated Cost (EGP)</label>
                                <input type="number" name="cost" class="form-control" placeholder="0.00" min="0" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Shop contact, special instructions…"></textarea>
                            </div>
                            <div style="display:flex;gap:10px;margin-top:4px;">
                                <button type="submit" name="add_repair" class="btn btn-red"><i class="fa fa-paper-plane"></i> Submit</button>
                                <button type="button" class="btn btn-ghost" onclick="toggleForm()"><i class="fa fa-xmark"></i> Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- FILTER TABS -->
        <div class="filter-tabs">
            <div class="ftab active" onclick="filterRepairs('all', this)">All (<?= $stat_total ?>)</div>
            <div class="ftab" onclick="filterRepairs('sent', this)">Sent (<?= $stat_sent ?>)</div>
            <div class="ftab" onclick="filterRepairs('in_progress', this)">In Progress (<?= $stat_progress ?>)</div>
            <div class="ftab" onclick="filterRepairs('returned', this)">Returned (<?= $stat_returned ?>)</div>
        </div>

        <!-- REPAIR CARDS -->
        <?php if (empty($repairs)): ?>
            <div class="card" style="text-align:center;padding:60px;">
                <i class="fa fa-truck-ramp-box" style="font-size:3rem;opacity:.15;"></i>
                <p style="margin-top:16px;color:var(--text-muted);">No external repairs recorded yet.</p>
                <button class="btn btn-red" style="margin-top:16px;" onclick="toggleForm()"><i class="fa fa-plus"></i> Add First Repair</button>
            </div>
        <?php else: ?>
            <div id="repairs-list">
            <?php foreach ($repairs as $rep):
                $st = $rep['status'] ?? 'sent';
                $sc = $status_colors[$st] ?? $status_colors['sent'];
            ?>
            <div class="repair-card" data-status="<?= htmlspecialchars($st) ?>">
                <div class="rc-header">
                    <div>
                        <div class="rc-tool"><?= htmlspecialchars($rep['tool_name'] ?? 'Tool #'.($rep['tool_id']??'?')) ?></div>
                        <div class="rc-shop">
                            <i class="fa fa-store"></i>
                            <?= htmlspecialchars($rep['shop_name'] ?? 'External Shop') ?>
                        </div>
                    </div>
                    <span class="badge" style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>;">
                        <?php
                        $icons = ['sent'=>'fa-truck','in_progress'=>'fa-gear fa-spin','returned'=>'fa-circle-check','cancelled'=>'fa-ban'];
                        $ic = $icons[$st] ?? 'fa-circle';
                        ?>
                        <i class="fa <?= $ic ?>"></i> <?= $sc['label'] ?>
                    </span>
                </div>

                <div class="rc-meta">
                    <?php if ($rep['issue'] ?? null): ?>
                    <span><i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars(substr($rep['issue'],0,80)) ?><?= strlen($rep['issue']??'')>80?'…':'' ?></span>
                    <?php endif; ?>
                    <?php if ($rep['sent_date'] ?? null): ?>
                    <span><i class="fa fa-calendar-day"></i> Sent: <?= date('M d, Y', strtotime($rep['sent_date'])) ?></span>
                    <?php endif; ?>
                    <?php if ($rep['est_return_date'] ?? null): ?>
                    <span><i class="fa fa-calendar-check"></i> Expected: <?= date('M d, Y', strtotime($rep['est_return_date'])) ?></span>
                    <?php endif; ?>
                    <?php if ($rep['cost'] ?? null): ?>
                    <span><i class="fa fa-coins"></i> <?= number_format($rep['cost'],0) ?> EGP</span>
                    <?php endif; ?>
                </div>

                <?php if ($rep['notes'] ?? null): ?>
                <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:10px;background:var(--surface);border-radius:6px;padding:8px 10px;">
                    <i class="fa fa-note-sticky" style="margin-right:5px;"></i><?= htmlspecialchars($rep['notes']) ?>
                </div>
                <?php endif; ?>

                <!-- STATUS UPDATE FORM -->
                <?php if (isset($rep['id'])): ?>
                <form method="POST" class="rc-actions">
                    <input type="hidden" name="repair_id" value="<?= $rep['id'] ?>">
                    <input type="hidden" name="tool_id_upd" value="<?= $rep['tool_id'] ?>">
                    <select name="status" class="form-control" style="width:auto;padding:5px 10px;font-size:.78rem;">
                        <?php foreach ($status_colors as $sv => $sv_data): ?>
                        <option value="<?= $sv ?>" <?= $st===$sv?'selected':'' ?>><?= $sv_data['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-ghost btn-sm">
                        <i class="fa fa-rotate"></i> Update
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
function toggleForm() {
    const w = document.getElementById('add-form-wrap');
    w.style.display = w.style.display === 'none' ? 'block' : 'none';
    if (w.style.display === 'block') w.scrollIntoView({behavior:'smooth'});
}

function filterRepairs(status, el) {
    document.querySelectorAll('.ftab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.repair-card').forEach(card => {
        card.style.display = (status === 'all' || card.dataset.status === status) ? 'block' : 'none';
    });
}

document.querySelector('.hamburger')?.addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('open');
});
</script>
</body>
</html>