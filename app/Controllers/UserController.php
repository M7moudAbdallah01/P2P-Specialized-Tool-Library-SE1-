<?php
require_once __DIR__ . "/../Models/User.php";

$userModel = new User();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $userId = $_POST['user_id'];
    $action = $_POST['action'];

    $status = 'active';
    if ($action == 'suspend') {
        $status = 'suspended';
    } elseif ($action == 'blacklist') {
        $status = 'blacklisted';
    } elseif ($action == 'activate') {
        $status = 'active';
    }

    if ($userModel->updateStatus($userId, $status)) {
        header("Location: ../Views/Admin/users.php?success=1");
    } else {
        echo "Error updating user status.";
    }
    exit();
}