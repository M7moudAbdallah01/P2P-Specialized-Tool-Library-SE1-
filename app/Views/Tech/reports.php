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

$unread_q    = $conn->query("SELECT COUNT(*) AS cnt FROM messages WHERE receiver_id=$uid AND is_read=0");
$unread_msgs = $unread_q ? $unread_q->fetch_assoc()['cnt'] : 0;


$pending_q     = $conn->query("SELECT COUNT(*) AS c FROM damage_declarations WHERE technician_id=$uid AND status='pending'");
$pending_count = $pending_q ? $pending_q->fetch_assoc()['c'] : 0;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_repair'])) {
    $rid            = intval($_POST['repair_id']);
    $new_status     = $conn->real_escape_string($_POST['new_status']);
    $tech_note      = $conn->real_escape_string($_POST['tech_note'] ?? '');
    $diagnosis_type = $conn->real_escape_string($_POST['diagnosis_type'] ?? '');

    $conn->query("
        UPDATE damage_declarations
        SET status='$new_status',
            tech_note='$tech_note',
            diagnosis_type='$diagnosis_type',
            updated_at=NOW()
        WHERE id=$rid AND technician_id=$uid
    ");

    header("Location: reports.php?msg=updated");
    exit();
}


$status_filter = $_GET['status'] ?? '';
$search        = trim($_GET['search'] ?? '');

$where = "WHERE dd.technician_id=$uid";
if ($status_filter && in_array($status_filter, ['pending','reviewing','resolved'])) {
    $where .= " AND dd.status='$status_filter'";
}
if ($search) {
    $search_escaped = $conn->real_escape_string($search);
    $where .= " AND (dd.tool_name LIKE '%$search_escaped%' OR dd.reference_no LIKE '%$search_escaped%')";
}


