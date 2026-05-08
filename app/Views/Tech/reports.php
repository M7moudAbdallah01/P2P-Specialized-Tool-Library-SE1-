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

$r = $conn->query("SELECT COUNT(*) AS cnt FROM messages WHERE receiver_id=$uid AND is_read=0");
$unread_msgs = $r ? $r->fetch_assoc()['cnt'] : 0;

/* العداد بتاع الـ sidebar — pending بتاع التيكنيشن ده بس */
$r = $conn->query("SELECT COUNT(*) AS c FROM repair_requests WHERE technician_id=$uid AND status='pending'");
$pending_count = $r ? $r->fetch_assoc()['c'] : 0;

/* =========================================================
   UPDATE REPAIR
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_repair'])) {
    $rid        = intval($_POST['repair_id']);
    $new_status = $conn->real_escape_string($_POST['new_status']);
    $tech_note  = $conn->real_escape_string($_POST['tech_note'] ?? '');

    $result = $conn->query("
        UPDATE repair_requests
        SET status='$new_status', tech_note='$tech_note', updated_at=NOW()
        WHERE id=$rid AND technician_id=$uid
    ");

    header("Location: reports.php?msg=updated");
    exit();
}

/* =========================================================
   FILTERS
========================================================= */
$status_filter = $_GET['status'] ?? '';
$search        = trim($_GET['search'] ?? '');

$where = "WHERE rr.technician_id=$uid";
if ($status_filter && in_array($status_filter, ['pending','reviewing','in_progress','completed'])) {
    $where .= " AND rr.status='$status_filter'";
}
if ($search) {
    $s = $conn->real_escape_string($search);
    $where .= " AND (rr.tool_name LIKE '%$s%' OR rr.reference_no LIKE '%$s%')";
}

