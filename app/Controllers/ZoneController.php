<?php
// استدعاء الموديل باستخدام المسار النسبي المضمون
require_once __DIR__ . "/../Models/Zone.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zone'])) {
    $zoneName = $_POST['zone'];

    // نداء دالة الإضافة من الموديل
    Zone::add($zoneName);

    // الرجوع التلقائي لصفحة العرض بعد النجاح
    header("Location: ../Views/Admin/zones.php");
    exit();
} else {
    // حماية: لو حد حاول يفتح الملف مباشرة يرجعه لصفحة الزونز
    header("Location: ../Views/Admin/zones.php");
    exit();
}