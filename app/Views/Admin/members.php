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

    header("Location: member-profile.php?msg=deleted");
    exit();
}

/* =========================================================
   4) CHANGE ROLE
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {

    $uid  = intval($_POST['user_id']);
    $role = $conn->real_escape_string($_POST['role']);

    $conn->query("
        UPDATE users
        SET role = '$role'
        WHERE user_id = $uid
    ");

    header("Location: member-profile.php?msg=updated");
    exit();
}

/* =========================================================
   5) UPDATE TIER + TRUST
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {

    $uid   = intval($_POST['user_id']);
    $tier  = $conn->real_escape_string($_POST['membership_tier']);
    $trust = floatval($_POST['trust_score']);

    $conn->query("
        UPDATE users
        SET membership_tier = '$tier',
            trust_score     = $trust
        WHERE user_id = $uid
    ");

    header("Location: member-profile.php?msg=updated");
    exit();
}

/* =========================================================
   6) FILTERS
========================================================= */
$search      = trim($_GET['search'] ?? '');
$role_filter = $_GET['role'] ?? '';

$where = "WHERE role IN ('technical','client')";

/* Search */
if ($search !== '') {

    $s = $conn->real_escape_string($search);

    $where .= "
        AND (
            name LIKE '%$s%'
            OR email LIKE '%$s%'
        )
    ";
}

/* Role Filter */
if (in_array($role_filter, ['technical', 'client'])) {

    $rf = $conn->real_escape_string($role_filter);
    $where .= " AND role = '$rf'";
}

/* =========================================================
   7) FETCH USERS
========================================================= */
$users = $conn->query("
    SELECT *
    FROM users
    $where
    ORDER BY user_id DESC
");

/* =========================================================
   8) COUNTS
========================================================= */
$counts = [];

foreach (['technical', 'client'] as $role) {

    $res = $conn->query("
        SELECT COUNT(*) AS c
        FROM users
        WHERE role = '$role'
    ");

    $counts[$role] = $res->fetch_assoc()['c'];
}

$total_members = $counts['technical'] + $counts['client'];
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Tool Hub - Members</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
            <img src="../../assets/images/logo.png" alt="Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="sidebar-nav">

        <a href="dashboard.php" class="nav-link">
            <i class="fa fa-gauge"></i> Dashboard
        </a>

        <a href="tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
        </a>

        <a href="member-profile.php" class="nav-link active">
            <i class="fa fa-users"></i> Members
        </a>

        <a href="reservations.php" class="nav-link">
            <i class="fa fa-calendar"></i> Reservations
        </a>

        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
        </a>

    </div>

    <div class="role-badge role-admin">ADMIN</div>

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
                <?= htmlspecialchars($_SESSION['name']) ?>
                <span>Admin</span>
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

                <?=
                $_GET['msg'] === 'deleted'
                ? 'User deleted successfully.'
                : 'Updated successfully.'
                ?>

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

        </div>

        <!-- CARD -->
        <div class="card">

            <h2 style="margin-bottom:20px;">
                Members (<?= $users->num_rows ?>)
            </h2>

            <!-- FILTER -->
            <form method="GET" class="filters">

                <input
                    type="text"
                    name="search"
                    placeholder="Search by name or email..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <select name="role">

                    <option value="">All Roles</option>

                    <option value="client"
                        <?= $role_filter === 'client' ? 'selected' : '' ?>>
                        Client
                    </option>

                    <option value="technical"
                        <?= $role_filter === 'technical' ? 'selected' : '' ?>>
                        Technical
                    </option>

                </select>

                <button class="btn btn-red">
                    <i class="fa fa-search"></i> Filter
                </button>

                <a href="member-profile.php" class="btn btn-ghost">
                    Reset
                </a>

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

                    <td>
                        <strong>
                            <?= htmlspecialchars($u['name']) ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($u['email']) ?>
                    </td>

                    <!-- ROLE -->
                    <td>

                        <form method="POST">

                            <input type="hidden"
                                   name="user_id"
                                   value="<?= $u['user_id'] ?>">

                            <input type="hidden"
                                   name="change_role"
                                   value="1">

                            <select
                                name="role"
                                onchange="this.form.submit()"
                            >

                                <option value="client"
                                <?= $u['role']=='client'?'selected':'' ?>>
                                    Client
                                </option>

                                <option value="technical"
                                <?= $u['role']=='technical'?'selected':'' ?>>
                                    Technical
                                </option>

                            </select>

                        </form>

                    </td>

                    <!-- TIER + TRUST -->
                    <td colspan="2">

                        <form method="POST"
                              style="display:flex;gap:10px;align-items:center;">

                            <input type="hidden"
                                   name="user_id"
                                   value="<?= $u['user_id'] ?>">

                            <input type="hidden"
                                   name="update_user"
                                   value="1">

                            <!-- Tier -->
                            <select name="membership_tier">

                                <option value="basic"
                                <?= $u['membership_tier']=='basic'?'selected':'' ?>>
                                    Basic
                                </option>

                                <option value="premium"
                                <?= $u['membership_tier']=='premium'?'selected':'' ?>>
                                    Premium
                                </option>

                                <option value="vip"
                                <?= $u['membership_tier']=='vip'?'selected':'' ?>>
                                    VIP
                                </option>

                            </select>

                            <!-- Trust -->
                            <input type="number"
                                   name="trust_score"
                                   min="0"
                                   max="1"
                                   step="0.01"
                                   value="<?= $u['trust_score'] ?>"
                                   style="width:80px; margin-left:120px;">

                            <button class="btn btn-red btn-sm">
                                Save
                            </button>

                        </form>

                    </td>

                    <!-- DELETE -->
                    <td>

                        <a
                        href="?delete=<?= $u['user_id'] ?>"
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

    </div>

</div>

</body>
</html>