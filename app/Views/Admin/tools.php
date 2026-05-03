<?php
/* =========================================================
   1) SESSION + AUTH CHECK
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
$db = Database::getInstance();
$conn = $db->getConnection();

/* =========================================================
   3) ACTIONS (DELETE / TOGGLE)
========================================================= */

// Delete tool
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM tools WHERE tool_id = $id");

    header("Location: tools.php?msg=deleted");
    exit();
}

// Toggle availability
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE tools SET availability = NOT availability WHERE tool_id = $id");

    header("Location: tools.php");
    exit();
}

/* =========================================================
   4) FILTERS (SEARCH + CATEGORY)
========================================================= */
$search = trim($_GET['search'] ?? '');
$cat_filter = intval($_GET['category'] ?? 0);

$where = "WHERE 1=1";

// search
if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $where .= " AND (t.name LIKE '%$s%' OR u.name LIKE '%$s%')";
}

// category
if ($cat_filter > 0) {
    $where .= " AND t.category_id = $cat_filter";
}

/* =========================================================
   5) FETCH DATA
========================================================= */

// tools
$tools = $conn->query("
    SELECT t.*, u.name AS owner_name, c.name AS category_name
    FROM tools t
    JOIN users u ON t.owner_id = u.user_id
    JOIN category c ON t.category_id = c.category_id
    $where
    ORDER BY t.created_at DESC
");

// categories
$categories = $conn->query("SELECT * FROM category ORDER BY name");
?>





<!DOCTYPE html>
<html lang="en">
<head>

<!-- =========================================================
   6) HEAD (META + CSS)
========================================================= -->
<meta charset="UTF-8">
<title>Tool Hub - Tools</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Main CSS -->
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">

</head>
<body>
<!-- =========================================================
   7) SIDEBAR
========================================================= -->
<div class="sidebar">

    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
        <a href="tools.php" class="nav-link active"><i class="fa fa-wrench"></i> Tools</a>
        <a href="members.php" class="nav-link"><i class="fa fa-users"></i> Members</a>
        <a href="reservations.php" class="nav-link"><i class="fa fa-calendar"></i> Reservations</a>
        <a href="chat.php" class="nav-link"><i class="fa fa-comments"></i> Chat</a>
    </div>

    <div class="role-badge role-admin">ADMIN</div>
</div>

<!-- =========================================================
   8) TOPBAR
========================================================= -->
<div class="layout-right">

    <div class="topbar">
        <div class="topbar-title">Tools</div>

        <div class="topbar-right">
            <button class="avatar-btn admin-avatar">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Admin</span>
            </button>

            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>
        </div>
    </div>


    <!-- =========================================================
   9) MAIN CONTENT
========================================================= -->
<div class="main-content">

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success">
        Tool deleted successfully.
    </div>
<?php endif; ?>


<!-- FILTERS -->
<form method="GET" class="filters">

    <input type="text"
           name="search"
           placeholder="Search..."
           value="<?= htmlspecialchars($search) ?>">

    <select name="category">
        <option value="0">All Categories</option>

        <?php while($cat = $categories->fetch_assoc()): ?>
            <option value="<?= $cat['category_id'] ?>"
                <?= $cat_filter == $cat['category_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button class="btn btn-red">Search</button>
    <a href="tools.php" class="btn btn-ghost">Reset</a>

</form>


<!-- TABLE -->
<table>

<thead>
<tr>
    <th>#</th>
    <th>Tool</th>
    <th>Category</th>
    <th>Client</th>
    <th>Price</th>
    <th>Status</th>
    <th>Available</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php while($t = $tools->fetch_assoc()): ?>
<tr>

<td><?= $t['tool_id'] ?></td>

<td>
    <strong><?= htmlspecialchars($t['name']) ?></strong><br>
    <small><?= substr($t['description'],0,40) ?>...</small>
</td>

<td><?= $t['category_name'] ?></td>
<td><?= $t['owner_name'] ?></td>

<td>$<?= $t['base_price'] ?></td>

<td><?= $t['state'] ?></td>

<td>
    <?= $t['availability'] ? 'Yes' : 'No' ?>
</td>

<td>
    <a href="tool_Details.php?id=<?= $t['tool_id'] ?>">View</a>
    <a href="?toggle=<?= $t['tool_id'] ?>">Toggle</a>
    <a href="?delete=<?= $t['tool_id'] ?>">Delete</a>
</td>

</tr>
<?php endwhile; ?>

</tbody>
</table>



</body>
</html>