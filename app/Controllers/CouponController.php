<?php
require_once   "../../Core/database.php";
$db = Database::getInstance()->getConnection();

if (isset($_POST['add_campaign'])) {
    
    // 3. التأكد من أن المتغير $conn معرف في ملف database.php
    if (!isset($conn)) {
        die("Error: Connection variable \$conn is not defined in database.php");
    }

    // جلب البيانات من الـ Form (استخدمنا الأسماء اللي في الفورم بتاعك)
    $code = mysqli_real_escape_string($conn, $_POST['code']);
    $discount = (int)$_POST['discount_percentage'];
    $cat_id = (int)$_POST['category_id'];
    $expiry = mysqli_real_escape_string($conn, $_POST['expiry_date']);

    // 4. جملة الـ SQL مطابقة لأسماء الأعمدة في صورتك ****
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