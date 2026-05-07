<?php
session_start();
require_once __DIR__ . "../../../../Core/database.php";

$db   = Database::getInstance();
$conn = $db->getConnection();

$username = "";
$email    = "";
$error    = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username         = trim($_POST['username']);
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
            $stmt->close();
        } else {
            $stmt->close();

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role   = "client";

            $stmt2 = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("ssss", $username, $email, $hashed, $role);

            if ($stmt2->execute()) {
                $stmt2->close();
                header("Location: login.php?registered=1");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
                $stmt2->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Register</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/home.css">
</head>
<body>

<!-- ── SIDEBAR ── -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="role-badge" style="background:#6b7280;color:#fff;">GUEST</div>

    <div class="sidebar-nav">
        <a href="../../../public/index.php" class="nav-link">
            <i class="fa fa-home"></i> Home
        </a>
        <a href="../Auth/login.php" class="nav-link">
            <i class="fa fa-right-to-bracket"></i> Login
        </a>
        <a href="../Auth/register.php" class="nav-link active">
            <i class="fa fa-user-plus"></i> Register
        </a>
    </div>
</div>

<!-- ── RIGHT SIDE ── -->
<div class="layout-right">

    <div class="topbar">
        <div class="topbar-title">Register</div>
    </div>

    <div class="main-content">
        <div class="auth-center-wrap">
            <div class="auth-card">

                <div class="auth-logo">
                    <img src="../../assets/images/logo.png" alt="Tool Hub">
                    <span>TOOL<b>HUB</b></span>
                </div>

                <h2>Create Account</h2>

                <?php if ($error): ?>
                    <div class="auth-error">
                        <i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php">

                    <div class="input-group">
                        <i class="fa fa-user"></i>
                        <input type="text" name="username" placeholder="Full Name"
                               value="<?= htmlspecialchars($username) ?>" required>
                    </div>

                    <div class="input-group">
                        <i class="fa fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email Address"
                               value="<?= htmlspecialchars($email) ?>" required>
                    </div>

                    <div class="input-group">
                        <i class="fa fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <p class="pass-hint">Minimum 8 characters recommended</p>

                    <div class="input-group">
                        <i class="fa fa-lock"></i>
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    </div>

                    <button type="submit" class="auth-btn">
                        <i class="fa fa-user-plus"></i> Create Account
                    </button>

                </form>

                <div class="auth-link">
                    Already have an account? <a href="login.php">Log In</a>
                </div>

                <div class="auth-back">
                    <a href="../../../Public/index.php">
                        <i class="fa fa-arrow-left"></i> Back to Home
                    </a>
                </div>

            </div>
        </div>
    </div><!-- /.main-content -->

</div><!-- /.layout-right -->

</body>
</html>