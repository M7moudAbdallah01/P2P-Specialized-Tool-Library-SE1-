<?php
/* =========================================================
   1) SESSION + AUTH
========================================================= */
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}

/* =========================================================
   2) DATABASE
========================================================= */
$db   = Database::getInstance();
$conn = $db->getConnection();

/* =========================================================
   3) ACTIONS
========================================================= */

// Resolve dispute + decide deposit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve'])) {
    $did      = intval($_POST['dispute_id']);
    $decision = $conn->real_escape_string($_POST['decision']); // 'refund' | 'forfeit' | 'split'
    $note     = $conn->real_escape_string($_POST['admin_note']);

    $conn->query("
        UPDATE dispute
        SET status       = 'resolved',
            decision     = '$decision',
            admin_note   = '$note',
            resolved_at  = NOW()
        WHERE dispute_id = $did
    ");

    header("Location: reports.php?msg=resolved");
    exit();
}

// Reject / close dispute
if (isset($_GET['close'])) {
    $did = intval($_GET['close']);
    $conn->query("UPDATE dispute SET status = 'closed' WHERE dispute_id = $did");
    header("Location: reports.php?msg=closed");
    exit();
}

/* =========================================================
   4) FILTERS
========================================================= */
$status_filter = $_GET['status'] ?? 'open';
$search        = trim($_GET['search'] ?? '');

$where = "WHERE 1=1";
if (in_array($status_filter, ['open','resolved','closed'])) {
    $where .= " AND d.status = '$status_filter'";
}
if ($search !== '') {
    $s      = $conn->real_escape_string($search);
    $where .= " AND (reporter.name LIKE '%$s%' OR reported.name LIKE '%$s%' OR t.name LIKE '%$s%')";
}

