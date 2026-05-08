<?php
/* =========================================================
   AUTH — technician only
========================================================= */
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();
$uid  = intval($_SESSION['user_id']);

/* ── unread messages ── */
$r = $conn->query("SELECT COUNT(*) AS cnt FROM messages WHERE receiver_id=$uid AND is_read=0");
$unread_msgs = $r ? $r->fetch_assoc()['cnt'] : 0;

/* ── ensure repair_requests table exists ── */
$conn->query("
    CREATE TABLE IF NOT EXISTS repair_requests (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        damage_id        INT           NOT NULL,
        technician_id    INT           DEFAULT NULL,
        tool_name        VARCHAR(255)  NOT NULL,
        severity         ENUM('low','medium','high') NOT NULL DEFAULT 'low',
        description      TEXT          DEFAULT NULL,
        photos           TEXT          DEFAULT NULL,
        status           ENUM('pending','in_progress','completed') DEFAULT 'pending',
        admin_note       TEXT          DEFAULT NULL,
        tech_note        TEXT          DEFAULT NULL,
        created_at       DATETIME      DEFAULT CURRENT_TIMESTAMP,
        updated_at       DATETIME      ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$conn->query("ALTER TABLE repair_requests ADD COLUMN IF NOT EXISTS tech_note TEXT DEFAULT NULL");

/* =========================================================
   ACTIONS
========================================================= */

/* ── Update status + add tech note ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_repair'])) {
    $rid       = intval($_POST['repair_id']);
    $new_status= $conn->real_escape_string($_POST['new_status']);
    $tech_note = $conn->real_escape_string($_POST['tech_note'] ?? '');

    $conn->query("
        UPDATE repair_requests
        SET status='$new_status', tech_note='$tech_note', updated_at=NOW()
        WHERE id=$rid AND technician_id=$uid
    ");

    // If completed, update damage_declaration too
    if ($new_status === 'completed') {
        $rq = $conn->query("SELECT damage_id FROM repair_requests WHERE id=$rid");
        if ($rq && $rrow = $rq->fetch_assoc()) {
            $dmg_id = intval($rrow['damage_id']);
            $conn->query("UPDATE damage_declarations SET status='resolved' WHERE id=$dmg_id");
        }
    }
    header("Location: repair-requests.php?msg=updated"); exit();
}

/* =========================================================
   FILTERS
========================================================= */
$status_filter = $_GET['status'] ?? '';
$search        = trim($_GET['search'] ?? '');

$where = "WHERE rr.technician_id=$uid";
if ($status_filter && in_array($status_filter,['pending','in_progress','completed'])) {
    $where .= " AND rr.status='$status_filter'";
}
if ($search) {
    $s = $conn->real_escape_string($search);
    $where .= " AND (rr.tool_name LIKE '%$s%' OR dd.reference_no LIKE '%$s%')";
}

/* =========================================================
   FETCH REPAIRS
========================================================= */
$repairs_q = $conn->query("
    SELECT rr.*,
           dd.reference_no, dd.reservation_id, dd.damage_date,
           dd.location, dd.damage_type, dd.document_path,
           u.name AS reporter_name, u.email AS reporter_email
    FROM repair_requests rr
    LEFT JOIN damage_declarations dd ON rr.damage_id = dd.id
    LEFT JOIN users u ON dd.reporter_id = u.user_id
    $where
    ORDER BY rr.created_at DESC
");

/* ── counts ── */
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
<style>
/* ══ STAT MINI ══ */
.stat-mini{display:flex;gap:14px;margin-bottom:22px;flex-wrap:wrap;}
.stat-mini-card{background:var(--card,#1a1a1a);border:1px solid var(--border,#2a2a2a);
    border-radius:10px;padding:14px 22px;min-width:100px;text-align:center;}
.stat-mini-card .num{font-size:1.5rem;font-weight:700;color:var(--text,#fff);}
.stat-mini-card .lbl{font-size:.72rem;color:var(--text-dim,#888);margin-top:2px;}

/* ══ FILTERS ══ */
.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;}
.filters input,.filters select{background:var(--card,#1a1a1a);border:1px solid var(--border,#2a2a2a);
    color:var(--text,#fff);border-radius:8px;padding:8px 14px;
    font-family:'Poppins',sans-serif;font-size:.82rem;}
.filters input:focus,.filters select:focus{outline:none;border-color:var(--red,#ff2e2e);}

/* ══ REPAIR CARD ══ */
.repair-card{background:var(--card,#1a1a1a);border:1px solid var(--border,#2a2a2a);
    border-radius:14px;margin-bottom:16px;overflow:hidden;transition:.2s;}
.repair-card:hover{border-color:#444;}
.repair-card.is-pending    {border-left:4px solid #f59e0b;}
.repair-card.is-in_progress{border-left:4px solid #6366f1;}
.repair-card.is-completed  {border-left:4px solid #2dbe6c;}

/* HEAD */
.rc-head{display:flex;align-items:center;gap:14px;padding:14px 20px;
    border-bottom:1px solid var(--border,#2a2a2a);flex-wrap:wrap;}
.rc-id{font-size:.78rem;color:var(--text-dim,#888);font-weight:600;
    background:#222;padding:3px 10px;border-radius:6px;}
.rc-tool{font-weight:700;font-size:.9rem;color:var(--text,#fff);flex:1;}
.rc-date{font-size:.75rem;color:var(--text-dim,#888);margin-left:auto;}

.spill{padding:3px 12px;border-radius:20px;font-size:.72rem;font-weight:700;text-transform:uppercase;}
.spill-pending    {background:#f59e0b22;color:#f59e0b;}
.spill-in_progress{background:#6366f122;color:#6366f1;}
.spill-completed  {background:#2dbe6c22;color:#2dbe6c;}

.sev-badge{padding:2px 10px;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase;}
.sev-low   {background:#2dbe6c22;color:#2dbe6c;}
.sev-medium{background:#f59e0b22;color:#f59e0b;}
.sev-high  {background:#ff2e2e22;color:#ff2e2e;}

/* BODY */
.rc-body{display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;}
.rc-col{padding:16px 20px;border-right:1px solid var(--border,#2a2a2a);}
.rc-col:last-child{border-right:none;}
.rc-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;
    color:var(--text-dim,#888);margin-bottom:5px;}
.rc-val{font-size:.85rem;color:var(--text,#fff);font-weight:500;}
.rc-sub{font-size:.75rem;color:var(--text-dim,#888);margin-top:3px;}
.desc-text{font-size:.82rem;color:var(--text-dim,#aaa);line-height:1.55;}

/* PHOTOS */
.photo-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;}
.photo-thumb{width:54px;height:54px;border-radius:8px;object-fit:cover;cursor:pointer;
    border:1px solid #333;transition:.2s;}
.photo-thumb:hover{border-color:var(--red,#ff2e2e);transform:scale(1.05);}
.no-photo{width:54px;height:54px;border-radius:8px;background:#222;border:1px dashed #333;
    display:flex;align-items:center;justify-content:center;color:#555;font-size:.8rem;}

/* FOOTER */
.rc-foot{display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;
    padding:14px 20px;border-top:1px solid var(--border,#2a2a2a);background:#161616;}
.rc-foot label{font-size:.75rem;color:var(--text-dim,#888);display:block;margin-bottom:4px;}
.rc-foot select,.rc-foot textarea{background:#1e1e1e;border:1px solid #333;
    color:var(--text,#fff);border-radius:8px;padding:8px 12px;
    font-family:'Poppins',sans-serif;font-size:.8rem;}
.rc-foot select:focus,.rc-foot textarea:focus{outline:none;border-color:var(--red,#ff2e2e);}
.rc-foot textarea{resize:vertical;min-height:60px;flex:1;}

/* ADMIN NOTE BOX */
.admin-note-box{background:#1a1a2e;border:1px solid #6366f133;border-radius:8px;
    padding:10px 14px;font-size:.8rem;color:#aaa;line-height:1.5;}
.admin-note-box strong{color:#6366f1;}

/* BUTTONS */
.btn{padding:8px 18px;border-radius:8px;border:none;cursor:pointer;
    font-family:'Poppins',sans-serif;font-size:.8rem;font-weight:600;}
.btn-red   {background:var(--red,#ff2e2e);color:#fff;}
.btn-ghost {background:transparent;border:1px solid #333;color:var(--text-dim,#888);}
.btn-green {background:#2dbe6c;color:#fff;}
.btn-purple{background:#6366f1;color:#fff;}
.btn-update{background:#6366f1;color:#fff;padding:9px 20px;border-radius:8px;
    border:none;cursor:pointer;font-family:'Poppins',sans-serif;font-size:.8rem;font-weight:600;}

/* EMPTY */
.empty-state{text-align:center;padding:50px;color:var(--text-dim,#888);font-size:.9rem;}
.empty-state i{font-size:2.5rem;display:block;margin-bottom:12px;opacity:.3;}

/* ALERT */
.alert{padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:.83rem;
    display:flex;align-items:center;gap:10px;}
.alert-success{background:#2dbe6c22;color:#2dbe6c;border:1px solid #2dbe6c44;}

/* PROGRESS TRACKER */
.progress-track{display:flex;align-items:center;gap:0;margin:8px 0 4px;}
.pt-step{display:flex;flex-direction:column;align-items:center;gap:3px;flex:1;}
.pt-dot{width:26px;height:26px;border-radius:50%;border:2px solid #333;
    display:flex;align-items:center;justify-content:center;font-size:.7rem;
    color:#555;background:#161616;transition:.2s;}
.pt-dot.done{background:#2dbe6c;border-color:#2dbe6c;color:#fff;}
.pt-dot.active{background:#6366f1;border-color:#6366f1;color:#fff;}
.pt-label{font-size:.65rem;color:#666;text-align:center;}
.pt-label.done{color:#2dbe6c;}
.pt-label.active{color:#6366f1;}
.pt-line{flex:1;height:2px;background:#222;margin:0 -1px;position:relative;top:-12px;}
.pt-line.done{background:#2dbe6c;}

@media(max-width:900px){.rc-body{grid-template-columns:1fr;}}
</style>
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><img src="../../assets/images/logo.png" alt="Tool Hub Logo"></div>
        <div class="brand-text">TOOL HUB</div>
    </div>
    <div class="role-badge" style="background:#f59e0b;color:#000;">TECHNICIAN</div>
    <div class="sidebar-nav">
        <a href="dashboard.php"  class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
        <a href="repair-requests.php" class="nav-link active">
            <i class="fa fa-screwdriver-wrench"></i> Repair Requests
            <?php if ($cnt['pending'] > 0): ?>
                <span class="nav-count"><?= $cnt['pending'] ?></span>
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
            <button class="avatar-btn" style="background:#f59e0b;color:#000;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Technician</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn"><i class="fa fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content">

        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="fa fa-circle-check"></i>
            <?= $_GET['msg']==='updated' ? 'Repair request updated successfully.' : 'Action completed.' ?>
        </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="stat-mini">
            <div class="stat-mini-card"><div class="num"><?= $cnt['total'] ?></div><div class="lbl">Total</div></div>
            <div class="stat-mini-card"><div class="num" style="color:#f59e0b;"><?= $cnt['pending'] ?></div><div class="lbl">Pending</div></div>
            <div class="stat-mini-card"><div class="num" style="color:#6366f1;"><?= $cnt['in_progress'] ?></div><div class="lbl">In Progress</div></div>
            <div class="stat-mini-card"><div class="num" style="color:#2dbe6c;"><?= $cnt['completed'] ?></div><div class="lbl">Completed</div></div>
        </div>

        <!-- FILTERS -->
        <form method="GET" class="filters">
            <input type="text" name="search" placeholder="Search tool or reference..."
                   value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="">All Status</option>
                <option value="pending"     <?= $status_filter==='pending'    ?'selected':'' ?>>Pending</option>
                <option value="in_progress" <?= $status_filter==='in_progress'?'selected':'' ?>>In Progress</option>
                <option value="completed"   <?= $status_filter==='completed'  ?'selected':'' ?>>Completed</option>
            </select>
            <button class="btn btn-red"><i class="fa fa-search"></i> Filter</button>
            <a href="repair-requests.php" class="btn btn-ghost">Reset</a>
        </form>

        <!-- REPAIR LIST -->
        <?php if (!$repairs_q || $repairs_q->num_rows === 0): ?>
        <div class="empty-state">
            <i class="fa fa-screwdriver-wrench"></i>
            No repair requests assigned to you yet.
        </div>
        <?php else: ?>
        <?php while ($rr = $repairs_q->fetch_assoc()):
            $stat_cls  = 'is-' . str_replace(' ','_', $rr['status']);
            $spill_cls = 'spill-' . str_replace(' ','_', $rr['status']);
            $sev_cls   = 'sev-' . ($rr['severity'] ?? 'low');
            $photos    = json_decode($rr['photos'] ?? '[]', true);

            // progress positions
            $steps = ['pending','in_progress','completed'];
            $cur_idx = array_search($rr['status'], $steps);
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
                    <?= strtoupper(str_replace('_',' ',$rr['status'])) ?>
                </span>
                <span class="rc-date">
                    <i class="fa fa-clock" style="font-size:.7rem;"></i>
                    <?= date('M d, Y — H:i', strtotime($rr['created_at'])) ?>
                </span>
            </div>

            <!-- PROGRESS TRACKER -->
            <div style="padding:12px 20px 0;border-bottom:1px solid var(--border,#2a2a2a);">
                <div class="progress-track">
                    <?php foreach ($steps as $i => $step): ?>
                        <?php $is_done   = $i < $cur_idx; $is_active = $i === $cur_idx; ?>
                        <div class="pt-step">
                            <div class="pt-dot <?= $is_done?'done':($is_active?'active':'') ?>">
                                <?php if ($is_done): ?><i class="fa fa-check" style="font-size:.65rem;"></i>
                                <?php elseif ($is_active): ?><i class="fa fa-circle" style="font-size:.5rem;"></i>
                                <?php else: ?><i class="fa fa-circle" style="font-size:.5rem;opacity:.3;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="pt-label <?= $is_done?'done':($is_active?'active':'') ?>">
                                <?= ucwords(str_replace('_',' ',$step)) ?>
                            </div>
                        </div>
                        <?php if ($i < count($steps)-1): ?>
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
                    <div class="rc-val"><?= $rr['damage_date'] ? date('d M Y', strtotime($rr['damage_date'])) : '—' ?></div>

                    <div class="rc-label" style="margin-top:12px;">Location</div>
                    <div class="rc-val"><?= htmlspecialchars($rr['location'] ?? '—') ?></div>

                    <div class="rc-label" style="margin-top:12px;">Reported By</div>
                    <div class="rc-val"><?= htmlspecialchars($rr['reporter_name'] ?? 'Unknown') ?></div>
                    <div class="rc-sub"><?= htmlspecialchars($rr['reporter_email'] ?? '') ?></div>
                </div>

                <!-- Col 2: Damage Details -->
                <div class="rc-col">
                    <div class="rc-label">Damage Type</div>
                    <div class="rc-val"><?= htmlspecialchars(ucwords(str_replace('_',' ',$rr['damage_type'] ?? '—'))) ?></div>

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
                    <div class="desc-text" style="color:#6366f1;"><?= nl2br(htmlspecialchars($rr['tech_note'])) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Col 3: Photos + Admin Note -->
                <div class="rc-col">
                    <div class="rc-label">Damage Photos</div>
                    <div class="photo-row">
                        <?php if (!empty($photos)): ?>
                            <?php foreach (array_slice($photos,0,4) as $ph): ?>
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
                            <option value="pending"     <?= $rr['status']==='pending'    ?'selected':'' ?>>Pending</option>
                            <option value="in_progress" <?= $rr['status']==='in_progress'?'selected':'' ?>>In Progress</option>
                            <option value="completed"   <?= $rr['status']==='completed'  ?'selected':'' ?>>Completed</option>
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