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
   2) DATABASE CONNECTION
========================================================= */
$db   = Database::getInstance();
$conn = $db->getConnection();

/* =========================================================
   3) DELETE USER
========================================================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id !== $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE user_id = $id");
    }
    header("Location: members.php?msg=deleted");
    exit();
}

/* =========================================================
   4) CHANGE STATUS (suspend / blacklist / activate)
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $uid    = intval($_POST['user_id']);
    $action = $conn->real_escape_string($_POST['action']);

    $status_map = [
        'activate'  => 'active',
        'suspend'   => 'suspended',
        'blacklist' => 'blacklisted',
    ];

    if (isset($status_map[$action])) {
        $new_status = $status_map[$action];
        $conn->query("UPDATE users SET status = '$new_status' WHERE user_id = $uid");
    }

    header("Location: members.php?msg=updated");
    exit();
}

/* =========================================================
   5) CHANGE ROLE
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $uid  = intval($_POST['user_id']);
    $role = $conn->real_escape_string($_POST['role']);
    $conn->query("UPDATE users SET role = '$role' WHERE user_id = $uid");
    header("Location: members.php?msg=updated");
    exit();
}

/* =========================================================
   6) UPDATE TIER + TRUST
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $uid   = intval($_POST['user_id']);
    $tier  = $conn->real_escape_string($_POST['membership_tier']);
    $trust = floatval($_POST['trust_score']);
    $conn->query("UPDATE users SET membership_tier = '$tier', trust_score = $trust WHERE user_id = $uid");
    header("Location: members.php?msg=updated");
    exit();
}

/* =========================================================
   7) FILTERS
========================================================= */
$search      = trim($_GET['search'] ?? '');
$role_filter = $_GET['role'] ?? '';
$tab         = $_GET['tab'] ?? 'members'; // 'members' | 'status'

$where = "WHERE role IN ('technical','client')";

if ($search !== '') {
    $s      = $conn->real_escape_string($search);
    $where .= " AND (name LIKE '%$s%' OR email LIKE '%$s%')";
}

if (in_array($role_filter, ['technical', 'client'])) {
    $rf     = $conn->real_escape_string($role_filter);
    $where .= " AND role = '$rf'";
}

/* =========================================================
   8) FETCH USERS
========================================================= */
$users = $conn->query("SELECT * FROM users $where ORDER BY user_id DESC");

/* =========================================================
   9) COUNTS
========================================================= */
$counts = [];
foreach (['technical', 'client'] as $r) {
    $res          = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = '$r'");
    $counts[$r]   = $res->fetch_assoc()['c'];
}

$r2 = $conn->query("SELECT COUNT(*) AS c FROM users WHERE status = 'suspended'");
$counts['suspended']   = $r2->fetch_assoc()['c'];

$r3 = $conn->query("SELECT COUNT(*) AS c FROM users WHERE status = 'blacklisted'");
$counts['blacklisted'] = $r3->fetch_assoc()['c'];

