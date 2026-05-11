<?php

session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}


$db   = Database::getInstance();
$conn = $db->getConnection();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $rid    = intval($_POST['reservation_id']);
    $status = $conn->real_escape_string($_POST['status']);
    if (in_array($status, ['pending','confirmed','cancelled','completed'])) {
        $conn->query("UPDATE reservations SET status = '$status' WHERE reservation_id = $rid");
    }
    header("Location: reservations.php?msg=updated");
    exit();
}

if (isset($_GET['delete'])) {
    $rid = intval($_GET['delete']);
    $conn->query("DELETE FROM reservations WHERE reservation_id = $rid");
    header("Location: reservations.php?msg=deleted");
    exit();
}

$status_filter = $_GET['status'] ?? '';
$search        = trim($_GET['search'] ?? '');
$date_from     = $_GET['date_from'] ?? '';
$date_to       = $_GET['date_to'] ?? '';

$where = "WHERE 1=1";
if (in_array($status_filter, ['pending','confirmed','cancelled','completed'])) {
    $where .= " AND r.status = '$status_filter'";
}
if ($search !== '') {
    $s      = $conn->real_escape_string($search);
    $where .= " AND (u.name LIKE '%$s%' OR t.name LIKE '%$s%')";
}
if ($date_from !== '') {
    $df     = $conn->real_escape_string($date_from);
    $where .= " AND r.start_date >= '$df'";
}
if ($date_to !== '') {
    $dt     = $conn->real_escape_string($date_to);
    $where .= " AND r.end_date <= '$dt'";
}

