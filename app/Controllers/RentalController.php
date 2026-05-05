<?php
// 1. تفعيل الأخطاء كاملة
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. المسارات
require_once __DIR__ . "/../Models/Rental.php";
require_once __DIR__ . "/../../Core/database.php";

// 3. تشغيل فانكشن 19
Rental::EscalateLateReturns();

// 4. جلب البيانات (Query معدلة لتجنب الأخطاء)
$db = Database::getInstance()->getConnection();

// جربنا أكتر من اسم عمود (name أو tool_name) عشان نضمن إنها تشتغل عندك
// جلب البيانات (تعديل أسماء الأعمدة بناءً على قاعدة بياناتك)
$query = "SELECT r.*, 
          u.name as user_name, 
          t.tool_name as tool_name 
          FROM rentals r
          LEFT JOIN users u ON r.user_id = u.user_id
          LEFT JOIN tools t ON r.tool_id = t.tool_id";

$result = mysqli_query($db, $query);

// كود لكشف الخطأ لو الـ Query فشلت
if (!$result) {
    die("SQL Error: " . mysqli_error($db));
}

$rentals = mysqli_fetch_all($result, MYSQLI_ASSOC);

// 5. استدعاء الـ View
include __DIR__ . "/../Views/Admin/rentals.php";
?>