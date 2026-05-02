<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "tool_library";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    <link rel="stylesheet" href="../../../assets/Css/auth.css">
    <link rel="stylesheet" href="../../../assets/Css/ToolSpecification.css">
</head>

<body>

    <div class="tool-page">
        <div class="login-container full-width">
            <?php if (isset($_GET['error'])): ?>
                <div
                    style="color: #ff2e2e; background: rgba(255,0,0,0.1); padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; border: 1px solid #ff2e2e;">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div
                    style="color: #2eff2e; background: rgba(0,255,0,0.1); padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; border: 1px solid #2eff2e;">
                    تم رفع البيانات بنجاح!
                </div>
            <?php endif; ?>
            <h2>Add Tool Specification</h2>
            <form action="add_tool.php" method="POST" enctype="multipart/form-data">

                <div class="input-group">
                    <label>Tool Name</label>
                    <div class="input-box">
                        <input type="text" name="tool_name" placeholder="e.g. Cordless Drill" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Description</label>
                    <div class="input-box">
                        <input type="text" name="description" placeholder="Brief tool description" required>
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
                        <label>Safety Video URL</label>
                        <div class="input-box">
                            <input type="text" name="video_link" placeholder="https://youtube.com/...">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Warranty Info</label>
                        <div class="input-box">
                            <input type="text" name="warranty" placeholder="e.g. 2 Years">
                        </div>
                    </div>
                </div>

                <button type="submit" class="upload-btn">Upload Tool Specification</button>
            </form>
        </div>

    </div>


</body>

</html>