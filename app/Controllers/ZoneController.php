<?php
require_once "../Models/Zone.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = $_POST['zone_name'];
    if (Zone::addZone($name)) {
        header("Location: ../Views/Admin/dashboard.php?success=1");
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']); // تأمين الـ ID
    
    if (Zone::deleteZone($id)) {
        header("Location: ../Views/Admin/dashboard.php?deleted=1");
        exit();
    } else {
        echo "Error: Could not delete zone. It might be linked to users.";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = $_POST['zone_id'];
    $newName = $_POST['new_name'];
    
    if (Zone::updateZone($id, $newName)) {
        header("Location: ../Views/Admin/dashboard.php?updated=1");
        exit();
    }
}
?>