$total_members = $counts['technical'] + $counts['client'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Members</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">

</head>
<body>

<!-- =========================================================
   SIDEBAR
========================================================= -->
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
        <a href="tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
        </a>
        <a href="categories.php" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg> Categories
        </a>
        <a href="members.php" class="nav-link active">
            <i class="fa fa-users"></i> Members
        </a>
        <a href="reservations.php" class="nav-link">
            <i class="fa fa-calendar"></i> Reservations
        </a>
        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
        </a>
        <a class="nav-link" href="#">
            <i class="fa fa-scale-balanced"></i> Disputes &amp; Reports
        </a>
    </div>
</div>

<!-- =========================================================
   RIGHT SIDE
========================================================= -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-title">Members</div>
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
                <?= $_GET['msg'] === 'deleted' ? 'User deleted successfully.' : 'Updated successfully.' ?>
            </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="stat-mini">
            <div class="stat-mini-card">
                <div class="num"><?= $total_members ?></div>
                <div class="lbl">Total Members</div>
            </div>
            <div class="stat-mini-card">
                <div class="num"><?= $counts['client'] ?></div>
                <div class="lbl">Clients</div>
            </div>
            <div class="stat-mini-card">
                <div class="num"><?= $counts['technical'] ?></div>
                <div class="lbl">Technical</div>
            </div>
            <div class="stat-mini-card">
                <div class="num"><?= $counts['suspended'] ?></div>
                <div class="lbl">Suspended</div>
            </div>
            <div class="stat-mini-card">
                <div class="num"><?= $counts['blacklisted'] ?></div>
                <div class="lbl">Blacklisted</div>
            </div>
        </div>

        <!-- TABS -->
        <div class="tabs">
            <a href="?tab=members<?= $search ? '&search='.urlencode($search) : '' ?><?= $role_filter ? '&role='.$role_filter : '' ?>"
               class="tab-btn <?= $tab === 'members' ? 'active' : '' ?>">
                <i class="fa fa-users"></i> Members List
            </a>
            <a href="?tab=status"
               class="tab-btn <?= $tab === 'status' ? 'active' : '' ?>">
                <i class="fa fa-shield-halved"></i> Suspension &amp; Blacklist
            </a>
        </div>

        <!-- ================================================
             TAB 1 — MEMBERS LIST
        ================================================ -->
        <?php if ($tab === 'members'): ?>

        <div class="card">

            <!-- FILTER -->
            <form method="GET" class="filters">
                <input type="hidden" name="tab" value="members">
                <input type="text" name="search" placeholder="Search by name or email..."
                       value="<?= htmlspecialchars($search) ?>">
                <select name="role">
                    <option value="">All Roles</option>
                    <option value="client"    <?= $role_filter === 'client'    ? 'selected' : '' ?>>Client</option>
                    <option value="technical" <?= $role_filter === 'technical' ? 'selected' : '' ?>>Technical</option>
                </select>
                <button class="btn btn-red"><i class="fa fa-search"></i> Filter</button>
                <a href="members.php?tab=members" class="btn btn-ghost">Reset</a>
            </form>

            <!-- TABLE -->
            <div style="overflow-x:auto; margin-top:20px;">
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Tier</th>
                    <th>Trust</th>
                    <th>Delete</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['user_id'] ?></td>
                    <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>

                    <!-- ROLE -->
                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id"     value="<?= $u['user_id'] ?>">
                            <input type="hidden" name="change_role" value="1">
                            <select name="role" onchange="this.form.submit()">
                                <option value="client"    <?= $u['role'] === 'client'    ? 'selected' : '' ?>>Client</option>
                                <option value="technical" <?= $u['role'] === 'technical' ? 'selected' : '' ?>>Technical</option>
                            </select>
                        </form>
                    </td>

                    <!-- TIER + TRUST -->
                    <td colspan="2">
                        <form method="POST" style="display:flex;gap:10px;align-items:center;">
                            <input type="hidden" name="user_id"     value="<?= $u['user_id'] ?>">
                            <input type="hidden" name="update_user" value="1">
                            <select name="membership_tier">
                                <option value="basic"   <?= $u['membership_tier'] === 'basic'   ? 'selected' : '' ?>>Basic</option>
                                <option value="premium" <?= $u['membership_tier'] === 'premium' ? 'selected' : '' ?>>Premium</option>
                                <option value="vip"     <?= $u['membership_tier'] === 'vip'     ? 'selected' : '' ?>>VIP</option>
                            </select>
                            <input type="number" name="trust_score" min="0" max="1" step="0.01"
                                   value="<?= $u['trust_score'] ?>" style="width:80px; margin-left: 80px;">
                            <button class="btn btn-red btn-sm">Save</button>
                        </form>
                    </td>

                    <!-- DELETE -->
                    <td>
                        <a href="?delete=<?= $u['user_id'] ?>&tab=members"
                           onclick="return confirm('Delete user?')"
                           class="btn btn-ghost btn-sm">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            </div>

        </div>

        <?php endif; ?>

        <!-- ================================================
             TAB 2 — SUSPENSION & BLACKLIST
        ================================================ -->
        <?php if ($tab === 'status'): ?>

        <?php
        // Re-fetch all non-admin users for the status tab (no role filter applied)
        $all_users = $conn->query("
            SELECT * FROM users
            WHERE role IN ('technical','client')
            ORDER BY user_id DESC
        ");
        ?>

        <div class="card">
            <div style="overflow-x:auto;">
            <table>
                <thead>
                <tr>
                    <th>User Information</th>
                    <th>Role</th>
                    <th>Current Status</th>
                    <th style="text-align:right;">Management Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($all_users->num_rows === 0): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;padding:40px;color:#9ca3af;">
                            No users found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($u = $all_users->fetch_assoc()):
                        $status    = $u['status'] ?? 'active';
                        $pill_cls  = match($status) {
                            'suspended'   => 'sp-suspended',
                            'blacklisted' => 'sp-blacklisted',
                            default       => 'sp-active'
                        };
                        $dot_icon  = match($status) {
                            'suspended'   => '⚠',
                            'blacklisted' => '✕',
                            default       => '●'
                        };
                    ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($u['name']) ?></strong><br>
                            <small style="color:#9ca3af;"><?= htmlspecialchars($u['email']) ?></small>
                        </td>
                        <td>
                            <span style="text-transform:capitalize;"><?= $u['role'] ?></span>
                        </td>
                        <td>
                            <span class="status-pill <?= $pill_cls ?>">
                                <?= $dot_icon ?> <?= strtoupper($status) ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <form method="POST" style="display:inline-flex;gap:8px;justify-content:flex-end;">
                                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">

                                <?php if ($status !== 'active'): ?>
                                    <button type="submit" name="action" value="activate" class="btn-green">
                                        <i class="fa fa-circle-check"></i> Reactivate
                                    </button>
                                <?php endif; ?>

                                <?php if ($status !== 'suspended'): ?>
                                    <button type="submit" name="action" value="suspend" class="btn btn-ghost btn-sm">
                                        <i class="fa fa-pause"></i> Suspend
                                    </button>
                                <?php endif; ?>

                                <?php if ($status !== 'blacklisted'): ?>
                                    <button type="submit" name="action" value="blacklist" class="btn btn-red btn-sm">
                                        <i class="fa fa-ban"></i> Blacklist
                                    </button>
                                <?php endif; ?>

                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <?php endif; ?>

    </div><!-- /.main-content -->
</div><!-- /.layout-right -->

</body>
</html>