<?php
require_once __DIR__ . "/../Models/Zone.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['zone'])) {
    
    $zoneName = trim($_POST['zone']);

    if (!empty($zoneName)) {
        $result = Zone::add($zoneName);

        if ($result) {
            header("Location: ../Views/Admin/zones.php?success=1");
            exit();
        } else {
            echo "Error: Could not add zone to database.";
        }
    } else {
        echo "Please enter a valid zone name.";
    }
} else {
    header("Location: ../Views/Admin/zones.php");
    exit();
}