<?php
require_once "../Models/User.php";

$userModel = new User();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $id = $_POST['user_id'];
    $action = $_POST['action'];

    // تحديد الحالة بناءً على الزرار اللي انداس
    $status = 'active';
    if ($action == 'suspend') {
        $status = 'suspended';
    } elseif ($action == 'blacklist') {
        $status = 'blacklisted';
    }

    if ($userModel->updateStatus($id, $status)) {
        header("Location: ../Views/Admin/users.php?status=updated");
    } else {
        echo "حدث خطأ أثناء تحديث الحالة.";
    }
    exit();
}