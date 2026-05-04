<?php
require_once "../Models/Zone.php";

// 1. التعامل مع طلبات الـ POST (إضافة منطقة)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = $_POST['zone_name'];
    if (Zone::addZone($name)) {
        header("Location: ../Views/Admin/zones.php?success=1");
        exit();
    }
}

// 2. التعامل مع طلبات الـ GET (حذف منطقة)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']); // تأمين الـ ID
    
    if (Zone::deleteZone($id)) {
        // السطر ده هو اللي هيحل مشكلة الصفحة البيضاء ويرجعك للجدول
        header("Location: ../Views/Admin/zones.php?deleted=1");
        exit();
    } else {
        echo "Error: Could not delete zone. It might be linked to users.";
    }
}
// إضافة هذا الجزء داخل الـ POST logic في الكنترولر
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = $_POST['zone_id'];
    $newName = $_POST['new_name'];
    
    if (Zone::updateZone($id, $newName)) {
        header("Location: ../Views/Admin/zones.php?updated=1");
        exit();
    }
}
?>