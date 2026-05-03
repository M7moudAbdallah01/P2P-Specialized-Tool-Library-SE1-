<?php
// بنطلع خطوة عشان نلاقي الموديلز
require_once __DIR__ . "/../Models/User.php";

$userModel = new User();

// بنشوف هل فيه بيانات مبعوتة عن طريق الـ POST؟
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $userId = $_POST['user_id'];
    $action = $_POST['action'];

    // تحديد الحالة الجديدة بناءً على الزرار اللي انداس
    $status = 'active';
    if ($action == 'suspend') {
        $status = 'suspended';
    } elseif ($action == 'blacklist') {
        $status = 'blacklisted';
    } elseif ($action == 'activate') {
        $status = 'active';
    }

    // بننادي على الفانكشن اللي عملناها في الموديل
    if ($userModel->updateStatus($userId, $status)) {
        // لو تمام، بنرجعه لصفحة المستخدمين تاني عشان يشوف التغيير
        header("Location: ../Views/Admin/users.php?success=1");
    } else {
        echo "Error updating user status.";
    }
    exit();
}