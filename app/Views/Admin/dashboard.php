<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}
 
$db = Database::getInstance();
$conn = $db->getConnection();


