<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";

$db   = Database::getInstance();
$conn = $db->getConnection();

/*
|--------------------------------------------------------------------------
| SECURITY + ROLE CHECK
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$error   = "";
$success = "";

/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | TOOLS TABLE FIELDS
    |--------------------------------------------------------------------------
    */
    $tool_name      = trim($_POST['tool_name'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $base_price     = floatval($_POST['base_price'] ?? 0);
    $category_id    = intval($_POST['category_id'] ?? 0);
    $state          = trim($_POST['state'] ?? 'Good');
    $availability   = isset($_POST['availability']) ? intval($_POST['availability']) : 1;
    $warranty_date  = !empty($_POST['warranty_expiry_date']) ? $_POST['warranty_expiry_date'] : null;
    $video_link     = trim($_POST['video_link'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | FILE PATHS
    |--------------------------------------------------------------------------
    */
    $manual_path = null;
    $image_path  = null;

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */
    if (empty($tool_name) || empty($description)) {
        $error = "Tool name and description are required.";
    }

    if ($base_price < 0) {
        $error = "Base price must be valid.";
    }

    /*
    |--------------------------------------------------------------------------
    | HANDLE PDF MANUAL
    |--------------------------------------------------------------------------
    */
    if (
        empty($error) &&
        isset($_FILES['manual']) &&
        $_FILES['manual']['error'] === UPLOAD_ERR_OK
    ) {

        $manual_dir = __DIR__ . "/uploads/manuals/";

        if (!is_dir($manual_dir)) {
            mkdir($manual_dir, 0777, true);
        }

        $manual_ext = strtolower(pathinfo($_FILES["manual"]["name"], PATHINFO_EXTENSION));

        if ($manual_ext !== "pdf") {
            $error = "Only PDF manuals are allowed.";
        } elseif ($_FILES["manual"]["size"] > 5000000) {
            $error = "Manual size must not exceed 5MB.";
        } else {

            $manual_name = time() . "_manual_" .
                preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES["manual"]["name"]));

            $manual_server_path = $manual_dir . $manual_name;
            $manual_path        = "uploads/manuals/" . $manual_name;

            if (!move_uploaded_file($_FILES["manual"]["tmp_name"], $manual_server_path)) {
                $error = "Failed to upload manual file.";
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HANDLE TOOL IMAGE
    |--------------------------------------------------------------------------
    */
    if (
        empty($error) &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] === UPLOAD_ERR_OK
    ) {

        $image_dir = __DIR__ . "/uploads/tools/";

        if (!is_dir($image_dir)) {
            mkdir($image_dir, 0777, true);
        }

        $image_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($image_ext, $allowed)) {
            $error = "Only JPG, PNG, WEBP images are allowed.";
        } elseif ($_FILES["image"]["size"] > 5000000) {
            $error = "Image size must not exceed 5MB.";
        } else {

            $image_name = time() . "_image_" .
                preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES["image"]["name"]));

            $image_server_path = $image_dir . $image_name;
            $image_path        = "uploads/tools/" . $image_name;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_server_path)) {
                $error = "Failed to upload tool image.";
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DATABASE INSERT INTO TOOLS ONLY
    |--------------------------------------------------------------------------
    */
    if (empty($error)) {

        try {

            $stmt = $conn->prepare("
                INSERT INTO tools (
                    owner_id,
                    category_id,
                    name,
                    description,
                    base_price,
                    state,
                    availability,
                    warranty_expiry_date,
                    manual_path,
                    video_link,
                    image_path
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param(
                "iissdsissss",
                $user_id,
                $category_id,
                $tool_name,
                $description,
                $base_price,
                $state,
                $availability,
                $warranty_date,
                $manual_path,
                $video_link,
                $image_path
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to insert tool: " . $stmt->error);
            }

            $stmt->close();

            header("Location: ToolSpecification.php?success=1");
            exit();

        } catch (Exception $e) {

            $error = $e->getMessage();

            header("Location: ToolSpecification.php?error=" . urlencode($error));
            exit();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FINAL ERROR REDIRECT
    |--------------------------------------------------------------------------
    */
    if (!empty($error)) {
        header("Location: ToolSpecification.php?error=" . urlencode($error));
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| FETCH CATEGORIES
|--------------------------------------------------------------------------
*/
$categories = [];
$catQuery = $conn->query("SELECT category_id, name FROM category ORDER BY name ASC");
if ($catQuery) {
    while ($row = $catQuery->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
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
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="role-badge" style="background:#6366f1;color:#fff;">CLIENT</div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
        <a href="../Tools/tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
        </a>
        <a href="ToolSpecification.php" class="nav-link active"><i class="fa fa-plus"></i> Add Tool</a>
        <a href="../Tools/categories.php" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" />
                <line x1="8" y1="21" x2="16" y2="21" />
                <line x1="12" y1="17" x2="12" y2="21" />
            </svg> Categories
        </a>
        <a href="chat.php" class="nav-link"><i class="fa fa-comments"></i> Chat</a>
        <!-- <a href="reports.php" class="nav-link"><i class="fa fa-scale-balanced"></i> Reports</a> -->
        <a href="ToolCompatibility.php" class="nav-link"><i class="fa fa-circle-check"></i> Compatibility Checker</a>
        <a href="DamageDeclaration.php" class="nav-link">
            <i class="fa fa-triangle-exclamation"></i> Damage Report
         </a>
    </div>
</div>

<div class="layout-right">

    <div class="topbar">
        <div class="topbar-title">Add Tool</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#6366f1;color:#fff;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Client</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <div class="main-content">

        <div class="tool-page">
            <div class="login-container full-width">

                <h2>Add New Tool</h2>

                <?php if (isset($_GET['success'])): ?>
                    <div class="success-msg">Tool added successfully!</div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="error-msg"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>

                <form action="ToolSpecification.php" method="POST" enctype="multipart/form-data">

                    <div class="input-group">
                        <label>Tool Name</label>
                        <div class="input-box">
                            <input type="text" name="tool_name" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Description</label>
                        <div class="input-box">
                            <textarea name="description" rows="4" style="width: 690px;" required></textarea>
                        </div>
                    </div>

                    <div class="grid-inputs">

                        <div class="input-group">
                            <label>Category</label>
                            <div class="input-box">
                                <select name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>">
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Base Price / Day ($)</label>
                            <div class="input-box">
                                <input type="number" name="base_price" step="0.01" min="0" required>
                            </div>
                        </div>

                    </div>

                    <div class="grid-inputs">

                        <div class="input-group">
                            <label>Condition</label>
                            <div class="input-box">
                                <select name="state">
                                    <option value="Good">Good</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Poor">Poor</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Availability</label>
                            <div class="input-box">
                                <select name="availability">
                                    <option value="1">Available</option>
                                    <option value="0">Unavailable</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="input-group">
                        <label>Manual (PDF only)</label>
                        <div class="input-box">
                            <input type="file" name="manual" accept=".pdf">
                        </div>
                    </div>

                    <div class="grid-inputs">

                        <div class="input-group">
                            <label>Video Link</label>
                            <div class="input-box">
                                <input type="url" name="video_link" placeholder="https://youtube.com/...">
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Warranty Expiry Date</label>
                            <div class="input-box">
                                <input type="date" name="warranty_expiry_date">
                            </div>
                        </div>

                    </div>

                    <button type="submit" class="upload-btn">
                        <i class="fa fa-upload"></i> Add Tool
                    </button>

                </form>

            </div>
        </div>

    </div>
</div>

</body>
</html>