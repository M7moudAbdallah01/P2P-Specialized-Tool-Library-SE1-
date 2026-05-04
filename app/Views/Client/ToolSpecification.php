<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";

$db = Database::getInstance();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tool_name = mysqli_real_escape_string($conn, $_POST['tool_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $video_link = mysqli_real_escape_string($conn, $_POST['video_link']);
    $warranty = mysqli_real_escape_string($conn, $_POST['warranty']);

    $target_dir = "uploads/manuals/";
    if (!is_dir($target_dir))
        mkdir($target_dir, 0777, true);

    $file_name = time() . "_" . basename($_FILES["manual"]["name"]);
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $error = "";

    if ($file_type != "pdf") {
        $error = "Invalid file type. Only PDF files are allowed.";
    } elseif ($_FILES["manual"]["size"] > 5000000) { // 5MB
        $error = "File is too large. Maximum size is 5MB.";
    }


    if ($error == "") {
        if (move_uploaded_file($_FILES["manual"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO tool_specifications (tool_name, description, manual_path, video_link, warranty) 
                    VALUES ('$tool_name', '$description', '$target_file', '$video_link', '$warranty')";

            if ($conn->query($sql) === TRUE) {
                header("Location: add_tool_page.php?success=1");
                exit();
            } else {
                $error = "Database Error: " . $conn->error;
            }
        } else {
            $error = "Failed to upload file to the server.";
        }
    }

    if ($error != "") {
        header("Location: add_tool_page.php?error=" . urlencode($error));
        exit();
    }
}
?>
<!DOCTYPE html>
<html>

<meta charset="UTF-8">
<title>Tool Hub - Add Tool</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/ToolSpecification.css">
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="logo">
                <img src="../../../assets/images/logo.png" alt="Tool Hub Logo">
            </div>
            <div class="brand-text">TOOL HUB</div>
        </div>
        <div class="role-badge" style="background:#6366f1;color:#fff;">CLIENT</div>
        <div class="sidebar-nav">
            <a href="dashboard.php" class="nav-link">
                <i class="fa fa-gauge"></i> Dashboard
            </a>
            <a href="my-tools.php" class="nav-link">
                <i class="fa fa-wrench"></i> My Tools
            </a>
            <a href="ToolSpecification.php" class="nav-link active">
                <i class="fa fa-plus"></i> Add Tool
            </a>
            <a href="../Tools/categories.php" class="nav-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="flex-shrink:0">
                    <rect x="2" y="3" width="20" height="14" rx="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg> Categories
            </a>
            <a href="reservations.php" class="nav-link">
                <i class="fa fa-calendar"></i> Reservations
            </a>
            <a href="chat.php" class="nav-link">
                <i class="fa fa-comments"></i> Messages
            </a>
            <a href="reports.php" class="nav-link">
                <i class="fa fa-scale-balanced"></i> Reports
            </a>

        <a href="ToolCompatibility.php" class="nav-link">
            <i class="fa fa-circle-check"></i> Compatibility Checker
        </a>            
        </div>
    </div>
    <div class="layout-right">

        <div class="topbar">
            <div class="topbar-title">Tool Specification</div>
            <div class="topbar-right">
                <button class="avatar-btn" style="background:#6366f1;color:#fff;border:none;cursor:default;">
                    <span>Client</span>
                </button>
                <a href="../Auth/login.php" class="icon-btn">
                    <i class="fa fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
        <div class="main-content">
            <div class="tool-page">
                <div class="login-container full-width">
                    <h2>Add Tool Specification</h2>
                    <form action="add_tool.php" method="POST" enctype="multipart/form-data">
                        <div class="input-group">
                            <label>Tool Name</label>
                            <div class="input-box">
                                <input type="text" name="tool_name" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Description</label>
                            <div class="input-box">
                                <input type="text" name="description" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Manual (PDF)</label>
                            <div class="input-box">
                                <input type="file" name="manual" required>
                            </div>
                        </div>
                        <div class="grid-inputs">
                            <div class="input-group">
                                <label>Video</label>
                                <div class="input-box">
                                    <input type="text" name="video_link">
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Warranty</label>
                                <div class="input-box">
                                    <input type="text" name="warranty">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="upload-btn">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>