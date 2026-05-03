<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}
 
$db = Database::getInstance();
$conn = $db->getConnection();


$db = Database::getInstance();
$conn = $db->getConnection();
 
$r = $conn->query("SELECT COUNT(*) AS c FROM users");
$total_users = $r->fetch_assoc()['c'];
 
$r = $conn->query("SELECT COUNT(*) AS c FROM tools");
$total_tools = $r->fetch_assoc()['c'];
 
$r = $conn->query("SELECT COUNT(*) AS c FROM reservation WHERE status = 'pending'");
$pending = $r->fetch_assoc()['c'];
 
$r = $conn->query("SELECT COUNT(*) AS c FROM dispute WHERE status = 'open'");
$disputes = $r->fetch_assoc()['c'];

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tool Hub - Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
</head>
<body>
 
<nav class="navbar">
    <span class="brand">⚙ Tool Hub</span>
    <a href="dashboard.php" class="active"><i class="fa fa-gauge-high"></i> Dashboard</a>
    <a href="tools.php"><i class="fa fa-wrench"></i> Tools</a>
    <a href="member-profile.php"><i class="fa fa-users"></i> Members</a>
    <a href="reservations.php"><i class="fa fa-calendar"></i> Reservations</a>
    <a href="chat.php"><i class="fa fa-comments"></i> Chat</a>
    <div class="spacer"></div>
    <a href="../Auth/login.php" class="logout"><i class="fa fa-right-from-bracket"></i> Logout</a>
</nav>
 
<div class="container">
 
    <div class="welcome">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h1>
        <p>Here's a quick overview — <?= date('l, d M Y') ?></p>
    </div>
 
    <div class="stats">
        <div class="stat-card">
            <div class="icon">👥</div>
            <div class="num"><?= $total_users ?></div>
            <div class="label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="icon">🔧</div>
            <div class="num"><?= $total_tools ?></div>
            <div class="label">Listed Tools</div>
        </div>
        <div class="stat-card">
            <div class="icon">⏳</div>
            <div class="num"><?= $pending ?></div>
            <div class="label">Pending Reservations</div>
        </div>
        <div class="stat-card">
            <div class="icon">⚠️</div>
            <div class="num"><?= $disputes ?></div>
            <div class="label">Open Disputes</div>
        </div>
    </div>
 
    <div class="links">
        <a class="link-card" href="tools.php"><i class="fa fa-wrench"></i> Manage Tools</a>
        <a class="link-card" href="member-profile.php"><i class="fa fa-users"></i> Manage Members</a>
        <a class="link-card" href="reservations.php"><i class="fa fa-calendar-check"></i> Reservations</a>
        <a class="link-card" href="tool_Details.php"><i class="fa fa-magnifying-glass"></i> Tool Details</a>
        <a class="link-card" href="chat.php"><i class="fa fa-comments"></i> Messages</a>
        <a class="link-card" href="#"><i class="fa fa-scale-balanced"></i> Disputes</a>
    </div>
 
</div>
</body>
</html>
