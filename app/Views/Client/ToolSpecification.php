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

<head>
    <link rel="stylesheet" href="../../assets/Css/ToolSpecification.css">
</head>

<body>
<div class="layout">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="logo">
                <img src="../../assets/images/logo.png">
            </div>
            <div class="brand-text">TOOL HUB</div>
        </div>

        <div class="sidebar-nav">
            <a href="dashboard.php" class="nav-link active">Dashboard</a>
            <a href="tools.php" class="nav-link">Tools</a>
            <a href="reservations.php" class="nav-link">Reservations</a>
            <a href="chat.php" class="nav-link">Chat</a>
        </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="main-content">

        <div class="topbar">
            <div class="topbar-title">Dashboard</div>
        </div>

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