<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();

$uid = intval($_SESSION['user_id']);



$status_filter_raw = $_GET['status'] ?? '';
$search_raw        = trim($_GET['search'] ?? '');

$where = "WHERE reporter_id = $uid";

if ($status_filter_raw !== '' && in_array($status_filter_raw, ['pending','reviewing','resolved'])) {
    $sf     = $conn->real_escape_string($status_filter_raw);
    $where .= " AND status = '$sf'";
}

if ($search_raw !== '') {
    $s      = $conn->real_escape_string($search_raw);
    $where .= " AND (tool_name LIKE '%$s%' OR reference_no LIKE '%$s%')";
}



$reports_q = $conn->query("
    SELECT *
    FROM damage_declarations
    $where
    ORDER BY id DESC
");



$cnt = [];
foreach (['pending','reviewing','resolved'] as $status_key) {
    $count_q          = $conn->query("SELECT COUNT(*) AS c FROM damage_declarations
                                       WHERE reporter_id = $uid AND status = '$status_key'");
    $cnt[$status_key] = $count_q->fetch_assoc()['c'];
}
$cnt['total'] = $cnt['pending'] + $cnt['reviewing'] + $cnt['resolved'];


$unread_q    = $conn->query("SELECT COUNT(*) AS cnt FROM messages
                              WHERE receiver_id = $uid AND is_read = 0");
$unread_msgs = $unread_q->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - My Reports</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/creport.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><img src="../../assets/images/logo.png"></div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="role-badge" style="background:#6366f1;color:#fff;">CLIENT</div>

    <div class="sidebar-nav">
        <a href="dashboard.php"           class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
        <a href="../Tools/tools.php"      class="nav-link"><i class="fa fa-wrench"></i> Tools</a>
        <a href="ToolSpecification.php"   class="nav-link"><i class="fa fa-plus"></i> Add Tool</a>
        <a href="../Tools/categories.php" class="nav-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg> Categories
        </a>
        <a href="my-reservations.php" class="nav-link"><i class="fa fa-calendar-check"></i> My Reservations</a>
        <a href="my-reports.php"      class="nav-link active"><i class="fa fa-triangle-exclamation"></i> My Reports</a>
        <a href="chat.php"            class="nav-link">
            <i class="fa fa-comments"></i> Chat
            <?php if ($unread_msgs > 0): ?>
                <span class="nav-count"><?= $unread_msgs ?></span>
            <?php endif; ?>
        </a>
        <a href="ToolCompatibility.php"  class="nav-link"><i class="fa fa-circle-check"></i> Compatibility Checker</a>
        <a href="DamageDeclaration.php"  class="nav-link"><i class="fa fa-triangle-exclamation"></i> Damage Report</a>
    </div>
</div>

<!-- RIGHT -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <button class="hamburger"><i class="fa fa-bars"></i></button>
        <div class="topbar-title">My Damage Reports</div>
        <div class="topbar-right">
            <button class="avatar-btn"><?= htmlspecialchars($_SESSION['name']) ?></button>
            <a href="../Auth/login.php" class="icon-btn"><i class="fa fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="main-content">

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" style="margin-bottom:20px;">
            <i class="fa fa-circle-check"></i> Report submitted successfully.
        </div>
        <?php endif; ?>

        <!-- STATS -->
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
                <div class="num" style="color:#6366f1;"><?= $cnt['reviewing'] ?></div>
                <div class="lbl">Reviewing</div>
            </div>
            <div class="stat-mini-card">
                <div class="num" style="color:#2dbe6c;"><?= $cnt['resolved'] ?></div>
                <div class="lbl">Resolved</div>
            </div>
        </div>

        <!-- FILTERS -->
        <form method="GET" class="filters">
            <input type="text"
                   name="search"
                   placeholder="Search tool or reference..."
                   value="<?= htmlspecialchars($search_raw) ?>">

            <select name="status">
                <option value="">All Status</option>
                <option value="pending"   <?= $status_filter_raw === 'pending'   ? 'selected' : '' ?>>Pending</option>
                <option value="reviewing" <?= $status_filter_raw === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
                <option value="resolved"  <?= $status_filter_raw === 'resolved'  ? 'selected' : '' ?>>Resolved</option>
            </select>

            <button class="btn-sm"><i class="fa fa-search"></i> Filter</button>
            <a href="my-reports.php" class="btn-sm" style="text-decoration:none;">Reset</a>
        </form>

        <!-- TABLE -->
        <div class="card">
            <?php if (!$reports_q || $reports_q->num_rows === 0): ?>
            <div class="empty-state">
                <i class="fa fa-file-circle-xmark"></i>
                <?= $status_filter_raw !== '' || $search_raw !== ''
                    ? 'No reports match your filter.'
                    : 'No reports found.' ?>
            </div>
            <?php else: ?>
            <table class="res-table">
                <thead>
                    <tr>
                        <th>#REF</th>
                        <th>Tool</th>
                        <th>Damage Type</th>
                        <th>Severity</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Evidence</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $reports_q->fetch_assoc()):
                    $pill = match($row['status']) {
                        'reviewing' => 'pill-reviewing',
                        'resolved'  => 'pill-resolved',
                        default     => 'pill-pending'
                    };
                    $photos = json_decode($row['photos'], true);
                ?>
                <tr>
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($row['reference_no']) ?></div>
                    </td>
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($row['tool_name']) ?></div>
                        <div style="font-size:.75rem;color:var(--text-muted);">
                            Reservation #<?= htmlspecialchars($row['reservation_id']) ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($row['damage_type']) ?></td>
                    <td><?= ucfirst($row['severity']) ?></td>
                    <td><?= date('M d, Y', strtotime($row['damage_date'])) ?></td>
                    <td>
                        <span class="status-pill <?= $pill ?>">
                            <i class="fa fa-circle" style="font-size:.45rem;"></i>
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!empty($photos[0])): ?>
                            <a href="<?= htmlspecialchars($photos[0]) ?>" target="_blank" class="btn-sm">
                                <i class="fa fa-image"></i> View
                            </a>
                        <?php else: ?>
                            <span style="color:var(--text-muted);">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
document.querySelector('.hamburger')?.addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('open');
});
</script>
</body>
</html>