/* =========================================================
   FETCH REPAIRS
========================================================= */
$repairs_q = $conn->query("
    SELECT rr.*,
           u.name  AS reporter_name,
           u.email AS reporter_email
    FROM repair_requests rr
    LEFT JOIN users u ON rr.reporter_id = u.user_id
    $where
    ORDER BY rr.submitted_at DESC
");

/* ── counts بتاع التيكنيشن ده بس ── */
$cnt = [];
foreach (['pending','in_progress','completed'] as $s) {
    $rq = $conn->query("SELECT COUNT(*) AS c FROM repair_requests WHERE technician_id=$uid AND status='$s'");
    $cnt[$s] = $rq ? $rq->fetch_assoc()['c'] : 0;
}
$cnt['total'] = array_sum($cnt);
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
            <?= $_GET['msg'] === 'updated' ? 'Repair request updated successfully.' : 'Action completed.' ?>
        </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="stat-mini">
            <div class="stat-mini-card">
                <div class="num"><?= $cnt['total'] ?></div>
                <div class="lbl">Total</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#f59e0b;"><?= $cnt['pending'] ?></div>
                <div class="lbl">Pending</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#6366f1;"><?= $cnt['in_progress'] ?></div>
                <div class="lbl">In Progress</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#2dbe6c;"><?= $cnt['completed'] ?></div>
                <div class="lbl">Completed</div>
            </div>
        </div>

        <!-- FILTERS -->
        <form method="GET" class="filters">
            <input type="text" name="search" placeholder="Search tool or reference..."
                   value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="">All Status</option>
                <option value="pending"     <?= $status_filter === 'pending'     ? 'selected' : '' ?>>Pending</option>
                <option value="in_progress" <?= $status_filter === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="completed"   <?= $status_filter === 'completed'   ? 'selected' : '' ?>>Completed</option>
            </select>
            <button class="btn btn-red"><i class="fa fa-search"></i> Filter</button>
            <a href="reports.php" class="btn btn-ghost">Reset</a>
        </form>

        <!-- REPAIR LIST -->
        <?php if (!$repairs_q || $repairs_q->num_rows === 0): ?>
        <div class="empty-state">
            <i class="fa fa-screwdriver-wrench"></i>
            No repair requests assigned to you yet.
        </div>
        <?php else: ?>

        <?php while ($rr = $repairs_q->fetch_assoc()):
            $stat_cls  = 'is-'   . str_replace(' ', '_', $rr['status']);
            $spill_cls = 'spill-'. str_replace(' ', '_', $rr['status']);
            $sev_cls   = 'sev-'  . ($rr['severity'] ?? 'low');
            $photos    = json_decode($rr['photos'] ?? '[]', true);

            $steps   = ['pending', 'in_progress', 'completed'];
            $cur_idx = array_search($rr['status'], $steps);
            if ($cur_idx === false) $cur_idx = 0; /* reviewing يتعامل معاه كـ pending */
        ?>
        <div class="repair-card <?= $stat_cls ?>">

            <!-- HEAD -->
            <div class="rc-head">
                <span class="rc-id">#<?= $rr['id'] ?></span>
                <span class="rc-tool">
                    <i class="fa fa-wrench" style="color:#f59e0b;font-size:.8rem;margin-right:5px;"></i>
                    <?= htmlspecialchars($rr['tool_name']) ?>
                </span>
                <span class="sev-badge <?= $sev_cls ?>"><?= ucfirst($rr['severity']) ?></span>
                <span class="spill <?= $spill_cls ?>">
                    <?= strtoupper(str_replace('_', ' ', $rr['status'])) ?>
                </span>
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
                                <?= ucwords(str_replace('_', ' ', $step)) ?>
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

                <!-- Col 1: Report Info -->
                <div class="rc-col">
                    <div class="rc-label">Reference</div>
                    <div class="rc-val"><?= htmlspecialchars($rr['reference_no'] ?? '—') ?></div>

                    <div class="rc-label" style="margin-top:12px;">Reservation</div>
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

                <!-- Col 2: Damage Details -->
                <div class="rc-col">
                    <div class="rc-label">Damage Type</div>
                    <div class="rc-val">
                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $rr['damage_type'] ?? '—'))) ?>
                    </div>

                    <div class="rc-label" style="margin-top:12px;">Description</div>
                    <div class="desc-text"><?= nl2br(htmlspecialchars($rr['description'] ?? '—')) ?></div>

                    <?php if ($rr['document_path']): ?>
                    <div class="rc-label" style="margin-top:12px;">Document</div>
                    <a href="<?= htmlspecialchars($rr['document_path']) ?>" target="_blank"
                       style="color:#6366f1;font-size:.8rem;">
                        <i class="fa fa-file-pdf"></i> View Document
                    </a>
                    <?php endif; ?>

                    <?php if ($rr['tech_note']): ?>
                    <div class="rc-label" style="margin-top:12px;">Your Last Note</div>
                    <div class="desc-text" style="color:#6366f1;">
                        <?= nl2br(htmlspecialchars($rr['tech_note'])) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Col 3: Photos + Admin Note -->
                <div class="rc-col">
                    <div class="rc-label">Damage Photos</div>
                    <div class="photo-row">
                        <?php if (!empty($photos)): ?>
                            <?php foreach (array_slice($photos, 0, 4) as $ph): ?>
                                <img src="<?= htmlspecialchars($ph) ?>" class="photo-thumb"
                                     onclick="window.open(this.src,'_blank')" alt="damage photo">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-photo"><i class="fa fa-image"></i></div>
                        <?php endif; ?>
                    </div>

                    <?php if ($rr['admin_note']): ?>
                    <div class="rc-label" style="margin-top:14px;">Admin Instructions</div>
                    <div class="admin-note-box">
                        <strong><i class="fa fa-circle-info"></i> From Admin:</strong><br>
                        <?= nl2br(htmlspecialchars($rr['admin_note'])) ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div><!-- /.rc-body -->

            <!-- ACTION FOOTER -->
            <?php if ($rr['status'] !== 'completed'): ?>
            <div class="rc-foot">
                <form method="POST" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;width:100%;">
                    <input type="hidden" name="update_repair" value="1">
                    <input type="hidden" name="repair_id"     value="<?= $rr['id'] ?>">
                    <div>
                        <label>Update Status</label>
                        <select name="new_status">
                            <option value="in_progress" <?= $rr['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed"   <?= $rr['status'] === 'completed'   ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Technician Note</label>
                        <textarea name="tech_note"
                            placeholder="Add notes about the repair, parts needed, estimated time..."
                            ><?= htmlspecialchars($rr['tech_note'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn-update">
                        <i class="fa fa-floppy-disk"></i> Update
                    </button>
                </form>
            </div>
            <?php else: ?>
            <div class="rc-foot" style="padding:12px 20px;color:#2dbe6c;font-size:.82rem;">
                <i class="fa fa-circle-check"></i> This repair has been completed.
                <?php if ($rr['updated_at']): ?>
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