<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../Models/Rental.php";
require_once __DIR__ . "/../../Core/database.php";

Rental::EscalateLateReturns();

$db = Database::getInstance()->getConnection();

$query = "SELECT r.*, 
          u.name as user_name, 
          t.tool_name as tool_name 
          FROM rentals r
          LEFT JOIN users u ON r.user_id = u.user_id
          LEFT JOIN tools t ON r.tool_id = t.tool_id";

$result = mysqli_query($db, $query);

if (!$result) {
    die("SQL Error: " . mysqli_error($db));
}

$rentals = mysqli_fetch_all($result, MYSQLI_ASSOC);

include __DIR__ . "/../Views/Admin/rentals.php";
?>