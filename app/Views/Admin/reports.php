<?php

session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();

$extra_cols = [
    "technician_id"  => "INT DEFAULT NULL",
    "tech_note"      => "TEXT DEFAULT NULL",
    "diagnosis_type" => "VARCHAR(100) DEFAULT NULL",
    "admin_note"     => "TEXT DEFAULT NULL",
    "updated_at"     => "DATETIME DEFAULT NULL",
];

foreach ($extra_cols as $col => $def) {
    $chk = $conn->query("SHOW COLUMNS FROM damage_declarations LIKE '$col'");
    if ($chk && $chk->num_rows === 0) {
        $conn->query("ALTER TABLE damage_declarations ADD COLUMN $col $def");
    }
}

$chk = $conn->query("SHOW COLUMNS FROM users LIKE 'trust_score'");
if ($chk && $chk->num_rows > 0) {
    $conn->query("ALTER TABLE users MODIFY COLUMN trust_score DECIMAL(5,2) NOT NULL DEFAULT 50.00");
    $conn->query("UPDATE users SET trust_score = 50 WHERE trust_score = 0 AND trust_score IS NOT NULL");
}


/* ═══════════════════════════════════════
   TRUST SCORE HANDLING
═══════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_trust'])) {
    $target_uid   = intval($_POST['target_user_id']);
    $dmg_id       = intval($_POST['damage_id']);
    $blamed_party = $conn->real_escape_string($_POST['blamed_party'] ?? 'reporter');
    $severity     = $conn->real_escape_string($_POST['severity_level'] ?? 'moderate');

    $deltas = [
        'minor'    => ['minus' => -5,  'plus' => 3],
        'moderate' => ['minus' => -10, 'plus' => 5],
        'severe'   => ['minus' => -20, 'plus' => 10],
    ];

    $d = $deltas[$severity] ?? $deltas['moderate'];

    switch ($blamed_party) {
        case 'reporter':
            $conn->query("
                UPDATE users
                SET trust_score = GREATEST(0, LEAST(100, COALESCE(trust_score,50) + {$d['minus']}))
                WHERE user_id = $target_uid
            ");
            break;

        case 'owner':
            $conn->query("
                UPDATE users
                SET trust_score = GREATEST(0, LEAST(100, COALESCE(trust_score,50) + {$d['plus']}))
                WHERE user_id = $target_uid
            ");
            break;

        case 'shared':
            $half = intdiv($d['minus'], 2);
            $conn->query("
                UPDATE users
                SET trust_score = GREATEST(0, LEAST(100, COALESCE(trust_score,50) + $half))
                WHERE user_id = $target_uid
            ");
            break;

        case 'accident':
            break;
    }

    $conn->query("
        UPDATE damage_declarations
        SET status='resolved',
            updated_at=NOW()
        WHERE id=$dmg_id
    ");

    header("Location: reports.php?tab=client&msg=trust_updated");
    exit();
}


/* ═══════════════════════════════════════
   SEND TO TECHNICIAN
═══════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_repair'])) {
    $dmg_id   = intval($_POST['damage_id']);
    $tech_id  = intval($_POST['technician_id']);
    $adm_note = $conn->real_escape_string($_POST['repair_note'] ?? '');

    $conn->query("
        UPDATE damage_declarations
        SET technician_id = $tech_id,
            admin_note    = '$adm_note',
            status        = 'reviewing',
            updated_at    = NOW()
        WHERE id = $dmg_id
    ");

    header("Location: reports.php?tab=client&msg=repair_sent");
    exit();
}


/* ═══════════════════════════════════════
   ADMIN UPDATE REPAIR STATUS
═══════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_update_repair'])) {
    $rid = intval($_POST['repair_id']);

    $allowed_statuses = ['reviewing', 'in_progress', 'completed'];

    $new_status = in_array($_POST['new_status'], $allowed_statuses)
        ? $_POST['new_status']
        : 'reviewing';

    $adm_note = $conn->real_escape_string($_POST['admin_note'] ?? '');

    $conn->query("
        UPDATE damage_declarations
        SET status     = '$new_status',
            admin_note = '$adm_note',
            updated_at = NOW()
        WHERE id = $rid
    ");

    header("Location: reports.php?tab=tech&msg=repair_updated");
    exit();
}


/* ═══════════════════════════════════════
   ACTIVE TAB
═══════════════════════════════════════ */
$tab = $_GET['tab'] ?? 'client';


/* ═══════════════════════════════════════
   CLIENT FILTERS
═══════════════════════════════════════ */
$c_search_raw        = trim($_GET['c_search'] ?? '');
$c_status_filter_raw = $_GET['c_status'] ?? '';

$c_where = "WHERE dd.technician_id IS NULL";

