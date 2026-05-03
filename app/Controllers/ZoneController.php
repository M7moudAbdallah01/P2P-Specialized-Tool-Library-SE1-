<?php
require_once __DIR__ . "/../Models/Zone.php";

// أولاً: كود المسح (بيشتغل لما تدوس على لينك الحذف)
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    if (Zone::delete($id)) {
        header("Location: ../Views/Admin/zones.php?deleted=1");
        exit();
    }
}

// ثانياً: كود الإضافة (بيشتغل لما تملأ الفورم)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['zone_name'])) {
    $zoneName = trim($_POST['zone_name']);
    if (!empty($zoneName)) {
        $zoneModel = new Zone();
        if ($zoneModel->addZone($zoneName)) {
            header("Location: ../Views/Admin/zones.php?success=1");
            exit();
        }
    }
}
