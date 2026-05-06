<?php
session_start();
require_once __DIR__ . "/../../Core/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Views/Auth/login.php");
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

// get tool id
$tool_id = intval($_GET['id'] ?? 0);

if ($tool_id <= 0) {
    die("Invalid Tool ID");
}

/* ===============================
   1) TOOL MAIN DATA
================================ */
$tool = $conn->query("
    SELECT t.*, u.name AS owner_name, c.name AS category_name
    FROM tools t
    JOIN users u ON t.owner_id = u.user_id
    JOIN category c ON t.category_id = c.category_id
    WHERE t.tool_id = $tool_id
")->fetch_assoc();

/* ===============================
   2) CERTIFICATIONS
================================ */
$certifications = $conn->query("
    SELECT *
    FROM certifications
    WHERE tool_id = $tool_id
    ORDER BY issue_date DESC
");

/* ===============================
   3) MAINTENANCE LOGS
================================ */
$maintenance = $conn->query("
    SELECT *
    FROM maintenance_logs
    WHERE tool_id = $tool_id
    ORDER BY date DESC
");

/* ===============================
   4) BATTERY (LATEST ONLY)
================================ */
$battery = $conn->query("
    SELECT *
    FROM battery_logs
    WHERE tool_id = $tool_id
    ORDER BY last_checked DESC
    LIMIT 1
")->fetch_assoc();