if ($c_status_filter_raw !== '' && in_array($c_status_filter_raw, ['pending','reviewing','resolved'])) {
    $sf = $conn->real_escape_string($c_status_filter_raw);
    $c_where .= " AND dd.status = '$sf'";
} else {
    $c_where .= " AND dd.status IN ('pending','reviewing','resolved')";
}

if ($c_search_raw !== '') {
    $s = $conn->real_escape_string($c_search_raw);
    $c_where .= " AND (
        dd.tool_name LIKE '%$s%' OR
        dd.reservation_id LIKE '%$s%' OR
        u.name LIKE '%$s%'
    )";
}


/* CLIENT REPORTS QUERY */
$damage_q = $conn->query("
    SELECT dd.*,
           u.name        AS reporter_name,
           u.email       AS reporter_email,
           u.trust_score,
           u.user_id     AS reporter_uid,
           tech.name     AS technician_name
    FROM damage_declarations dd
    LEFT JOIN users u    ON dd.reporter_id   = u.user_id
    LEFT JOIN users tech ON dd.technician_id = tech.user_id
    $c_where
    ORDER BY dd.submitted_at DESC
");


/* ═══════════════════════════════════════
   TECH FILTERS
═══════════════════════════════════════ */
$t_search_raw        = trim($_GET['t_search'] ?? '');
$t_status_filter_raw = $_GET['t_status'] ?? '';

$t_where = "WHERE dd.technician_id IS NOT NULL";

if ($t_status_filter_raw !== '' && in_array($t_status_filter_raw, ['reviewing','in_progress','completed'])) {
    $sf = $conn->real_escape_string($t_status_filter_raw);
    $t_where .= " AND dd.status = '$sf'";
}

if ($t_search_raw !== '') {
    $s = $conn->real_escape_string($t_search_raw);
    $t_where .= " AND (
        dd.tool_name LIKE '%$s%' OR
        dd.reference_no LIKE '%$s%' OR
        tech.name LIKE '%$s%'
    )";
}