$reservations_q = $conn->query("
    SELECT
        r.*,
        u.name  AS user_name,
        u.email AS user_email,
        t.name  AS tool_name,
        t.base_price
    FROM reservations r
    LEFT JOIN users u ON r.user_id  = u.user_id
    LEFT JOIN tools t ON r.tool_id  = t.tool_id
    $where
    ORDER BY r.reservation_id DESC
");

$cnt = [];
foreach (['pending','confirmed','cancelled','completed'] as $s) {
    $res     = $conn->query("SELECT COUNT(*) AS c FROM reservations WHERE status = '$s'");
    $cnt[$s] = $res->fetch_assoc()['c'];
}
$cnt['total'] = array_sum($cnt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Reservations</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/reservation.css">
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
        <a href="reservations.php" class="nav-link active">
            <i class="fa fa-calendar"></i> Reservations
        </a>
        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
        </a>
        <a href="reports.php" class="nav-link">
            <i class="fa fa-scale-balanced"></i> Reports
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
        <div class="topbar-title">Reservations</div>
        <div class="topbar-right">
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
            <?php echo match($_GET['msg']) {
                'updated' => '<i class="fa fa-circle-check"></i> Reservation status updated.',
                'deleted' => '<i class="fa fa-circle-xmark"></i> Reservation deleted.',
                default   => 'Action completed.'
            }; ?>
        </div>
        <?php endif; ?>

        <!-- STAT MINI -->
        <div class="stat-mini">
            <div class="stat-mini-card">
                <div class="num"><?= $cnt['total'] ?></div>
                <div class="lbl">Total</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#eab308;"><?= $cnt['pending'] ?></div>
                <div class="lbl">Pending</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#2dbe6c;"><?= $cnt['confirmed'] ?></div>
                <div class="lbl">Confirmed</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#6366f1;"><?= $cnt['completed'] ?></div>
                <div class="lbl">Completed</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#6b7280;"><?= $cnt['cancelled'] ?></div>
                <div class="lbl">Cancelled</div>
            </div>
        </div>

        <!-- FILTERS -->
        <form method="GET" class="filters">
            <input type="text" name="search"
                   placeholder="Search user or tool..."
                   value="<?= htmlspecialchars($search) ?>">

            <select name="status">
                <option value="">All Status</option>
                <option value="pending"   <?= $status_filter==='pending'   ? 'selected':'' ?>>Pending</option>
                <option value="confirmed" <?= $status_filter==='confirmed' ? 'selected':'' ?>>Confirmed</option>
                <option value="completed" <?= $status_filter==='completed' ? 'selected':'' ?>>Completed</option>
                <option value="cancelled" <?= $status_filter==='cancelled' ? 'selected':'' ?>>Cancelled</option>
            </select>

            <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" title="From date">
            <input type="date" name="date_to"   value="<?= htmlspecialchars($date_to) ?>"   title="To date">

            <button type="submit" class="btn-sm btn-red"><i class="fa fa-search"></i> Filter</button>
            <a href="reservations.php" class="btn-sm btn-ghost">Reset</a>
        </form>

        <!-- TABLE -->
        <div class="card" style="padding:0;overflow:hidden;">
            <?php if (!$reservations_q || $reservations_q->num_rows === 0): ?>
                <div class="empty-state">
                    <i class="fa fa-calendar-xmark"></i>
                    No reservations found.
                </div>
            <?php else: ?>
            <table class="res-table">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>User</th>
                        <th>Tool</th>
                        <th>Dates</th>
                        <th>Price/Day</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($res = $reservations_q->fetch_assoc()): ?>
                    <tr>
                        <td style="color:var(--text-muted);font-size:.8rem;">#<?= $res['reservation_id'] ?></td>
                        <td>
                            <div style="font-weight:600;"><?= htmlspecialchars($res['user_name'] ?? '—') ?></div>
                            <div style="font-size:.78rem;color:var(--text-muted);"><?= htmlspecialchars($res['user_email'] ?? '') ?></div>
                        </td>
                        <td>
                            <div style="font-weight:600;"><?= htmlspecialchars($res['tool_name'] ?? '—') ?></div>
                            <div style="font-size:.78rem;color:var(--text-muted);">$<?= number_format($res['base_price'] ?? 0, 2) ?>/day</div>
                        </td>
                        <td>
                            <div style="font-size:.82rem;">
                                <i class="fa fa-calendar-days" style="color:var(--red);margin-right:4px;"></i>
                                <?= date('M d', strtotime($res['start_date'])) ?>
                                <span style="color:var(--text-muted);"> → </span>
                                <?= date('M d, Y', strtotime($res['end_date'])) ?>
                            </div>
                        </td>
                        <td style="color:var(--text-muted);">
                            <?php
                            $days = max(1, (int)((strtotime($res['end_date']) - strtotime($res['start_date'])) / 86400));
                            $total = ($res['base_price'] ?? 0) * $days;
                            echo '$' . number_format($total, 2);
                            echo "<div style='font-size:.75rem;color:var(--text-dim);'>$days day(s)</div>";
                            ?>
                        </td>
                        <td>
                            <?php
                            $pill = match($res['status']) {
                                'confirmed' => 'pill-confirmed',
                                'cancelled' => 'pill-cancelled',
                                'completed' => 'pill-completed',
                                default     => 'pill-pending'
                            };
                            ?>
                            <span class="status-pill <?= $pill ?>">
                                <i class="fa fa-circle" style="font-size:.45rem;"></i>
                                <?= ucfirst($res['status']) ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="reservation_id" value="<?= $res['reservation_id'] ?>">
                                <select name="status" class="status-select">
                                    <option value="pending"   <?= $res['status']==='pending'   ? 'selected':'' ?>>Pending</option>
                                    <option value="confirmed" <?= $res['status']==='confirmed' ? 'selected':'' ?>>Confirmed</option>
                                    <option value="completed" <?= $res['status']==='completed' ? 'selected':'' ?>>Completed</option>
                                    <option value="cancelled" <?= $res['status']==='cancelled' ? 'selected':'' ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-sm btn-red" style="margin-left:6px;">
                                    <i class="fa fa-check"></i>
                                </button>
                            </form>
                            <a href="?delete=<?= $res['reservation_id'] ?><?= $status_filter ? '&status='.$status_filter : '' ?>"
                               class="btn-sm btn-danger"
                               style="margin-left:4px;"
                               onclick="return confirm('Delete this reservation?')">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div><!-- /main-content -->
</div><!-- /layout-right -->

<script>
// Hamburger toggle
document.querySelector('.hamburger')?.addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('open');
});
</script>
</body>
</html>