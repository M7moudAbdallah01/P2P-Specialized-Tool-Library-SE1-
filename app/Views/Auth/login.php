<?php
session_start();
require_once __DIR__ . "../../../../Core/database.php";

$db   = Database::getInstance();
$conn = $db->getConnection();

$email = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../Admin/dashboard.php");
            } elseif ($user['role'] === 'technical') {
                header("Location: ../Tech/dashboard.php");
            } else {
                header("Location: ../Client/client_dashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Email not found.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Login</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/home.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">

</head>
<body>

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
        <a href="../Auth/login.php" class="nav-link active">
            <i class="fa fa-right-to-bracket"></i> Login
        </a>
        <a href="../Auth/register.php" class="nav-link">
            <i class="fa fa-user-plus"></i> Register
        </a>
    </div>
</div>
 
<!-- ── RIGHT SIDE ── -->
<div class="layout-right">
 
    <div class="topbar">
        <div class="topbar-title">Login</div>
    </div>
 
    <div class="main-content">
        <div class="auth-center-wrap">
            <div class="auth-card">
 
                <div class="auth-logo">
                    <img src="../../assets/images/logo.png" alt="Tool Hub">
                    <span>TOOL<b>HUB</b></span>
                </div>
 
                <h2>Welcome Back</h2>
 
                <?php if ($error): ?>
                    <div class="auth-error">
                        <i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
 
                <form method="POST" action="login.php">
 
                    <div class="input-group">
                        <i class="fa fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email Address"
                               value="<?= htmlspecialchars($email) ?>" required>
                    </div>
 
                    <div class="input-group">
                        <i class="fa fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
 
                    <button type="submit" class="auth-btn">
                        <i class="fa fa-right-to-bracket"></i> Log In
                    </button>
 
                </form>
 
                <div class="auth-link">
                    Don't have an account? <a href="register.php">Register</a>
                </div>
 
                <div class="auth-back">
                    <a href="../Public/home.php">
                        <i class="fa fa-arrow-left"></i> Back to Home
                    </a>
                </div>
 
            </div>
        </div>
    </div><!-- /.main-content -->
 
</div><!-- /.layout-right -->
 

</body>
</html>