<?php
/* =========================================================
   AUTH
========================================================= */
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();

/* =========================================================
   ACTIONS
========================================================= */

/* ── Resolve dispute ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_dispute'])) {
    $did      = intval($_POST['dispute_id']);
    $decision = $conn->real_escape_string($_POST['decision']);
    $note     = $conn->real_escape_string($_POST['admin_note']);

    $conn->query("
        UPDATE dispute
        SET status='resolved', decision='$decision', admin_note='$note', resolved_at=NOW()
        WHERE dispute_id=$did
    ");
    header("Location: reports.php?tab=disputes&msg=resolved"); exit();
}

/* ── Close dispute ── */
if (isset($_GET['close_dispute'])) {
    $did = intval($_GET['close_dispute']);
    $conn->query("UPDATE dispute SET status='closed' WHERE dispute_id=$did");
    header("Location: reports.php?tab=disputes&msg=closed"); exit();
}

/* ── Adjust trust score ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_trust'])) {
    $target_uid = intval($_POST['target_user_id']);
    $delta      = intval($_POST['trust_delta']);        // e.g. -10 or +10
    $conn->query("
        UPDATE users
        SET trust_score = GREATEST(0, LEAST(100, COALESCE(trust_score,100) + $delta))
        WHERE user_id = $target_uid
    ");
    // Also update damage_declaration status
    $dmg_id = intval($_POST['damage_id']);
    $conn->query("UPDATE damage_declarations SET status='resolved' WHERE id=$dmg_id");
    header("Location: reports.php?tab=damage&msg=trust_updated"); exit();
}

/* ── Send to technician ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_repair'])) {
    $dmg_id   = intval($_POST['damage_id']);
    $tech_id  = intval($_POST['technician_id']);
    $adm_note = $conn->real_escape_string($_POST['repair_note'] ?? '');

    // Fetch damage info
    $dq = $conn->query("SELECT * FROM damage_declarations WHERE id=$dmg_id");
    $dd = $dq->fetch_assoc();

    $tn_esc  = $conn->real_escape_string($dd['tool_name'] ?? '');
    $sev_esc = $conn->real_escape_string($dd['severity']  ?? 'low');
    $dsc_esc = $conn->real_escape_string($dd['description'] ?? '');
    $ph_esc  = $conn->real_escape_string($dd['photos']    ?? '');

    $conn->query("
        INSERT INTO repair_requests (damage_id, technician_id, tool_name, severity, description, photos, admin_note)
        VALUES ($dmg_id, $tech_id, '$tn_esc', '$sev_esc', '$dsc_esc', '$ph_esc', '$adm_note')
    ");
    $conn->query("UPDATE damage_declarations SET status='reviewing' WHERE id=$dmg_id");
    header("Location: reports.php?tab=damage&msg=repair_sent"); exit();
}

/* =========================================================
   FILTERS & TAB
========================================================= */
$tab    = $_GET['tab']    ?? 'damage';
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

/* =========================================================
   FETCH DAMAGE DECLARATIONS
========================================================= */
$dmg_where = "WHERE 1=1";
if ($status_filter && in_array($status_filter,['pending','reviewing','resolved'])) {
    $dmg_where .= " AND dd.status='$status_filter'";
}
if ($search) {
    $s = $conn->real_escape_string($search);
    $dmg_where .= " AND (dd.tool_name LIKE '%$s%' OR dd.reservation_id LIKE '%$s%' OR u.name LIKE '%$s%')";
}