/* =========================================================
   5) FETCH DISPUTES
========================================================= */
$disputes_q = $conn->query("
    SELECT
        d.*,
        reporter.name  AS reporter_name,
        reporter.email AS reporter_email,
        reported.name  AS reported_name,
        t.name         AS tool_name,
        t.base_price   AS tool_price
    FROM dispute d
    LEFT JOIN users  reporter ON d.reporter_id      = reporter.user_id
    LEFT JOIN users  reported ON d.reported_user_id = reported.user_id
    LEFT JOIN tools  t        ON d.tool_id          = t.tool_id
    $where
    ORDER BY d.created_at DESC
");

/* =========================================================
   6) COUNTS
========================================================= */
$cnt = [];
foreach (['open','resolved','closed'] as $s) {
    $r       = $conn->query("SELECT COUNT(*) AS c FROM dispute WHERE status = '$s'");
    $cnt[$s] = $r->fetch_assoc()['c'];
}
$cnt['total'] = $cnt['open'] + $cnt['resolved'] + $cnt['closed'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Disputes & Reports</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<style>
/* ── Stat mini row ── */
.stat-mini       { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:22px; }
.stat-mini-card  { background:var(--surface); border:1px solid var(--border); border-radius:10px; padding:14px 22px; min-width:110px; text-align:center; }
.stat-mini-card .num { font-size:1.5rem; font-weight:700; color:var(--red); }
.stat-mini-card .lbl { font-size:.72rem; color:var(--text-muted); margin-top:2px; }

/* ── Dispute card ── */
.dispute-list    { display:flex; flex-direction:column; gap:16px; }

.dispute-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 13px;
    overflow: hidden;
    transition: border-color .18s;
}
.dispute-card:hover { border-color: var(--border2); }
.dispute-card.is-open     { border-left: 3px solid var(--red); }
.dispute-card.is-resolved { border-left: 3px solid #2dbe6c; }
.dispute-card.is-closed   { border-left: 3px solid #444; }

/* card header */
.dc-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    gap: 12px;
    flex-wrap: wrap;
}
.dc-id   { font-size:.75rem; color:var(--text-muted); }
.dc-tool { font-weight:700; font-size:.95rem; }
.dc-date { font-size:.75rem; color:var(--text-muted); margin-left:auto; white-space:nowrap; }

/* status pill */
.dpill {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 10px; border-radius:20px;
    font-size:.7rem; font-weight:700; letter-spacing:.04em;
}
.dpill-open     { background:rgba(230,57,70,.13); color:var(--red);  border:1px solid rgba(230,57,70,.3); }
.dpill-resolved { background:rgba(45,190,108,.13); color:#2dbe6c;    border:1px solid rgba(45,190,108,.3); }
.dpill-closed   { background:rgba(100,100,100,.15); color:#888;       border:1px solid #333; }

/* card body */
.dc-body {
    display: grid;
    grid-template-columns: 1fr 1fr 1.4fr;
    gap: 0;
    padding: 0;
}
@media(max-width:760px){ .dc-body{ grid-template-columns:1fr; } }

.dc-col {
    padding: 16px 18px;
    border-right: 1px solid var(--border);
    font-size:.84rem;
}
.dc-col:last-child { border-right:none; }

.dc-col-label {
    font-size:.68rem; color:var(--text-dim);
    text-transform:uppercase; letter-spacing:.06em;
    margin-bottom:6px;
}
.dc-col-val { color:var(--text); font-weight:500; }
.dc-col-sub { font-size:.75rem; color:var(--text-muted); margin-top:2px; }

.reason-text {
    font-size:.83rem; color:var(--text-muted);
    line-height:1.6; margin-top:4px;
}

/* evidence / photos */
.evidence-row { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
.evidence-thumb {
    width:54px; height:54px; border-radius:7px;
    background:var(--surface2); border:1px solid var(--border2);
    display:flex; align-items:center; justify-content:center;
    color:var(--text-dim); font-size:.7rem; text-align:center;
    cursor:pointer; overflow:hidden;
}
.evidence-thumb img { width:100%; height:100%; object-fit:cover; }
.evidence-thumb.no-img { flex-direction:column; gap:3px; }

/* decision form */
.dc-foot {
    padding: 14px 18px;
    border-top: 1px solid var(--border);
    background: var(--surface2);
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}
.dc-foot label {
    font-size:.7rem; color:var(--text-dim);
    text-transform:uppercase; letter-spacing:.05em;
    display:block; margin-bottom:4px;
}
.dc-foot select,
.dc-foot textarea,
.dc-foot input {
    background: var(--surface);
    border: 1px solid var(--border2);
    border-radius:7px;
    color: var(--text);
    font-family: inherit;
    font-size:.82rem;
    padding: 7px 10px;
    outline:none;
    transition: border-color .15s;
    resize: vertical;
}
.dc-foot select:focus,
.dc-foot textarea:focus { border-color:var(--red); }
.dc-foot select { min-width:150px; }
.dc-foot textarea { min-width:220px; min-height:52px; }

.dc-foot-actions { display:flex; gap:8px; align-items:flex-end; margin-left:auto; }

.btn-resolve {
    background:var(--red); color:#fff; border:none;
    border-radius:8px; padding:8px 18px;
    font-family:inherit; font-size:.82rem; font-weight:700;
    cursor:pointer; transition:background .15s;
    display:inline-flex; align-items:center; gap:6px;
}
.btn-resolve:hover { background:var(--red-dim); }

.btn-close-dispute {
    background:transparent; color:var(--text-muted);
    border:1px solid var(--border2);
    border-radius:8px; padding:8px 14px;
    font-family:inherit; font-size:.82rem;
    cursor:pointer; transition:all .15s;
    display:inline-flex; align-items:center; gap:6px;
    text-decoration:none;
}
.btn-close-dispute:hover { border-color:var(--red); color:var(--red); }

/* resolved decision badge */
.decision-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 12px; border-radius:20px;
    font-size:.75rem; font-weight:700;
}
.db-refund  { background:rgba(45,190,108,.13); color:#2dbe6c; border:1px solid rgba(45,190,108,.3); }
.db-forfeit { background:rgba(230,57,70,.13);  color:var(--red); border:1px solid rgba(230,57,70,.3); }
.db-split   { background:rgba(251,191,36,.13); color:#fbbf24; border:1px solid rgba(251,191,36,.3); }

/* empty state */
.empty-state {
    text-align:center; padding:52px 20px;
    color:var(--text-muted);
}
.empty-state i { font-size:2.5rem; margin-bottom:14px; color:var(--text-dim); display:block; }
</style>
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

    <div class="role-badge role-admin">ADMIN</div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link">
            <i class="fa fa-gauge"></i> Dashboard
        </a>
        <a href="../Tools/tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
        </a>
        <a href="../Tools/categories.php" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg> Categories
        </a>
        <a href="members.php" class="nav-link">
            <i class="fa fa-users"></i> Members
        </a>
        <a href="reservations.php" class="nav-link">
            <i class="fa fa-calendar"></i> Reservations
        </a>
        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
        </a>
        <a href="reports.php" class="nav-link active">
            <i class="fa fa-scale-balanced"></i> Disputes &amp; Reports
        </a>
    </div>
</div>

<!-- ═══════════════════════════════
     RIGHT SIDE
═══════════════════════════════ -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <button class="hamburger"><i class="fa fa-bars"></i></button>
        <div class="topbar-title">Disputes &amp; Reports</div>
        <div class="topbar-right">
            <div class="notif-wrap">
                <button class="icon-btn"><i class="fa fa-bell"></i></button>
                <?php if ($cnt['open'] > 0): ?>
                    <div class="notif-dot"><?= $cnt['open'] ?></div>
                <?php endif; ?>
            </div>
            <button class="avatar-btn admin-avatar">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Admin</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content">

        <!-- ALERT -->
        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?php
            echo match($_GET['msg']) {
                'resolved' => '<i class="fa fa-circle-check"></i> Dispute resolved and decision recorded.',
                'closed'   => '<i class="fa fa-circle-xmark"></i> Dispute closed.',
                default    => 'Action completed.'
            };
            ?>
        </div>
        <?php endif; ?>

        <!-- STAT MINI -->
        <div class="stat-mini">
            <div class="stat-mini-card">
                <div class="num"><?= $cnt['total'] ?></div>
                <div class="lbl">Total</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:var(--red);"><?= $cnt['open'] ?></div>
                <div class="lbl">Open</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#2dbe6c;"><?= $cnt['resolved'] ?></div>
                <div class="lbl">Resolved</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#888;"><?= $cnt['closed'] ?></div>
                <div class="lbl">Closed</div>
            </div>
        </div>

        <!-- FILTERS -->
        <form method="GET" class="filters" style="margin-bottom:20px;">
            <input type="text" name="search"
                   placeholder="Search by user or tool..."
                   value="<?= htmlspecialchars($search) ?>">

            <select name="status">
                <option value="open"     <?= $status_filter==='open'     ? 'selected':'' ?>>Open</option>
                <option value="resolved" <?= $status_filter==='resolved' ? 'selected':'' ?>>Resolved</option>
                <option value="closed"   <?= $status_filter==='closed'   ? 'selected':'' ?>>Closed</option>
            </select>

            <button class="btn btn-red"><i class="fa fa-search"></i> Filter</button>
            <a href="reports.php" class="btn btn-ghost">Reset</a>
        </form>

        <!-- DISPUTE LIST -->
        <div class="dispute-list">

        <?php if ($disputes_q->num_rows === 0): ?>
            <div class="card empty-state">
                <i class="fa fa-scale-balanced"></i>
                No disputes found for this filter.
            </div>

        <?php else: ?>

        <?php while ($d = $disputes_q->fetch_assoc()):
            $pill_cls = match($d['status']) {
                'resolved' => 'dpill-resolved',
                'closed'   => 'dpill-closed',
                default    => 'dpill-open'
            };
            $card_cls = 'is-' . $d['status'];
        ?>

        <div class="dispute-card <?= $card_cls ?>">

            <!-- HEAD -->
            <div class="dc-head">
                <span class="dc-id">#<?= $d['dispute_id'] ?></span>
                <span class="dc-tool">
                    <i class="fa fa-wrench" style="color:var(--red);font-size:.8rem;"></i>
                    <?= htmlspecialchars($d['tool_name'] ?? 'Unknown Tool') ?>
                </span>
                <span class="dpill <?= $pill_cls ?>">
                    <?= strtoupper($d['status']) ?>
                </span>
                <span class="dc-date">
                    <i class="fa fa-clock" style="font-size:.7rem;"></i>
                    <?= date('M d, Y — H:i', strtotime($d['created_at'])) ?>
                </span>
            </div>

            <!-- BODY: 3 cols -->
            <div class="dc-body">

                <!-- Col 1: Parties -->
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

                <!-- Col 2: Reason + Evidence -->
                <div class="dc-col">
                    <div class="dc-col-label">Reason</div>
                    <div class="reason-text">
                        <?= nl2br(htmlspecialchars($d['reason'] ?? 'No reason provided.')) ?>
                    </div>

                    <div class="dc-col-label" style="margin-top:12px;">Evidence / Photos</div>
                    <div class="evidence-row">
                        <?php if (!empty($d['evidence_path'])): ?>
                            <div class="evidence-thumb">
                                <img src="<?= htmlspecialchars($d['evidence_path']) ?>"
                                     alt="Evidence"
                                     onclick="window.open(this.src,'_blank')">
                            </div>
                        <?php else: ?>
                            <div class="evidence-thumb no-img">
                                <i class="fa fa-image"></i>
                                <span>No photo</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Col 3: Admin note / Decision (if resolved) -->
                <div class="dc-col">
                    <?php if ($d['status'] === 'resolved'): ?>
                        <div class="dc-col-label">Decision</div>
                        <?php
                        $db_cls = match($d['decision'] ?? '') {
                            'refund'  => 'db-refund',
                            'forfeit' => 'db-forfeit',
                            'split'   => 'db-split',
                            default   => 'db-refund'
                        };
                        $db_icon = match($d['decision'] ?? '') {
                            'refund'  => 'fa-rotate-left',
                            'forfeit' => 'fa-ban',
                            'split'   => 'fa-code-branch',
                            default   => 'fa-circle'
                        };
                        ?>
                        <div class="decision-badge <?= $db_cls ?>">
                            <i class="fa <?= $db_icon ?>"></i>
                            <?= ucfirst($d['decision'] ?? '—') ?> Deposit
                        </div>

                        <div class="dc-col-label" style="margin-top:12px;">Admin Note</div>
                        <div class="reason-text">
                            <?= nl2br(htmlspecialchars($d['admin_note'] ?? '—')) ?>
                        </div>

                        <?php if (!empty($d['resolved_at'])): ?>
                        <div class="dc-col-sub" style="margin-top:10px;">
                            <i class="fa fa-check-circle" style="color:#2dbe6c;"></i>
                            Resolved <?= date('M d, Y', strtotime($d['resolved_at'])) ?>
                        </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="dc-col-label">Mediation Log</div>
                        <div class="reason-text" style="color:var(--text-dim);">
                            Awaiting admin review. Use the form below to issue a decision.
                        </div>
                    <?php endif; ?>
                </div>

            </div><!-- /.dc-body -->

            <!-- FOOTER: action form (open only) -->
            <?php if ($d['status'] === 'open'): ?>
            <div class="dc-foot">

                <form method="POST" style="display:contents;">
                    <input type="hidden" name="dispute_id" value="<?= $d['dispute_id'] ?>">
                    <input type="hidden" name="resolve"    value="1">

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
                        <textarea name="admin_note"
                                  placeholder="Document your reasoning here..."
                                  rows="2"></textarea>
                    </div>

                    <div class="dc-foot-actions">
                        <button type="submit" class="btn-resolve">
                            <i class="fa fa-gavel"></i> Issue Decision
                        </button>
                    </div>
                </form>

                <a href="?close=<?= $d['dispute_id'] ?>&status=<?= $status_filter ?>"
                   class="btn-close-dispute"
                   onclick="return confirm('Close this dispute without a decision?')">
                    <i class="fa fa-xmark"></i> Close
                </a>

            </div>
            <?php endif; ?>

        </div><!-- /.dispute-card -->

        <?php endwhile; ?>
        <?php endif; ?>

        </div><!-- /.dispute-list -->

    </div><!-- /.main-content -->
</div><!-- /.layout-right -->

</body>
</html>