$repairs_q = $conn->query("
    SELECT dd.*,
           u.name  AS reporter_name,
           u.email AS reporter_email
    FROM damage_declarations dd
    LEFT JOIN users u ON dd.reporter_id = u.user_id
    $where
    ORDER BY dd.submitted_at DESC
");


$cnt = [];
foreach (['pending','reviewing','resolved'] as $status_key) {
    $count_q        = $conn->query("SELECT COUNT(*) AS c FROM damage_declarations WHERE technician_id=$uid AND status='$status_key'");
    $cnt[$status_key] = $count_q ? $count_q->fetch_assoc()['c'] : 0;
}
$cnt['completed'] = $cnt['resolved'] ?? 0;
$cnt['total']     = $cnt['pending'] + $cnt['reviewing'] + $cnt['resolved'];


$diagnosis_options = [
    ''                    => '— Select Diagnosis —',
    'electrical_fault'    => 'Electrical Fault',
    'mechanical_damage'   => 'Mechanical / Physical Damage',
    'software_issue'      => 'Software / Firmware Issue',
    'wear_and_tear'       => 'Wear & Tear',
    'calibration_needed'  => 'Calibration Needed',
    'missing_parts'       => 'Missing / Broken Parts',
    'corrosion'           => 'Corrosion / Rust',
    'overheating'         => 'Overheating',
    'operator_error'      => 'Operator Error / Misuse',
    'other'               => 'Other',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Repair Requests</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/tech.css">
<style>
.diag-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(99,102,241,.15);
    color: #6366f1;
    border: 1px solid rgba(99,102,241,.3);
    border-radius: 6px;
    padding: 3px 10px;
    font-size: .75rem;
    font-weight: 600;
    margin-top: 4px;
}
.note-sent-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(45,190,108,.12);
    color: #2dbe6c;
    border: 1px solid rgba(45,190,108,.25);
    border-radius: 5px;
    padding: 2px 8px;
    font-size: .7rem;
    margin-left: 8px;
}
.rc-foot form .form-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.rc-foot form .form-group label {
    font-size: .72rem;
    color: #888;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.rc-foot select,
.rc-foot textarea {
    background: #1a1a1a;
    border: 1px solid #2e2e2e;
    color: #e0e0e0;
    border-radius: 7px;
    padding: 7px 10px;
    font-family: 'Poppins', sans-serif;
    font-size: .82rem;
    transition: border-color .2s;
}
.rc-foot select:focus,
.rc-foot textarea:focus {
    outline: none;
    border-color: #6366f1;
}
.rc-foot textarea {
    resize: vertical;
    min-height: 70px;
    width: 100%;
}
.empty-state-big {
    text-align: center;
    padding: 60px 20px;
    color: #555;
}
.empty-state-big i {
    font-size: 3.5rem;
    margin-bottom: 18px;
    display: block;
    color: #333;
}
.empty-state-big h3 {
    font-size: 1.1rem;
    color: #888;
    font-weight: 400;
    margin: 0;
}
.foot-row {
    display: flex;
    gap: 14px;
    align-items: flex-end;
    flex-wrap: wrap;
    width: 100%;
}
.foot-row .fg-status  { min-width: 160px; }
.foot-row .fg-diag    { min-width: 200px; }
.foot-row .fg-note    { flex: 1; min-width: 200px; }
.foot-row .fg-submit  { flex-shrink: 0; }
.diag-row { margin-top: 12px; }
.photo-thumb {
    width: 64px;
    height: 64px;
    object-fit: cover;
    border-radius: 7px;
    cursor: pointer;
    border: 1px solid #2a2a2a;
    transition: transform .2s;
}
.photo-thumb:hover { transform: scale(1.05); }
</style>
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><img src="../../assets/images/logo.png" alt="Tool Hub Logo"></div>
        <div class="brand-text">TOOL HUB</div>
    </div>
    <div class="role-badge" style="background:#0ea5e9;color:#fff;">TECH</div>
    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
        <a href="repair_estimator.php" class="nav-link"><i class="fa fa-calculator"></i> Repair Estimator</a>
        <a href="external_repair.php" class="nav-link"><i class="fa fa-arrow-up-right-from-square"></i> External Repairs</a>
        <a href="reports.php" class="nav-link active">
            <i class="fa fa-screwdriver-wrench"></i> Repair Requests
            <?php if ($pending_count > 0): ?>
                <span class="nav-count"><?= $pending_count ?></span>
            <?php endif; ?>
        </a>
        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
            <?php if ($unread_msgs > 0): ?>
                <span class="nav-count"><?= $unread_msgs ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>

<!-- ═══ RIGHT ═══ -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-title">Repair Requests</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#0ea5e9;color:#fff;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Tech</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn"><i class="fa fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content">

        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="fa fa-circle-check"></i>
            <?= $_GET['msg'] === 'updated' ? 'Repair request updated — your notes have been sent to admin.' : 'Action completed.' ?>
        </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="stat-mini">
            <div class="stat-mini-card">
                <div class="num"><?= $cnt['total'] ?></div>
                <div class="lbl">Total Assigned</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#f59e0b;"><?= $cnt['pending'] ?></div>
                <div class="lbl">Pending</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#6366f1;"><?= $cnt['reviewing'] ?></div>
                <div class="lbl">Reviewing</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#2dbe6c;"><?= $cnt['completed'] ?></div>
                <div class="lbl">Completed</div>
            </div>
        </div>

        <!-- FILTERS -->
        <form method="GET" class="filters">
            <input type="text" name="search" placeholder="Search tool name or reference no..."
                   value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="">All Status</option>
                <option value="pending"   <?= $status_filter === 'pending'   ? 'selected' : '' ?>>Pending</option>
                <option value="reviewing" <?= $status_filter === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
                <option value="resolved"  <?= $status_filter === 'resolved'  ? 'selected' : '' ?>>Completed</option>
            </select>
            <button class="btn btn-red"><i class="fa fa-search"></i> Filter</button>
            <a href="reports.php" class="btn btn-ghost">Reset</a>
        </form>

        <!-- ═══ REPAIR LIST ═══ -->
        <?php if (!$repairs_q || $repairs_q->num_rows === 0): ?>
        <div class="empty-state-big">
            <i class="fa fa-screwdriver-wrench"></i>
            <h3>No repair requests assigned to you yet.<br>
                <small style="font-size:.85rem;color:#444;">Once an admin assigns a request, it will appear here.</small>
            </h3>
        </div>

        <?php else: ?>

        <?php while ($rr = $repairs_q->fetch_assoc()):
            $stat_cls  = 'is-'   . str_replace(' ', '_', $rr['status']);
            $spill_cls = 'spill-'. str_replace(' ', '_', $rr['status']);
            $sev_cls   = 'sev-'  . ($rr['severity'] ?? 'low');

            $photos_raw = $rr['photos'] ?? '';
            $photos = [];
            if ($photos_raw && $photos_raw !== 'null') {
                $decoded = json_decode($photos_raw, true);
                if (is_array($decoded)) $photos = $decoded;
            }

            /* Progress steps: pending → reviewing → completed */
            $steps   = ['pending', 'reviewing', 'resolved'];
            $cur_idx = array_search($rr['status'], $steps);
            if ($cur_idx === false) $cur_idx = 0;

            $diag_label = $diagnosis_options[$rr['diagnosis_type'] ?? ''] ?? '';
        ?>
        <div class="repair-card <?= $stat_cls ?>">

            <!-- HEAD -->
            <div class="rc-head">
                <span class="rc-id">#<?= $rr['id'] ?></span>
                <span class="rc-tool">
                    <i class="fa fa-wrench" style="color:#f59e0b;font-size:.8rem;margin-right:5px;"></i>
                    <?= htmlspecialchars($rr['tool_name']) ?>
                </span>
                <span class="sev-badge <?= $sev_cls ?>"><?= ucfirst($rr['severity'] ?? 'low') ?></span>
                <span class="spill <?= $spill_cls ?>">
                    <?= strtoupper(str_replace('_', ' ', $rr['status'])) ?>
                </span>
                <?php if ($rr['tech_note'] || $rr['diagnosis_type']): ?>
                    <span class="note-sent-tag"><i class="fa fa-paper-plane"></i> Report Sent to Admin</span>
                <?php endif; ?>
                <span class="rc-date">
                    <i class="fa fa-clock" style="font-size:.7rem;"></i>
                    <?= date('M d, Y — H:i', strtotime($rr['submitted_at'])) ?>
                </span>
            </div>

            <!-- PROGRESS TRACKER -->
            <div style="padding:12px 20px 0;border-bottom:1px solid var(--border,#2a2a2a);">
                <div class="progress-track">
                    <?php foreach ($steps as $i => $step): ?>
                        <?php $is_done = $i < $cur_idx; $is_active = $i === $cur_idx; ?>
                        <div class="pt-step">
                            <div class="pt-dot <?= $is_done ? 'done' : ($is_active ? 'active' : '') ?>">
                                <?php if ($is_done): ?>
                                    <i class="fa fa-check" style="font-size:.65rem;"></i>
                                <?php elseif ($is_active): ?>
                                    <i class="fa fa-circle" style="font-size:.5rem;"></i>
                                <?php else: ?>
                                    <i class="fa fa-circle" style="font-size:.5rem;opacity:.3;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="pt-label <?= $is_done ? 'done' : ($is_active ? 'active' : '') ?>">
                                <?= $step === 'resolved' ? 'Completed' : ucwords(str_replace('_', ' ', $step)) ?>
                            </div>
                        </div>
                        <?php if ($i < count($steps) - 1): ?>
                            <div class="pt-line <?= $i < $cur_idx ? 'done' : '' ?>"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- BODY -->
            <div class="rc-body">

                <!-- Col 1: Tool & Report Info -->
                <div class="rc-col">
                    <div class="rc-label">Tool Name</div>
                    <div class="rc-val" style="font-weight:600;font-size:.95rem;">
                        <?= htmlspecialchars($rr['tool_name']) ?>
                    </div>

                    <div class="rc-label" style="margin-top:12px;">Reference No.</div>
                    <div class="rc-val"><?= htmlspecialchars($rr['reference_no'] ?? '—') ?></div>

                    <div class="rc-label" style="margin-top:12px;">Reservation ID</div>
                    <div class="rc-val"><?= htmlspecialchars($rr['reservation_id'] ?? '—') ?></div>

                    <div class="rc-label" style="margin-top:12px;">Damage Date</div>
                    <div class="rc-val">
                        <?= $rr['damage_date'] ? date('d M Y', strtotime($rr['damage_date'])) : '—' ?>
                    </div>

                    <div class="rc-label" style="margin-top:12px;">Location</div>
                    <div class="rc-val"><?= htmlspecialchars($rr['location'] ?? '—') ?></div>

                    <div class="rc-label" style="margin-top:12px;">Reported By</div>
                    <div class="rc-val"><?= htmlspecialchars($rr['reporter_name'] ?? 'Unknown') ?></div>
                    <div class="rc-sub"><?= htmlspecialchars($rr['reporter_email'] ?? '') ?></div>
                </div>

                <!-- Col 2: Damage + Tech Diagnosis -->
                <div class="rc-col">
                    <div class="rc-label">Damage Type</div>
                    <div class="rc-val">
                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $rr['damage_type'] ?? '—'))) ?>
                    </div>

                    <div class="rc-label" style="margin-top:12px;">Description</div>
                    <div class="desc-text"><?= nl2br(htmlspecialchars($rr['description'] ?? '—')) ?></div>

                    <?php if ($rr['document_path']): ?>
                    <div class="rc-label" style="margin-top:12px;">Attached Document</div>
                    <a href="<?= htmlspecialchars($rr['document_path']) ?>" target="_blank"
                       style="color:#6366f1;font-size:.8rem;">
                        <i class="fa fa-file-pdf"></i> View Document
                    </a>
                    <?php endif; ?>

                    <?php if (!empty($rr['diagnosis_type'])): ?>
                    <div class="rc-label diag-row">Tech Diagnosis</div>
                    <div>
                        <span class="diag-badge">
                            <i class="fa fa-stethoscope" style="font-size:.7rem;"></i>
                            <?= htmlspecialchars($diag_label ?: $rr['diagnosis_type']) ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if ($rr['tech_note']): ?>
                    <div class="rc-label" style="margin-top:12px;">
                        Your Report to Admin
                        <span class="note-sent-tag"><i class="fa fa-paper-plane"></i> Sent</span>
                    </div>
                    <div class="desc-text" style="color:#a5b4fc;border-left:2px solid #6366f1;padding-left:8px;">
                        <?= nl2br(htmlspecialchars($rr['tech_note'])) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Col 3: Photos + Admin Note -->
                <div class="rc-col">
                    <div class="rc-label">Damage Photos</div>
                    <div class="photo-row">
                        <?php if (!empty($photos)): ?>
                            <?php foreach (array_slice($photos, 0, 6) as $ph): ?>
                                <img src="<?= htmlspecialchars($ph) ?>"
                                     class="photo-thumb"
                                     onclick="window.open(this.src,'_blank')"
                                     alt="damage photo"
                                     onerror="this.style.display='none'">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-photo"><i class="fa fa-image"></i> No photos</div>
                        <?php endif; ?>
                    </div>

                    <?php if ($rr['admin_note']): ?>
                    <div class="rc-label" style="margin-top:14px;">Admin Instructions</div>
                    <div class="admin-note-box">
                        <strong><i class="fa fa-circle-info"></i> From Admin:</strong><br>
                        <?= nl2br(htmlspecialchars($rr['admin_note'])) ?>
                    </div>
                    <?php endif; ?>

                    <div style="margin-top:14px;">
                        <div class="rc-label">Submitted</div>
                        <div class="rc-sub"><?= date('d M Y, H:i', strtotime($rr['submitted_at'])) ?></div>
                        <?php if (!empty($rr['updated_at']) && $rr['updated_at'] !== $rr['submitted_at']): ?>
                        <div class="rc-label" style="margin-top:6px;">Last Updated</div>
                        <div class="rc-sub"><?= date('d M Y, H:i', strtotime($rr['updated_at'])) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.rc-body -->

            <!-- ACTION FOOTER -->
            <?php if (!in_array($rr['status'], ['completed','resolved'])): ?>
            <div class="rc-foot">
                <form method="POST">
                    <input type="hidden" name="update_repair" value="1">
                    <input type="hidden" name="repair_id"     value="<?= $rr['id'] ?>">
                    <div class="foot-row">

                        <!-- Status -->
                        <div class="form-group fg-status">
                            <label>Update Status</label>
                            <select name="new_status">
                                <option value="reviewing" <?= $rr['status'] === 'reviewing' ? 'selected' : '' ?>>
                                    Reviewing
                                </option>
                                <option value="resolved">Completed</option>
                            </select>
                        </div>

                        <!-- Diagnosis -->
                        <div class="form-group fg-diag">
                            <label>Diagnosis / Root Cause <span style="color:#f87171;">*</span></label>
                            <select name="diagnosis_type">
                                <?php foreach ($diagnosis_options as $val => $lbl): ?>
                                <option value="<?= $val ?>"
                                    <?= ($rr['diagnosis_type'] ?? '') === $val ? 'selected' : '' ?>>
                                    <?= $lbl ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Note -->
                        <div class="form-group fg-note">
                            <label>
                                Technician Report / Notes
                                <span style="color:#6366f1;font-size:.65rem;font-weight:400;margin-left:4px;">
                                    (visible to admin)
                                </span>
                            </label>
                            <textarea name="tech_note"
                                placeholder="Describe the issue found, parts needed, estimated repair time, or any other details for admin..."
                                ><?= htmlspecialchars($rr['tech_note'] ?? '') ?></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="form-group fg-submit">
                            <label style="visibility:hidden;">Submit</label>
                            <button type="submit" class="btn-update">
                                <i class="fa fa-paper-plane"></i> Send Report
                            </button>
                        </div>

                    </div>
                </form>
            </div>

            <?php else: ?>
            <div class="rc-foot" style="padding:12px 20px;color:#2dbe6c;font-size:.82rem;">
                <i class="fa fa-circle-check"></i> This repair has been completed.
                <?php if (!empty($rr['updated_at'])): ?>
                    <span style="color:#555;margin-left:8px;font-size:.75rem;">
                        — <?= date('d M Y, H:i', strtotime($rr['updated_at'])) ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div><!-- /.repair-card -->
        <?php endwhile; ?>
        <?php endif; ?>

    </div><!-- /.main-content -->
</div><!-- /.layout-right -->

</body>
</html>