$damage_q = $conn->query("
    SELECT dd.*, u.name AS reporter_name, u.email AS reporter_email,
           u.trust_score, u.user_id AS reporter_uid
    FROM damage_declarations dd
    LEFT JOIN users u ON dd.reporter_id = u.user_id
    $dmg_where
    ORDER BY dd.submitted_at DESC
");

/* =========================================================
   FETCH DISPUTES
========================================================= */
$dis_status = $_GET['dis_status'] ?? 'open';
$dis_search = trim($_GET['dis_search'] ?? '');

$dis_where = "WHERE 1=1";
if (in_array($dis_status,['open','resolved','closed'])) {
    $dis_where .= " AND d.status='$dis_status'";
}
if ($dis_search) {
    $s = $conn->real_escape_string($dis_search);
    $dis_where .= " AND (reporter.name LIKE '%$s%' OR reported.name LIKE '%$s%' OR t.name LIKE '%$s%')";
}

$disputes_q = $conn->query("
    SELECT d.*,
           reporter.name  AS reporter_name,
           reporter.email AS reporter_email,
           reported.name  AS reported_name,
           t.name         AS tool_name,
           t.base_price   AS tool_price
    FROM dispute d
    LEFT JOIN users  reporter ON d.reporter_id      = reporter.user_id
    LEFT JOIN users  reported ON d.reported_user_id = reported.user_id
    LEFT JOIN tools  t        ON d.tool_id          = t.tool_id
    $dis_where
    ORDER BY d.created_at DESC
");

/* =========================================================
   COUNTS
========================================================= */
$dmg_cnt = [];
foreach (['pending','reviewing','resolved'] as $s) {
    $r = $conn->query("SELECT COUNT(*) AS c FROM damage_declarations WHERE status='$s'");
    $dmg_cnt[$s] = $r->fetch_assoc()['c'];
}
$dmg_cnt['total'] = array_sum($dmg_cnt);

$dis_cnt = [];
foreach (['open','resolved','closed'] as $s) {
    $r = $conn->query("SELECT COUNT(*) AS c FROM dispute WHERE status='$s'");
    $dis_cnt[$s] = $r->fetch_assoc()['c'];
}

/* ── Technicians list ── */
$techs_q = $conn->query("SELECT user_id, name FROM users WHERE role='technician' ORDER BY name");
$technicians = [];
if ($techs_q) { while ($t = $techs_q->fetch_assoc()) $technicians[] = $t; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Reports &amp; Disputes</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/reports.css">
<style>
/* ══ TAB BAR ══ */
.tab-bar{display:flex;gap:6px;margin-bottom:24px;background:var(--card,#1a1a1a);
    padding:6px;border-radius:12px;width:fit-content;}
.tab-btn{padding:9px 22px;border-radius:8px;border:none;cursor:pointer;font-family:'Poppins',sans-serif;
    font-size:.82rem;font-weight:600;color:var(--text-dim,#888);background:transparent;transition:.2s;}
.tab-btn.active{background:var(--red,#ff2e2e);color:#fff;}

/* ══ STAT MINI ══ */
.stat-mini{display:flex;gap:14px;margin-bottom:22px;flex-wrap:wrap;}
.stat-mini-card{background:var(--card,#1a1a1a);border:1px solid var(--border,#2a2a2a);
    border-radius:10px;padding:14px 22px;min-width:100px;text-align:center;}
.stat-mini-card .num{font-size:1.5rem;font-weight:700;color:var(--text,#fff);}
.stat-mini-card .lbl{font-size:.72rem;color:var(--text-dim,#888);margin-top:2px;}

/* ══ FILTERS ══ */
.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;}
.filters input,.filters select{background:var(--card,#1a1a1a);border:1px solid var(--border,#2a2a2a);
    color:var(--text,#fff);border-radius:8px;padding:8px 14px;font-family:'Poppins',sans-serif;font-size:.82rem;}
.filters input:focus,.filters select:focus{outline:none;border-color:var(--red,#ff2e2e);}

/* ══ DAMAGE CARD ══ */
.damage-card{background:var(--card,#1a1a1a);border:1px solid var(--border,#2a2a2a);
    border-radius:14px;margin-bottom:16px;overflow:hidden;transition:.2s;}
.damage-card:hover{border-color:#444;}
.damage-card.is-pending{border-left:4px solid #f59e0b;}
.damage-card.is-reviewing{border-left:4px solid #6366f1;}
.damage-card.is-resolved{border-left:4px solid #2dbe6c;}

.dc-head{display:flex;align-items:center;gap:14px;padding:14px 20px;
    border-bottom:1px solid var(--border,#2a2a2a);flex-wrap:wrap;}
.dc-id{font-size:.78rem;color:var(--text-dim,#888);font-weight:600;
    background:#222;padding:3px 10px;border-radius:6px;}
.dc-ref{font-weight:700;font-size:.88rem;color:var(--text,#fff);flex:1;}
.dc-tool{font-size:.82rem;color:var(--text-dim,#888);}
.dc-date{font-size:.75rem;color:var(--text-dim,#888);margin-left:auto;}

.spill{padding:3px 12px;border-radius:20px;font-size:.72rem;font-weight:700;text-transform:uppercase;}
.spill-pending{background:#f59e0b22;color:#f59e0b;}
.spill-reviewing{background:#6366f122;color:#6366f1;}
.spill-resolved{background:#2dbe6c22;color:#2dbe6c;}

.sev-badge{padding:2px 10px;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase;}
.sev-low{background:#2dbe6c22;color:#2dbe6c;}
.sev-medium{background:#f59e0b22;color:#f59e0b;}
.sev-high{background:#ff2e2e22;color:#ff2e2e;}

.dc-body{display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;padding:0;}
.dc-col{padding:16px 20px;border-right:1px solid var(--border,#2a2a2a);}
.dc-col:last-child{border-right:none;}
.dc-col-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;
    color:var(--text-dim,#888);margin-bottom:5px;}
.dc-col-val{font-size:.85rem;color:var(--text,#fff);font-weight:500;}
.dc-col-sub{font-size:.75rem;color:var(--text-dim,#888);margin-top:3px;}

.desc-text{font-size:.82rem;color:var(--text-dim,#aaa);line-height:1.55;
    max-height:80px;overflow:hidden;position:relative;}

.photo-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;}
.photo-thumb{width:54px;height:54px;border-radius:8px;object-fit:cover;cursor:pointer;
    border:1px solid #333;transition:.2s;}
.photo-thumb:hover{border-color:var(--red,#ff2e2e);transform:scale(1.05);}
.no-photo{width:54px;height:54px;border-radius:8px;background:#222;border:1px dashed #333;
    display:flex;align-items:center;justify-content:center;color:#555;font-size:.8rem;}

/* ══ DETAIL PANEL (expand) ══ */
.dc-detail{display:none;padding:16px 20px;border-top:1px solid var(--border,#2a2a2a);
    background:#161616;}
.dc-detail.open{display:block;}

/* ══ ACTION FOOTER ══ */
.dc-foot{display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;
    padding:14px 20px;border-top:1px solid var(--border,#2a2a2a);background:#161616;}
.dc-foot label{font-size:.75rem;color:var(--text-dim,#888);display:block;margin-bottom:4px;}
.dc-foot select,.dc-foot textarea,.dc-foot input[type=number]{
    background:#1e1e1e;border:1px solid #333;color:var(--text,#fff);
    border-radius:8px;padding:8px 12px;font-family:'Poppins',sans-serif;font-size:.8rem;min-width:160px;}
.dc-foot select:focus,.dc-foot textarea:focus{outline:none;border-color:var(--red,#ff2e2e);}
.dc-foot textarea{resize:vertical;min-height:60px;flex:1;}

/* ══ TRUST SCORE BAR ══ */
.trust-bar-wrap{margin-top:6px;}
.trust-bar{height:6px;border-radius:3px;background:#222;overflow:hidden;width:120px;}
.trust-fill{height:100%;border-radius:3px;transition:width .4s;}

/* ══ BUTTONS ══ */
.btn{padding:8px 18px;border-radius:8px;border:none;cursor:pointer;
    font-family:'Poppins',sans-serif;font-size:.8rem;font-weight:600;}
.btn-red{background:var(--red,#ff2e2e);color:#fff;}
.btn-ghost{background:transparent;border:1px solid #333;color:var(--text-dim,#888);}
.btn-green{background:#2dbe6c;color:#fff;}
.btn-purple{background:#6366f1;color:#fff;}
.btn-orange{background:#f59e0b;color:#fff;}
.btn-resolve{background:var(--red,#ff2e2e);color:#fff;padding:9px 20px;border-radius:8px;
    border:none;cursor:pointer;font-family:'Poppins',sans-serif;font-size:.8rem;font-weight:600;}
.btn-close-dispute{background:transparent;border:1px solid #333;color:#888;padding:9px 16px;
    border-radius:8px;cursor:pointer;font-size:.8rem;text-decoration:none;display:inline-block;}

/* ══ DISPUTE CARDS ══ */
.dispute-card{background:var(--card,#1a1a1a);border:1px solid var(--border,#2a2a2a);
    border-radius:14px;margin-bottom:16px;overflow:hidden;}
.dispute-card.is-open{border-left:4px solid var(--red,#ff2e2e);}
.dispute-card.is-resolved{border-left:4px solid #2dbe6c;}
.dispute-card.is-closed{border-left:4px solid #555;}
.dpill{padding:3px 12px;border-radius:20px;font-size:.72rem;font-weight:700;}
.dpill-open{background:#ff2e2e22;color:#ff2e2e;}
.dpill-resolved{background:#2dbe6c22;color:#2dbe6c;}
.dpill-closed{background:#55555522;color:#888;}
.decision-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;
    border-radius:8px;font-size:.8rem;font-weight:600;}
.db-refund{background:#2dbe6c22;color:#2dbe6c;}
.db-forfeit{background:#ff2e2e22;color:#ff2e2e;}
.db-split{background:#6366f122;color:#6366f1;}

/* ══ EMPTY ══ */
.empty-state{text-align:center;padding:50px;color:var(--text-dim,#888);font-size:.9rem;}
.empty-state i{font-size:2.5rem;display:block;margin-bottom:12px;opacity:.3;}

/* ══ ALERT ══ */
.alert{padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:.83rem;
    display:flex;align-items:center;gap:10px;}
.alert-success{background:#2dbe6c22;color:#2dbe6c;border:1px solid #2dbe6c44;}
.alert-info{background:#6366f122;color:#6366f1;border:1px solid #6366f144;}

/* ══ TOGGLE ARROW ══ */
.toggle-detail{background:none;border:none;color:var(--text-dim,#888);cursor:pointer;
    font-size:.85rem;display:flex;align-items:center;gap:5px;padding:0;}
.toggle-detail:hover{color:var(--text,#fff);}

@media(max-width:900px){.dc-body{grid-template-columns:1fr;}}
</style>
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
        <a href="members.php"             class="nav-link"><i class="fa fa-users"></i> Members</a>
        <a href="reservations.php"        class="nav-link"><i class="fa fa-calendar"></i> Reservations</a>
        <a href="chat.php"                class="nav-link"><i class="fa fa-comments"></i> Chat</a>
        <a href="reports.php"             class="nav-link active">
            <i class="fa fa-scale-balanced"></i> Disputes &amp; Reports
        </a>
    </div>
</div>

<!-- ═══ RIGHT ═══ -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <button class="hamburger"><i class="fa fa-bars"></i></button>
        <div class="topbar-title">Disputes &amp; Reports</div>
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
                'resolved'      => 'Dispute resolved and decision recorded.',
                'closed'        => 'Dispute closed.',
                'trust_updated' => 'Trust score updated and report resolved.',
                'repair_sent'   => 'Repair request sent to technician.',
                default         => 'Action completed.'
            } ?>
        </div>
        <?php endif; ?>

        <!-- TAB BAR -->
        <div class="tab-bar">
            <button class="tab-btn <?= $tab==='damage'?'active':'' ?>"
                    onclick="switchTab('damage')">
                <i class="fa fa-triangle-exclamation"></i> Damage Reports
                <?php if ($dmg_cnt['pending'] > 0): ?>
                    <span style="background:#ff2e2e;color:#fff;border-radius:10px;padding:1px 7px;
                                 font-size:.68rem;margin-left:4px;"><?= $dmg_cnt['pending'] ?></span>
                <?php endif; ?>
            </button>
            <button class="tab-btn <?= $tab==='disputes'?'active':'' ?>"
                    onclick="switchTab('disputes')">
                <i class="fa fa-scale-balanced"></i> Disputes
                <?php if ($dis_cnt['open'] > 0): ?>
                    <span style="background:#ff2e2e;color:#fff;border-radius:10px;padding:1px 7px;
                                 font-size:.68rem;margin-left:4px;"><?= $dis_cnt['open'] ?></span>
                <?php endif; ?>
            </button>
        </div>

        <!-- ════════════════════════════════════════
             TAB 1: DAMAGE DECLARATIONS
        ════════════════════════════════════════ -->
        <div id="tab-damage" class="tab-content" style="<?= $tab!=='damage'?'display:none':'' ?>">

            <!-- stats -->
            <div class="stat-mini">
                <div class="stat-mini-card"><div class="num"><?= $dmg_cnt['total'] ?></div><div class="lbl">Total</div></div>
                <div class="stat-mini-card"><div class="num" style="color:#f59e0b;"><?= $dmg_cnt['pending'] ?></div><div class="lbl">Pending</div></div>
                <div class="stat-mini-card"><div class="num" style="color:#6366f1;"><?= $dmg_cnt['reviewing'] ?></div><div class="lbl">Reviewing</div></div>
                <div class="stat-mini-card"><div class="num" style="color:#2dbe6c;"><?= $dmg_cnt['resolved'] ?></div><div class="lbl">Resolved</div></div>
            </div>

            <!-- filters -->
            <form method="GET" class="filters">
                <input type="hidden" name="tab" value="damage">
                <input type="text" name="search" placeholder="Search tool, reservation, user..."
                       value="<?= htmlspecialchars($search) ?>">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="pending"   <?= $status_filter==='pending'  ?'selected':'' ?>>Pending</option>
                    <option value="reviewing" <?= $status_filter==='reviewing'?'selected':'' ?>>Reviewing</option>
                    <option value="resolved"  <?= $status_filter==='resolved' ?'selected':'' ?>>Resolved</option>
                </select>
                <button class="btn btn-red"><i class="fa fa-search"></i> Filter</button>
                <a href="reports.php?tab=damage" class="btn btn-ghost">Reset</a>
            </form>

            <!-- list -->
            <?php if (!$damage_q || $damage_q->num_rows === 0): ?>
            <div class="empty-state"><i class="fa fa-triangle-exclamation"></i>No damage reports found.</div>
            <?php else: ?>
            <?php while ($dd = $damage_q->fetch_assoc()):
                $sev_cls  = 'sev-' . ($dd['severity'] ?? 'low');
                $stat_cls = 'is-' . ($dd['status'] ?? 'pending');
                $spill_cls= 'spill-' . ($dd['status'] ?? 'pending');
                $photos   = json_decode($dd['photos'] ?? '[]', true);
                $trust    = intval($dd['trust_score'] ?? 100);
                $trust_color = $trust >= 70 ? '#2dbe6c' : ($trust >= 40 ? '#f59e0b' : '#ff2e2e');
            ?>
            <div class="damage-card <?= $stat_cls ?>" id="dcard-<?= $dd['id'] ?>">

                <!-- HEAD -->
                <div class="dc-head">
                    <span class="dc-id">#<?= $dd['id'] ?></span>
                    <span class="dc-ref"><?= htmlspecialchars($dd['reference_no']) ?></span>
                    <span class="dc-tool"><i class="fa fa-wrench" style="color:var(--red);font-size:.75rem;"></i>
                        <?= htmlspecialchars($dd['tool_name']) ?></span>
                    <span class="sev-badge <?= $sev_cls ?>"><?= ucfirst($dd['severity']) ?></span>
                    <span class="spill <?= $spill_cls ?>"><?= strtoupper($dd['status']) ?></span>
                    <span class="dc-date"><i class="fa fa-clock" style="font-size:.7rem;"></i>
                        <?= date('M d, Y — H:i', strtotime($dd['submitted_at'])) ?></span>
                    <button class="toggle-detail" onclick="toggleDetail(<?= $dd['id'] ?>)">
                        <i class="fa fa-chevron-down" id="arr-<?= $dd['id'] ?>"></i> Details
                    </button>
                </div>

                <!-- BODY: 3 cols -->
                <div class="dc-body">

                    <!-- Col 1: Reporter -->
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
                                <?= $trust ?>/100
                            </div>
                            <div class="trust-bar">
                                <div class="trust-fill" style="width:<?= $trust ?>%;background:<?= $trust_color ?>;"></div>
                            </div>
                        </div>

                        <div class="dc-col-label" style="margin-top:12px;">Reservation</div>
                        <div class="dc-col-val"><?= htmlspecialchars($dd['reservation_id']) ?></div>
                        <div class="dc-col-sub"><?= htmlspecialchars($dd['location']) ?></div>
                    </div>

                    <!-- Col 2: Damage Info -->
                    <div class="dc-col">
                        <div class="dc-col-label">Damage Type</div>
                        <div class="dc-col-val"><?= htmlspecialchars(ucwords(str_replace('_',' ',$dd['damage_type']))) ?></div>

                        <div class="dc-col-label" style="margin-top:12px;">Date of Damage</div>
                        <div class="dc-col-val"><?= date('d M Y', strtotime($dd['damage_date'])) ?></div>

                        <?php if ($dd['witness']): ?>
                        <div class="dc-col-label" style="margin-top:12px;">Witness</div>
                        <div class="dc-col-val"><?= htmlspecialchars($dd['witness']) ?></div>
                        <?php endif; ?>

                        <div class="dc-col-label" style="margin-top:12px;">Description</div>
                        <div class="desc-text"><?= nl2br(htmlspecialchars($dd['description'] ?? '—')) ?></div>
                    </div>

                    <!-- Col 3: Photos -->
                    <div class="dc-col">
                        <div class="dc-col-label">Evidence Photos</div>
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

                        <?php if ($dd['document_path']): ?>
                        <div class="dc-col-label" style="margin-top:14px;">Document</div>
                        <a href="<?= htmlspecialchars($dd['document_path']) ?>" target="_blank"
                           style="color:#6366f1;font-size:.8rem;">
                            <i class="fa fa-file-pdf"></i> View Document
                        </a>
                        <?php endif; ?>
                    </div>

                </div><!-- /.dc-body -->

                <!-- DETAIL PANEL (hidden by default) -->
                <div class="dc-detail" id="detail-<?= $dd['id'] ?>">
                    <div style="font-size:.82rem;color:var(--text-dim);margin-bottom:10px;">
                        <strong style="color:var(--text);">Full Description:</strong><br>
                        <?= nl2br(htmlspecialchars($dd['description'] ?? '—')) ?>
                    </div>
                </div>

                <!-- ACTION FOOTER -->
                <?php if ($dd['status'] !== 'resolved'): ?>
                <div class="dc-foot">

                    <!-- Adjust Trust -->
                    <form method="POST" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                        <input type="hidden" name="adjust_trust" value="1">
                        <input type="hidden" name="damage_id"    value="<?= $dd['id'] ?>">
                        <input type="hidden" name="target_user_id" value="<?= $dd['reporter_uid'] ?>">
                        <div>
                            <label>Adjust Trust Score</label>
                            <select name="trust_delta">
                                <option value="-20">-20 (Severe violation)</option>
                                <option value="-10" selected>-10 (Moderate)</option>
                                <option value="-5">-5 (Minor)</option>
                                <option value="0">No change</option>
                                <option value="+5">+5 (Good behavior)</option>
                                <option value="+10">+10 (Restore)</option>
                            </select>
                        </div>
                        <div>
                            <label>Blame User</label>
                            <select name="blamed_party" style="min-width:130px;">
                                <option value="reporter">Reporter (client)</option>
                                <option value="owner">Tool Owner</option>
                                <option value="shared">Shared fault</option>
                                <option value="accident">Accident / No fault</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-orange">
                            <i class="fa fa-user-shield"></i> Apply &amp; Resolve
                        </button>
                    </form>

                    <!-- Send to Technician -->
                    <?php if (!empty($technicians) || true): ?>
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
                                        <option value="<?= $tech['user_id'] ?>">
                                            <?= htmlspecialchars($tech['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label>Repair Note</label>
                            <textarea name="repair_note" rows="1"
                                      placeholder="Instructions for technician..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-purple"
                                <?= empty($technicians)?'disabled':'' ?>>
                            <i class="fa fa-screwdriver-wrench"></i> Send for Repair
                        </button>
                    </form>
                    <?php endif; ?>

                </div>
                <?php else: ?>
                <div class="dc-foot" style="padding:12px 20px;color:#2dbe6c;font-size:.82rem;">
                    <i class="fa fa-circle-check"></i> This report has been resolved.
                </div>
                <?php endif; ?>

            </div><!-- /.damage-card -->
            <?php endwhile; ?>
            <?php endif; ?>

        </div><!-- #tab-damage -->

        <!-- ════════════════════════════════════════
             TAB 2: DISPUTES
        ════════════════════════════════════════ -->
        <div id="tab-disputes" class="tab-content" style="<?= $tab!=='disputes'?'display:none':'' ?>">

            <!-- stats -->
            <div class="stat-mini">
                <div class="stat-mini-card">
                    <div class="num"><?= array_sum($dis_cnt) ?></div><div class="lbl">Total</div>
                </div>
                <div class="stat-mini-card">
                    <div class="num" style="color:var(--red);"><?= $dis_cnt['open'] ?></div><div class="lbl">Open</div>
                </div>
                <div class="stat-mini-card">
                    <div class="num" style="color:#2dbe6c;"><?= $dis_cnt['resolved'] ?></div><div class="lbl">Resolved</div>
                </div>
                <div class="stat-mini-card">
                    <div class="num" style="color:#888;"><?= $dis_cnt['closed'] ?></div><div class="lbl">Closed</div>
                </div>
            </div>

            <!-- filters -->
            <form method="GET" class="filters">
                <input type="hidden" name="tab" value="disputes">
                <input type="text" name="dis_search" placeholder="Search by user or tool..."
                       value="<?= htmlspecialchars($dis_search) ?>">
                <select name="dis_status">
                    <option value="open"     <?= $dis_status==='open'    ?'selected':'' ?>>Open</option>
                    <option value="resolved" <?= $dis_status==='resolved'?'selected':'' ?>>Resolved</option>
                    <option value="closed"   <?= $dis_status==='closed'  ?'selected':'' ?>>Closed</option>
                </select>
                <button class="btn btn-red"><i class="fa fa-search"></i> Filter</button>
                <a href="reports.php?tab=disputes" class="btn btn-ghost">Reset</a>
            </form>

            <!-- dispute list -->
            <?php if (!$disputes_q || $disputes_q->num_rows === 0): ?>
            <div class="empty-state"><i class="fa fa-scale-balanced"></i>No disputes found.</div>
            <?php else: ?>
            <?php while ($d = $disputes_q->fetch_assoc()):
                $pill_cls = match($d['status']) {
                    'resolved'=>'dpill-resolved','closed'=>'dpill-closed',default=>'dpill-open'
                };
                $card_cls = 'is-' . $d['status'];
            ?>
            <div class="dispute-card <?= $card_cls ?>">

                <div class="dc-head">
                    <span class="dc-id">#<?= $d['dispute_id'] ?></span>
                    <span class="dc-tool">
                        <i class="fa fa-wrench" style="color:var(--red);font-size:.8rem;"></i>
                        <?= htmlspecialchars($d['tool_name'] ?? 'Unknown Tool') ?>
                    </span>
                    <span class="dpill <?= $pill_cls ?>"><?= strtoupper($d['status']) ?></span>
                    <span class="dc-date">
                        <i class="fa fa-clock" style="font-size:.7rem;"></i>
                        <?= date('M d, Y — H:i', strtotime($d['created_at'])) ?>
                    </span>
                </div>

                <div class="dc-body">
                    <!-- Parties -->
                    <div class="dc-col">
                        <div class="dc-col-label">Reporter</div>
                        <div class="dc-col-val">
                            <i class="fa fa-user" style="color:var(--red);font-size:.75rem;"></i>
                            <?= htmlspecialchars($d['reporter_name'] ?? '—') ?>
                        </div>
                        <div class="dc-col-sub"><?= htmlspecialchars($d['reporter_email'] ?? '') ?></div>

                        <div class="dc-col-label" style="margin-top:12px;">Reported</div>
                        <div class="dc-col-val">
                            <i class="fa fa-user-slash" style="color:#888;font-size:.75rem;"></i>
                            <?= htmlspecialchars($d['reported_name'] ?? '—') ?>
                        </div>
                        <div class="dc-col-sub">
                            Tool value: <strong style="color:var(--text);">$<?= $d['tool_price'] ?? '—' ?></strong>
                        </div>
                    </div>

                    <!-- Reason + Evidence -->
                    <div class="dc-col">
                        <div class="dc-col-label">Reason</div>
                        <div class="desc-text">
                            <?= nl2br(htmlspecialchars($d['reason'] ?? 'No reason provided.')) ?>
                        </div>
                        <div class="dc-col-label" style="margin-top:12px;">Evidence</div>
                        <div class="photo-row">
                            <?php if (!empty($d['evidence_path'])): ?>
                                <img src="<?= htmlspecialchars($d['evidence_path']) ?>"
                                     class="photo-thumb" onclick="window.open(this.src,'_blank')" alt="evidence">
                            <?php else: ?>
                                <div class="no-photo"><i class="fa fa-image"></i></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Decision / Note -->
                    <div class="dc-col">
                        <?php if ($d['status'] === 'resolved'): ?>
                            <div class="dc-col-label">Decision</div>
                            <?php
                            $db_cls  = match($d['decision']??'') {'refund'=>'db-refund','forfeit'=>'db-forfeit','split'=>'db-split',default=>'db-refund'};
                            $db_icon = match($d['decision']??'') {'refund'=>'fa-rotate-left','forfeit'=>'fa-ban','split'=>'fa-code-branch',default=>'fa-circle'};
                            ?>
                            <div class="decision-badge <?= $db_cls ?>">
                                <i class="fa <?= $db_icon ?>"></i>
                                <?= ucfirst($d['decision']??'—') ?> Deposit
                            </div>
                            <div class="dc-col-label" style="margin-top:12px;">Admin Note</div>
                            <div class="desc-text"><?= nl2br(htmlspecialchars($d['admin_note']??'—')) ?></div>
                            <?php if (!empty($d['resolved_at'])): ?>
                            <div class="dc-col-sub" style="margin-top:10px;">
                                <i class="fa fa-check-circle" style="color:#2dbe6c;"></i>
                                Resolved <?= date('M d, Y', strtotime($d['resolved_at'])) ?>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="dc-col-label">Mediation Log</div>
                            <div class="desc-text" style="color:var(--text-dim);">
                                Awaiting admin review. Use the form below to issue a decision.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($d['status'] === 'open'): ?>
                <div class="dc-foot">
                    <form method="POST" style="display:contents;">
                        <input type="hidden" name="dispute_id"    value="<?= $d['dispute_id'] ?>">
                        <input type="hidden" name="resolve_dispute" value="1">
                        <div>
                            <label>Deposit Decision</label>
                            <select name="decision" required>
                                <option value="">— Choose —</option>
                                <option value="refund">Refund Deposit to Renter</option>
                                <option value="forfeit">Forfeit Deposit to Owner</option>
                                <option value="split">Split Deposit 50/50</option>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label>Admin Note</label>
                            <textarea name="admin_note" placeholder="Document your reasoning..." rows="2"></textarea>
                        </div>
                        <div class="dc-foot-actions">
                            <button type="submit" class="btn-resolve">
                                <i class="fa fa-gavel"></i> Issue Decision
                            </button>
                        </div>
                    </form>
                    <a href="?tab=disputes&close_dispute=<?= $d['dispute_id'] ?>&dis_status=<?= $dis_status ?>"
                       class="btn-close-dispute"
                       onclick="return confirm('Close this dispute without a decision?')">
                        <i class="fa fa-xmark"></i> Close
                    </a>
                </div>
                <?php endif; ?>

            </div><!-- /.dispute-card -->
            <?php endwhile; ?>
            <?php endif; ?>

        </div><!-- #tab-disputes -->

    </div><!-- /.main-content -->
</div><!-- /.layout-right -->

<script>
function switchTab(name) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + name).style.display = 'block';
    event.currentTarget.classList.add('active');
    // update URL without reload
    const url = new URL(window.location);
    url.searchParams.set('tab', name);
    window.history.replaceState({}, '', url);
}

function toggleDetail(id) {
    const panel = document.getElementById('detail-' + id);
    const arrow = document.getElementById('arr-' + id);
    const open  = panel.classList.toggle('open');
    arrow.style.transform = open ? 'rotate(180deg)' : '';
    arrow.style.transition = '.2s';
}
</script>
</body>
</html>