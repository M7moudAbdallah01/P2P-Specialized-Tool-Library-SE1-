<?php
/* =========================================================
   1) SESSION + AUTH
========================================================= */
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/login.php");
    exit();
}

/* =========================================================
   2) DATABASE
========================================================= */
$db   = Database::getInstance();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];
$uid  = intval($_SESSION['user_id']);
$r = $conn->query("SELECT COUNT(*) AS cnt FROM messages WHERE receiver_id = $uid AND is_read = 0");
$unread_msgs = $r->fetch_assoc()['cnt'];
/* =========================================================
   3) FETCH MY RESERVATIONS
========================================================= */
$reservations_q = $conn->query("
    SELECT
        r.*,
        t.name        AS tool_name,
        t.base_price,
        t.image_path
    FROM reservations r
    LEFT JOIN tools t ON r.tool_id = t.tool_id
    WHERE r.user_id = $user_id
    ORDER BY r.reservation_id DESC
");

/* =========================================================
   4) COUNTS
========================================================= */
$cnt = [];

foreach (['pending','confirmed','cancelled','completed'] as $s) {
    $res = $conn->query("
        SELECT COUNT(*) AS c
        FROM reservations
        WHERE user_id = $user_id
        AND status = '$s'
    ");

    $cnt[$s] = $res->fetch_assoc()['c'];
}

$cnt['total'] = array_sum($cnt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - My Reservations</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/my-res.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="role-badge" style="background:#6366f1;color:#fff;">
        CLIENT
    </div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link">
            <i class="fa fa-gauge"></i> Dashboard
        </a>
        <a href="../Tools/tools.php" class="nav-link"><i class="fa fa-wrench"></i> Tools</a>
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

        <a href="my-reservations.php" class="nav-link active">
            <i class="fa fa-calendar-check"></i> My Reservations
        </a>


        <a href="my-reports.php" class="nav-link">
            <i class="fa fa-calendar-check"></i> My Reports
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
    </div>
</div>

<!-- RIGHT SIDE -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <button class="hamburger">
            <i class="fa fa-bars"></i>
        </button>

        <div class="topbar-title">
            My Reservations
        </div>

        <div class="topbar-right">

            <button class="avatar-btn" style="background:#6366f1;color:#fff;">
                <?= htmlspecialchars($_SESSION['name']) ?>
            </button>

            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>

        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content">

        <!-- STATS -->
        <div class="stat-mini">

            <div class="stat-mini-card">
                <div class="num"><?= $cnt['total'] ?></div>
                <div class="lbl">Total</div>
            </div>

            <div class="stat-mini-card">
                <div class="num" style="color:#eab308;">
                    <?= $cnt['pending'] ?>
                </div>
                <div class="lbl">Pending</div>
            </div>

            <div class="stat-mini-card">
                <div class="num" style="color:#2dbe6c;">
                    <?= $cnt['confirmed'] ?>
                </div>
                <div class="lbl">Confirmed</div>
            </div>

            <div class="stat-mini-card">
                <div class="num" style="color:#6366f1;">
                    <?= $cnt['completed'] ?>
                </div>
                <div class="lbl">Completed</div>
            </div>

            <div class="stat-mini-card">
                <div class="num" style="color:#6b7280;">
                    <?= $cnt['cancelled'] ?>
                </div>
                <div class="lbl">Cancelled</div>
            </div>

        </div>

        <!-- TABLE -->
        <div class="card">

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
                        <th>Tool</th>
                        <th>Dates</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php while($res = $reservations_q->fetch_assoc()): ?>

                    <?php
                    $days = max(
                        1,
                        (strtotime($res['end_date']) - strtotime($res['start_date'])) / 86400
                    );

                    $total = $days * ($res['base_price'] ?? 0);

                    $pill = match($res['status']) {
                        'confirmed' => 'pill-confirmed',
                        'cancelled' => 'pill-cancelled',
                        'completed' => 'pill-completed',
                        default     => 'pill-pending'
                    };
                    ?>

                    <tr>

                        <td style="color:var(--text-muted);">
                            #<?= $res['reservation_id'] ?>
                        </td>

                        <td>

                            <div class="tool-box">

                                <?php if (!empty($res['image_path'])): ?>
                                    <img src="../Client/<?= htmlspecialchars($res['image_path']) ?>">
                                <?php endif; ?>

                                <div>
                                    <div style="font-weight:600;">
                                        <?= htmlspecialchars($res['tool_name']) ?>
                                    </div>

                                    <div style="font-size:.75rem;color:var(--text-muted);">
                                        $<?= number_format($res['base_price'],2) ?>/day
                                    </div>
                                </div>

                            </div>

                        </td>

                        <td>

                            <div style="font-size:.82rem;">
                                <i class="fa fa-calendar-days" style="color:var(--red);margin-right:4px;"></i>

                                <?= date('M d, Y', strtotime($res['start_date'])) ?>

                                <span style="color:var(--text-muted);">
                                    →
                                </span>

                                <?= date('M d, Y', strtotime($res['end_date'])) ?>
                            </div>

                            <div style="font-size:.75rem;color:var(--text-muted);margin-top:4px;">
                                <?= $days ?> day(s)
                            </div>

                        </td>

                        <td style="font-weight:600;">
                            $<?= number_format($total, 2) ?>
                        </td>

                        <td>

                            <span class="status-pill <?= $pill ?>">

                                <i class="fa fa-circle" style="font-size:.45rem;"></i>

                                <?= ucfirst($res['status']) ?>

                            </span>

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