/* TECH REPORTS QUERY */
$repairs_q = $conn->query("
    SELECT dd.*,
           u.name    AS reporter_name,
           u.email   AS reporter_email,
           tech.name AS technician_name
    FROM damage_declarations dd
    LEFT JOIN users u    ON dd.reporter_id   = u.user_id
    LEFT JOIN users tech ON dd.technician_id = tech.user_id
    $t_where
    ORDER BY dd.updated_at DESC, dd.submitted_at DESC
");


/* ═══════════════════════════════════════
   FIXED COUNTERS
═══════════════════════════════════════ */

/* CLIENT COUNTERS */
$c_cnt = [];

foreach (['pending', 'reviewing', 'resolved'] as $s) {
    $r = $conn->query("
        SELECT COUNT(*) AS c
        FROM damage_declarations
        WHERE technician_id IS NULL
          AND status = '$s'
    ");

    $c_cnt[$s] = $r ? (int)$r->fetch_assoc()['c'] : 0;
}

$c_cnt['total'] =
    ($c_cnt['pending'] ?? 0) +
    ($c_cnt['reviewing'] ?? 0) +
    ($c_cnt['resolved'] ?? 0);


/* TECH COUNTERS */
$t_cnt = [];

foreach (['reviewing', 'in_progress', 'completed'] as $s) {
    $r = $conn->query("
        SELECT COUNT(*) AS c
        FROM damage_declarations
        WHERE technician_id IS NOT NULL
          AND status = '$s'
    ");

    $t_cnt[$s] = $r ? (int)$r->fetch_assoc()['c'] : 0;
}

$t_cnt['pending'] = 0;

$t_cnt['total'] =
    ($t_cnt['reviewing'] ?? 0) +
    ($t_cnt['in_progress'] ?? 0) +
    ($t_cnt['completed'] ?? 0);


/* ═══════════════════════════════════════
   TECHNICIANS LIST
═══════════════════════════════════════ */
$techs_q = $conn->query("
    SELECT user_id, name
    FROM users
    WHERE role='technical'
    ORDER BY name
");

$technicians = [];

if ($techs_q) {
    while ($t = $techs_q->fetch_assoc()) {
        $technicians[] = $t;
    }
}


/* ═══════════════════════════════════════
   DIAGNOSIS LABELS
═══════════════════════════════════════ */
$diagnosis_labels = [
    'electrical_fault'   => 'Electrical Fault',
    'mechanical_damage'  => 'Mechanical / Physical Damage',
    'software_issue'     => 'Software / Firmware Issue',
    'wear_and_tear'      => 'Wear & Tear',
    'calibration_needed' => 'Calibration Needed',
    'missing_parts'      => 'Missing / Broken Parts',
    'corrosion'          => 'Corrosion / Rust',
    'overheating'        => 'Overheating',
    'operator_error'     => 'Operator Error / Misuse',
    'other'              => 'Other',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Reports</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/reports.css">
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><img src="../../assets/images/logo.png" alt="Tool Hub Logo"></div>
        <div class="brand-text">TOOL HUB</div>
    </div>
    <div class="role-badge role-admin">ADMIN</div>
    <div class="sidebar-nav">
        <a href="dashboard.php"           class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
        <a href="../Tools/tools.php"      class="nav-link"><i class="fa fa-wrench"></i> Tools</a>
        <a href="../Tools/categories.php" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg> Categories
        </a>
        <a href="members.php"      class="nav-link"><i class="fa fa-users"></i> Members</a>
        <a href="reservations.php" class="nav-link"><i class="fa fa-calendar"></i> Reservations</a>
        <a href="chat.php"         class="nav-link"><i class="fa fa-comments"></i> Chat</a>
        <a href="reports.php"      class="nav-link active">
            <i class="fa fa-triangle-exclamation"></i> Reports
        </a>
    </div>
</div>

<!-- ═══ RIGHT ═══ -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <button class="hamburger"><i class="fa fa-bars"></i></button>
        <div class="topbar-title">Reports</div>
        <div class="topbar-right">
            <button class="avatar-btn admin-avatar">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Admin</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn"><i class="fa fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content">

        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="fa fa-circle-check"></i>
            <?= match($_GET['msg']) {
                'trust_updated'  => 'Report resolved — trust score updated accordingly.',
                'repair_sent'    => 'Report assigned to technician successfully.',
                'repair_updated' => 'Repair status updated.',
                default          => 'Action completed.'
            } ?>
        </div>
        <?php endif; ?>

        <!-- TAB BAR -->
        <div class="tab-bar">
            <button class="tab-btn <?= $tab === 'client' ? 'active' : '' ?>"
                    onclick="switchTab('client', event)">
                <i class="fa fa-triangle-exclamation"></i> Client Reports
                <?php if ($c_cnt['pending'] > 0): ?>
                    <span style="background:#ff2e2e;color:#fff;border-radius:10px;
                                 padding:1px 7px;font-size:.68rem;margin-left:4px;">
                        <?= $c_cnt['pending'] ?>
                    </span>
                <?php endif; ?>
            </button>
            <button class="tab-btn <?= $tab === 'tech' ? 'active' : '' ?>"
                    onclick="switchTab('tech', event)">
                <i class="fa fa-screwdriver-wrench"></i> Tech Reports
                <?php if ($t_cnt['pending'] + ($t_cnt['reviewing'] ?? 0) > 0): ?>
                    <span style="background:#ff2e2e;color:#fff;border-radius:10px;
                                 padding:1px 7px;font-size:.68rem;margin-left:4px;">
                        <?= $t_cnt['pending'] + ($t_cnt['reviewing'] ?? 0) ?>
                    </span>
                <?php endif; ?>
            </button>
        </div>

        <!-- ════════════════════════════════
             TAB 1 — CLIENT REPORTS
        ════════════════════════════════ -->
        <div id="tab-client" class="tab-content" style="<?= $tab !== 'client' ? 'display:none' : '' ?>">

            <div class="stat-mini">
                <div class="stat-mini-card"><div class="num"><?= $c_cnt['total'] ?></div><div class="lbl">Total</div></div>
                <div class="stat-mini-card"><div class="num" style="color:#f59e0b;"><?= $c_cnt['pending'] ?></div><div class="lbl">Pending</div></div>
                <div class="stat-mini-card"><div class="num" style="color:#6366f1;"><?= $c_cnt['reviewing'] ?></div><div class="lbl">Reviewing</div></div>
                <div class="stat-mini-card"><div class="num" style="color:#2dbe6c;"><?= $c_cnt['resolved'] ?></div><div class="lbl">Resolved</div></div>
            </div>

            <form method="GET" class="filters">
                <input type="hidden" name="tab" value="client">
                <input type="text" name="c_search" placeholder="Search tool, reservation, user..."
                       value="<?= htmlspecialchars($c_search_raw) ?>">
                <select name="c_status">
                    <option value="">All Status</option>
                    <option value="pending"   <?= $c_status_filter_raw === 'pending'   ? 'selected' : '' ?>>Pending</option>
                    <option value="reviewing" <?= $c_status_filter_raw === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
                    <option value="resolved"  <?= $c_status_filter_raw === 'resolved'  ? 'selected' : '' ?>>Resolved</option>
                </select>
                <button class="btn btn-red"><i class="fa fa-search"></i> Filter</button>
                <a href="reports.php?tab=client" class="btn btn-ghost">Reset</a>
            </form>

            <?php if (!$damage_q || $damage_q->num_rows === 0): ?>
            <div class="empty-state">
                <i class="fa fa-triangle-exclamation"></i>
                <?= $c_status_filter_raw !== '' || $c_search_raw !== '' ? 'No reports match your filter.' : 'No client reports found.' ?>
            </div>
            <?php else: ?>

            <?php while ($dd = $damage_q->fetch_assoc()):
                $sev_cls   = 'sev-'   . ($dd['severity'] ?? 'low');
                $stat_cls  = 'is-'    . ($dd['status']   ?? 'pending');
                $spill_cls = 'spill-' . ($dd['status']   ?? 'pending');
                $photos_raw = $dd['photos'] ?? '';
                $photos = [];
                if ($photos_raw && $photos_raw !== 'null') {
                    $dec = json_decode($photos_raw, true);
                    if (is_array($dec)) $photos = $dec;
                }
                $trust       = floatval($dd['trust_score'] ?? 50);
                $trust_color = $trust >= 70 ? '#2dbe6c' : ($trust >= 40 ? '#f59e0b' : '#ff2e2e');
            ?>
            <div class="damage-card <?= $stat_cls ?>" id="dcard-<?= $dd['id'] ?>">

                <div class="dc-head">
                    <span class="dc-id">#<?= $dd['id'] ?></span>
                    <span class="dc-ref"><?= htmlspecialchars($dd['reference_no'] ?? '—') ?></span>
                    <span class="dc-tool">
                        <i class="fa fa-wrench" style="color:var(--red);font-size:.75rem;"></i>
                        <?= htmlspecialchars($dd['tool_name']) ?>
                    </span>
                    <span class="sev-badge <?= $sev_cls ?>"><?= ucfirst($dd['severity']) ?></span>
                    <span class="spill <?= $spill_cls ?>"><?= strtoupper($dd['status']) ?></span>
                    <?php if (!empty($dd['technician_name'])): ?>
                        <span style="font-size:.72rem;color:#6366f1;margin-left:4px;">
                            <i class="fa fa-screwdriver-wrench"></i> <?= htmlspecialchars($dd['technician_name']) ?>
                        </span>
                    <?php endif; ?>
                    <span class="dc-date">
                        <i class="fa fa-clock" style="font-size:.7rem;"></i>
                        <?= date('M d, Y — H:i', strtotime($dd['submitted_at'])) ?>
                    </span>
                    <button type="button" class="toggle-detail" onclick="toggleDetail('c', <?= $dd['id'] ?>)">
                        <i class="fa fa-chevron-down" id="arr-c-<?= $dd['id'] ?>"></i> Details
                    </button>
                </div>

                <div class="dc-body">

                    <div class="dc-col">
                        <div class="dc-col-label">Reporter</div>
                        <div class="dc-col-val">
                            <i class="fa fa-user" style="color:var(--red);font-size:.75rem;"></i>
                            <?= htmlspecialchars($dd['reporter_name'] ?? 'Unknown') ?>
                        </div>
                        <div class="dc-col-sub"><?= htmlspecialchars($dd['reporter_email'] ?? '') ?></div>

                        <div class="dc-col-label" style="margin-top:12px;">Trust Score</div>
                        <div class="trust-bar-wrap">
                            <div style="font-size:.82rem;font-weight:600;color:<?= $trust_color ?>;">
                                <?= number_format($trust, 1) ?> / 100
                            </div>
                            <div class="trust-bar">
                                <div class="trust-fill" style="width:<?= min(100,$trust) ?>%;background:<?= $trust_color ?>;"></div>
                            </div>
                        </div>

                        <div class="dc-col-label" style="margin-top:12px;">Reservation</div>
                        <div class="dc-col-val"><?= htmlspecialchars($dd['reservation_id'] ?? '—') ?></div>

                        <div class="dc-col-label" style="margin-top:12px;">Location</div>
                        <div class="dc-col-sub"><?= htmlspecialchars($dd['location'] ?? '—') ?></div>
                    </div>

                    <div class="dc-col">
                        <div class="dc-col-label">Damage Type</div>
                        <div class="dc-col-val">
                            <?= htmlspecialchars(ucwords(str_replace('_',' ',$dd['damage_type'] ?? '—'))) ?>
                        </div>

                        <div class="dc-col-label" style="margin-top:12px;">Date of Damage</div>
                        <div class="dc-col-val">
                            <?= $dd['damage_date'] ? date('d M Y', strtotime($dd['damage_date'])) : '—' ?>
                        </div>

                        <?php if (!empty($dd['witness'])): ?>
                        <div class="dc-col-label" style="margin-top:12px;">Witness</div>
                        <div class="dc-col-val"><?= htmlspecialchars($dd['witness']) ?></div>
                        <?php endif; ?>

                        <div class="dc-col-label" style="margin-top:12px;">Description</div>
                        <div class="desc-text"><?= nl2br(htmlspecialchars($dd['description'] ?? '—')) ?></div>

                        <?php if (!empty($dd['tech_note'])): ?>
                        <div class="dc-col-label" style="margin-top:12px;">
                            Tech Report
                            <span class="note-sent-tag"><i class="fa fa-paper-plane"></i> Received</span>
                        </div>
                        <div class="desc-text" style="color:#a5b4fc;border-left:2px solid #6366f1;padding-left:8px;">
                            <?= nl2br(htmlspecialchars($dd['tech_note'])) ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($dd['diagnosis_type'])): ?>
                        <div class="dc-col-label" style="margin-top:8px;">Tech Diagnosis</div>
                        <span class="diag-badge">
                            <i class="fa fa-stethoscope" style="font-size:.7rem;"></i>
                            <?= htmlspecialchars($diagnosis_labels[$dd['diagnosis_type']] ?? $dd['diagnosis_type']) ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <div class="dc-col">
                        <div class="dc-col-label">Evidence Photos</div>
                        <div class="photo-row">
                            <?php if (!empty($photos)): ?>
                                <?php foreach (array_slice($photos, 0, 4) as $ph): ?>
                                    <img src="<?= htmlspecialchars($ph) ?>"
                                         class="photo-thumb"
                                         onclick="window.open(this.src,'_blank')"
                                         alt="damage photo"
                                         onerror="this.style.display='none'">
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-photo"><i class="fa fa-image"></i></div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($dd['document_path'])): ?>
                        <div class="dc-col-label" style="margin-top:14px;">Document</div>
                        <a href="<?= htmlspecialchars($dd['document_path']) ?>" target="_blank"
                           style="color:#6366f1;font-size:.8rem;">
                            <i class="fa fa-file-pdf"></i> View Document
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($dd['admin_note'])): ?>
                        <div class="dc-col-label" style="margin-top:14px;">Your Note to Tech</div>
                        <div style="font-size:.8rem;background:#1a1a2e;border-left:3px solid #6366f1;
                                    padding:8px 12px;border-radius:4px;color:#a5b4fc;margin-top:4px;">
                            <?= nl2br(htmlspecialchars($dd['admin_note'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                </div><!-- /.dc-body -->

                <div class="dc-detail" id="detail-c-<?= $dd['id'] ?>">
                    <div style="font-size:.82rem;color:var(--text-dim);margin-bottom:10px;">
                        <strong style="color:var(--text);">Full Description:</strong><br>
                        <?= nl2br(htmlspecialchars($dd['description'] ?? '—')) ?>
                    </div>
                </div>

                <?php if ($dd['status'] !== 'resolved'): ?>
                <div class="dc-foot">

                    <!-- FORM 1: Resolve + Trust -->
                    <form method="POST" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                        <input type="hidden" name="adjust_trust"   value="1">
                        <input type="hidden" name="damage_id"      value="<?= $dd['id'] ?>">
                        <input type="hidden" name="target_user_id" value="<?= $dd['reporter_uid'] ?>">

                        <div>
                            <label>Who is Responsible?</label>
                            <select name="blamed_party"
                                    onchange="previewTrust(this, <?= $dd['id'] ?>)">
                                <option value="reporter">Reporter (client fault)</option>
                                <option value="owner">Tool Owner (owner fault)</option>
                                <option value="shared">Shared fault</option>
                                <option value="accident">Accident — no fault</option>
                            </select>
                        </div>

                        <div>
                            <label>Severity</label>
                            <select name="severity_level"
                                    onchange="previewTrust(document.querySelector('[name=blamed_party]'), <?= $dd['id'] ?>)">
                                <option value="minor">Minor (-5 / +3)</option>
                                <option value="moderate" selected>Moderate (-10 / +5)</option>
                                <option value="severe">Severe (-20 / +10)</option>
                            </select>
                        </div>

                        <div>
                            <!-- Live preview -->
                            <div class="trust-impact" id="impact-<?= $dd['id'] ?>"></div>
                            <button type="submit" class="btn btn-orange" style="margin-top:4px;">
                                <i class="fa fa-user-shield"></i> Apply &amp; Resolve
                            </button>
                        </div>
                    </form>

                    <!-- FORM 2: Assign Technician -->
                    <?php if ($dd['status'] !== 'reviewing'): ?>
                    <form method="POST" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;
                                               border-left:1px solid #2a2a2a;padding-left:14px;">
                        <input type="hidden" name="send_repair" value="1">
                        <input type="hidden" name="damage_id"   value="<?= $dd['id'] ?>">
                        <div>
                            <label>Assign Technician</label>
                            <select name="technician_id">
                                <?php if (empty($technicians)): ?>
                                    <option value="0">No technicians found</option>
                                <?php else: ?>
                                    <?php foreach ($technicians as $tech): ?>
                                        <option value="<?= $tech['user_id'] ?>"
                                            <?= ($dd['technician_id'] ?? 0) == $tech['user_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tech['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label>Note for Technician</label>
                            <textarea name="repair_note"
                                      placeholder="Instructions, priority, special notes..."><?= htmlspecialchars($dd['admin_note'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-purple" <?= empty($technicians) ? 'disabled' : '' ?>>
                            <i class="fa fa-screwdriver-wrench"></i>
                            <?= !empty($dd['technician_id']) ? 'Re-assign' : 'Send for Repair' ?>
                        </button>
                    </form>
                    <?php else: ?>
                    <div style="font-size:.8rem;color:#6366f1;padding-left:14px;">
                        <i class="fa fa-circle-info"></i>
                        Assigned to <strong><?= htmlspecialchars($dd['technician_name'] ?? '—') ?></strong>.
                        <?php if (!empty($dd['tech_note'])): ?>
                            Tech has submitted a report.
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </div>
                <?php else: ?>
                <div class="dc-foot" style="padding:12px 20px;color:#2dbe6c;font-size:.82rem;">
                    <i class="fa fa-circle-check"></i> Resolved.
                    <?php if (!empty($dd['updated_at'])): ?>
                        <span style="color:#555;margin-left:8px;font-size:.75rem;">
                            — <?= date('d M Y, H:i', strtotime($dd['updated_at'])) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div><!-- /.damage-card -->
            <?php endwhile; ?>
            <?php endif; ?>

        </div><!-- #tab-client -->

        <!-- ════════════════════════════════
             TAB 2 — TECH REPORTS
             (damage_declarations with technician_id)
        ════════════════════════════════ -->
        <div id="tab-tech" class="tab-content" style="<?= $tab !== 'tech' ? 'display:none' : '' ?>">

            <div class="stat-mini">
                <div class="stat-mini-card"><div class="num"><?= $t_cnt['total'] ?></div><div class="lbl">Total Assigned</div></div>
                <div class="stat-mini-card"><div class="num" style="color:#f59e0b;"><?= $t_cnt['pending'] + ($t_cnt['reviewing'] ?? 0) ?></div><div class="lbl">Pending / Review</div></div>
                <div class="stat-mini-card"><div class="num" style="color:#2dbe6c;"><?= $t_cnt['completed'] ?></div><div class="lbl">Completed</div></div>
            </div>

            <form method="GET" class="filters">
                <input type="hidden" name="tab" value="tech">
                <input type="text" name="t_search" placeholder="Search tool, reference, technician..."
                       value="<?= htmlspecialchars($t_search_raw) ?>">
                <select name="t_status">
                    <option value="">All Status</option>
                    <option value="reviewing"   <?= $t_status_filter_raw === 'reviewing'   ? 'selected' : '' ?>>Reviewing</option>
                    <option value="in_progress" <?= $t_status_filter_raw === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed"   <?= $t_status_filter_raw === 'completed'   ? 'selected' : '' ?>>Completed</option>
                </select>
                <button class="btn btn-red"><i class="fa fa-search"></i> Filter</button>
                <a href="reports.php?tab=tech" class="btn btn-ghost">Reset</a>
            </form>

            <?php if (!$repairs_q || $repairs_q->num_rows === 0): ?>
            <div class="empty-state">
                <i class="fa fa-screwdriver-wrench"></i>
                <?= $t_status_filter_raw !== '' || $t_search_raw !== '' ? 'No reports match your filter.' : 'No tech repair requests yet — assign a technician from Client Reports.' ?>
            </div>
            <?php else: ?>

            <?php while ($rr = $repairs_q->fetch_assoc()):
                $sev_cls   = 'sev-'   . ($rr['severity'] ?? 'low');
                $stat_cls  = 'is-'    . str_replace(' ','_', $rr['status'] ?? 'pending');
                $spill_cls = 'spill-' . str_replace(' ','_', $rr['status'] ?? 'pending');
                $photos_raw = $rr['photos'] ?? '';
                $photos = [];
                if ($photos_raw && $photos_raw !== 'null') {
                    $dec = json_decode($photos_raw, true);
                    if (is_array($dec)) $photos = $dec;
                }
                $steps   = ['pending','reviewing','in_progress','completed'];
                $cur_idx = array_search($rr['status'], $steps);
                if ($cur_idx === false) $cur_idx = 0;
            ?>
            <div class="damage-card <?= $stat_cls ?>" id="rcard-<?= $rr['id'] ?>">

                <div class="dc-head">
                    <span class="dc-id">#<?= $rr['id'] ?></span>
                    <span class="dc-ref"><?= htmlspecialchars($rr['reference_no'] ?? '—') ?></span>
                    <span class="dc-tool">
                        <i class="fa fa-wrench" style="color:#f59e0b;font-size:.75rem;"></i>
                        <?= htmlspecialchars($rr['tool_name']) ?>
                    </span>
                    <span class="sev-badge <?= $sev_cls ?>"><?= ucfirst($rr['severity'] ?? 'low') ?></span>
                    <span class="spill <?= $spill_cls ?>">
                        <?= strtoupper(str_replace('_',' ', $rr['status'])) ?>
                    </span>
                    <?php if (!empty($rr['tech_note']) || !empty($rr['diagnosis_type'])): ?>
                        <span class="note-sent-tag"><i class="fa fa-paper-plane"></i> Tech Report Received</span>
                    <?php endif; ?>
                    <span class="dc-date">
                        <i class="fa fa-clock" style="font-size:.7rem;"></i>
                        <?= date('M d, Y — H:i', strtotime($rr['submitted_at'])) ?>
                    </span>
                    <button type="button" class="toggle-detail" onclick="toggleDetail('t', <?= $rr['id'] ?>)">
                        <i class="fa fa-chevron-down" id="arr-t-<?= $rr['id'] ?>"></i> Details
                    </button>
                </div>

                <!-- PROGRESS TRACKER (4-step including reviewing) -->


                <div class="dc-body">

                    <!-- Col 1 -->
                    <div class="dc-col">
                        <div class="dc-col-label">Reported By</div>
                        <div class="dc-col-val">
                            <i class="fa fa-user" style="color:var(--red);font-size:.75rem;"></i>
                            <?= htmlspecialchars($rr['reporter_name'] ?? 'Unknown') ?>
                        </div>
                        <div class="dc-col-sub"><?= htmlspecialchars($rr['reporter_email'] ?? '') ?></div>

                        <div class="dc-col-label" style="margin-top:12px;">Assigned Technician</div>
                        <div class="dc-col-val" style="color:#0ea5e9;">
                            <i class="fa fa-screwdriver-wrench"></i>
                            <?= htmlspecialchars($rr['technician_name'] ?? '— Not assigned') ?>
                        </div>

                        <div class="dc-col-label" style="margin-top:12px;">Reservation</div>
                        <div class="dc-col-val"><?= htmlspecialchars($rr['reservation_id'] ?? '—') ?></div>

                        <div class="dc-col-label" style="margin-top:12px;">Damage Date</div>
                        <div class="dc-col-val">
                            <?= $rr['damage_date'] ? date('d M Y', strtotime($rr['damage_date'])) : '—' ?>
                        </div>

                        <div class="dc-col-label" style="margin-top:12px;">Location</div>
                        <div class="dc-col-val"><?= htmlspecialchars($rr['location'] ?? '—') ?></div>
                    </div>

                    <!-- Col 2 -->
                    <div class="dc-col">
                        <div class="dc-col-label">Damage Type</div>
                        <div class="dc-col-val">
                            <?= htmlspecialchars(ucwords(str_replace('_',' ',$rr['damage_type'] ?? '—'))) ?>
                        </div>

                        <div class="dc-col-label" style="margin-top:12px;">Description</div>
                        <div class="desc-text"><?= nl2br(htmlspecialchars($rr['description'] ?? '—')) ?></div>

                        <?php if (!empty($rr['diagnosis_type'])): ?>
                        <div class="dc-col-label" style="margin-top:12px;">Tech Diagnosis</div>
                        <span class="diag-badge">
                            <i class="fa fa-stethoscope" style="font-size:.7rem;"></i>
                            <?= htmlspecialchars($diagnosis_labels[$rr['diagnosis_type']] ?? $rr['diagnosis_type']) ?>
                        </span>
                        <?php endif; ?>

                        <?php if (!empty($rr['tech_note'])): ?>
                        <div class="dc-col-label" style="margin-top:12px;">
                            Technician Report
                            <span class="note-sent-tag"><i class="fa fa-paper-plane"></i> Received</span>
                        </div>
                        <div class="desc-text" style="color:#a5b4fc;border-left:2px solid #6366f1;padding-left:8px;">
                            <?= nl2br(htmlspecialchars($rr['tech_note'])) ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($rr['document_path'])): ?>
                        <div class="dc-col-label" style="margin-top:12px;">Document</div>
                        <a href="<?= htmlspecialchars($rr['document_path']) ?>" target="_blank"
                           style="color:#6366f1;font-size:.8rem;">
                            <i class="fa fa-file-pdf"></i> View Document
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- Col 3 -->
                    <div class="dc-col">
                        <div class="dc-col-label">Damage Photos</div>
                        <div class="photo-row">
                            <?php if (!empty($photos)): ?>
                                <?php foreach (array_slice($photos, 0, 4) as $ph): ?>
                                    <img src="<?= htmlspecialchars($ph) ?>"
                                         class="photo-thumb"
                                         onclick="window.open(this.src,'_blank')"
                                         alt="damage photo"
                                         onerror="this.style.display='none'">
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-photo"><i class="fa fa-image"></i></div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($rr['admin_note'])): ?>
                        <div class="dc-col-label" style="margin-top:14px;">Your Note to Tech</div>
                        <div style="font-size:.8rem;background:#1a1a2e;border-left:3px solid #6366f1;
                                    padding:8px 12px;border-radius:4px;color:#a5b4fc;margin-top:4px;">
                            <?= nl2br(htmlspecialchars($rr['admin_note'])) ?>
                        </div>
                        <?php endif; ?>

                        <div style="margin-top:14px;">
                            <div class="dc-col-label">Submitted</div>
                            <div class="dc-col-sub"><?= date('d M Y, H:i', strtotime($rr['submitted_at'])) ?></div>
                            <?php if (!empty($rr['updated_at'])): ?>
                            <div class="dc-col-label" style="margin-top:6px;">Last Updated</div>
                            <div class="dc-col-sub"><?= date('d M Y, H:i', strtotime($rr['updated_at'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div><!-- /.dc-body -->

                <div class="dc-detail" id="detail-t-<?= $rr['id'] ?>">
                    <div style="font-size:.82rem;color:var(--text-dim);margin-bottom:10px;">
                        <strong style="color:var(--text);">Full Description:</strong><br>
                        <?= nl2br(htmlspecialchars($rr['description'] ?? '—')) ?>
                    </div>
                </div>

                <!-- ACTION FOOTER -->
                <?php if ($rr['status'] !== 'completed'): ?>
                <div class="dc-foot">
                    <form method="POST" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;width:100%;">
                        <input type="hidden" name="admin_update_repair" value="1">
                        <input type="hidden" name="repair_id"           value="<?= $rr['id'] ?>">
                        <div>
                            <label>Update Status</label>
                            <select name="new_status">
                                <option value="reviewing"   <?= $rr['status'] === 'reviewing'   ? 'selected' : '' ?>>Reviewing</option>
                                <option value="in_progress" <?= $rr['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="completed"                                                          >Completed</option>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label>Note to Technician</label>
                            <textarea name="admin_note"
                                      placeholder="Send instructions or feedback to technician..."
                                      ><?= htmlspecialchars($rr['admin_note'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-orange">
                            <i class="fa fa-floppy-disk"></i> Update
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="dc-foot" style="padding:12px 20px;color:#2dbe6c;font-size:.82rem;">
                    <i class="fa fa-circle-check"></i> Repair completed.
                    <?php if (!empty($rr['updated_at'])): ?>
                        <span style="color:#555;margin-left:8px;font-size:.75rem;">
                            — <?= date('d M Y, H:i', strtotime($rr['updated_at'])) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div><!-- /.damage-card -->
            <?php endwhile; ?>
            <?php endif; ?>

        </div><!-- #tab-tech -->

    </div><!-- /.main-content -->
</div><!-- /.layout-right -->

<script>
/* ── Tab switch ── */
function switchTab(name, event) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + name).style.display = 'block';
    if (event && event.currentTarget) event.currentTarget.classList.add('active');
    const url = new URL(window.location);
    url.searchParams.set('tab', name);
    window.history.replaceState({}, '', url);
}

/* ── Detail toggle ── */
function toggleDetail(prefix, id) {
    const panel = document.getElementById('detail-' + prefix + '-' + id);
    const arrow = document.getElementById('arr-'    + prefix + '-' + id);
    const open  = panel.classList.toggle('open');
    arrow.style.transform  = open ? 'rotate(180deg)' : '';
    arrow.style.transition = '.2s';
}

/* ── Live trust impact preview ── */
function previewTrust(blameSelect, id) {
    const form      = blameSelect.closest('form');
    const sevSelect = form.querySelector('[name=severity_level]');
    const box       = document.getElementById('impact-' + id);
    if (!box || !sevSelect) return;

    const blame = blameSelect.value;
    const sev   = sevSelect.value;
    const deltas = { minor:{minus:-5,plus:3}, moderate:{minus:-10,plus:5}, severe:{minus:-20,plus:10} };
    const d = deltas[sev] || deltas.moderate;

    let html = '';
    if (blame === 'reporter')
        html = `<span class="trust-minus">Reporter trust: ${d.minus} pts</span>`;
    else if (blame === 'owner')
        html = `<span class="trust-plus">Reporter trust: +${d.plus} pts (cleared)</span>`;
    else if (blame === 'shared')
        html = `<span class="trust-minus">Reporter trust: ${Math.ceil(d.minus/2)} pts (shared)</span>`;
    else
        html = `<span class="trust-zero">No trust change (accident)</span>`;

    box.innerHTML = html;
    box.classList.add('show');
}

/* init previews on load */
document.querySelectorAll('[name=blamed_party]').forEach(sel => {
    const id = sel.closest('form')?.querySelector('[type=hidden][name=damage_id]')?.value;
    if (id) previewTrust(sel, id);
});
</script>
</body>
</html>