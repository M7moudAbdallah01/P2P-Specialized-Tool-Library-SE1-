<?php

session_start();
require_once __DIR__ . "/../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}
 

$db   = Database::getInstance();
$conn = $db->getConnection();

if (isset($_POST['add_campaign'])) {
    
    if (!isset($conn)) {
        die("Error: Connection variable \$conn is not defined in database.php");
    }

    $code = mysqli_real_escape_string($conn, $_POST['code']);
    $discount = (int)$_POST['discount_percentage'];
    $cat_id = (int)$_POST['category_id'];
    $expiry = mysqli_real_escape_string($conn, $_POST['expiry_date']);

    $query = "INSERT INTO coupons (code, discount_percentage, category_id, expiry_date, status) 
              VALUES ('$code', '$discount', '$cat_id', '$expiry', 1)";

    if (mysqli_query($conn, $query)) {
        echo "<h2 style='color:green;'>Success!</h2>";
        echo "Campaign <b>$code</b> added successfully.";
        echo "<br><a href='../views/Admin/dashboard.php'>Back to Dashboard</a>";
    } else {
        echo "SQL Error: " . mysqli_error($conn);
    }
}
?>