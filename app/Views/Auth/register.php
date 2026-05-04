<?php
session_start();
require_once __DIR__ . "/../../../../Core/database.php";

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
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">

<style>
.auth-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f4;
    padding: 24px;
}

.auth-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 40px 36px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 8px 32px rgba(0,0,0,.08);
}

.auth-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 28px;
}
.auth-logo img { width: 38px; }
.auth-logo span {
    font-size: 1.2rem;
    font-weight: 800;
    letter-spacing: -.5px;
}
.auth-logo span b { color: #ef4444; }

.auth-card h2 {
    font-size: 1.3rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 24px;
    color: #111;
}

.input-group {
    position: relative;
    margin-bottom: 14px;
}
.input-group i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.9rem;
    pointer-events: none;
}
.input-group input {
    width: 100%;
    padding: 11px 14px 11px 38px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.88rem;
    color: #111;
    outline: none;
    transition: border-color .15s;
    background: #fafafa;
}
.input-group input:focus {
    border-color: #ef4444;
    background: #fff;
}

.auth-error {
    background: #fee2e2;
    color: #b91c1c;
    border-radius: 8px;
    padding: 9px 14px;
    font-size: 0.82rem;
    margin-bottom: 14px;
    text-align: center;
}

.auth-btn {
    width: 100%;
    padding: 12px;
    background: #ef4444;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.92rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
    margin-top: 6px;
}
.auth-btn:hover { background: #b91c1c; }

.auth-link {
    text-align: center;
    margin-top: 18px;
    font-size: 0.83rem;
    color: #6b7280;
}
.auth-link a { color: #ef4444; font-weight: 600; text-decoration: none; }
.auth-link a:hover { text-decoration: underline; }

.auth-back {
    text-align: center;
    margin-top: 12px;
    font-size: 0.8rem;
}
.auth-back a { color: #9ca3af; text-decoration: none; }
.auth-back a:hover { color: #111; }

/* password strength hint */
.pass-hint {
    font-size: 0.74rem;
    color: #9ca3af;
    margin-top: -10px;
    margin-bottom: 14px;
    padding-left: 4px;
}
</style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub">
            <span>TOOL<b>HUB</b></span>
        </div>

        <h2>Create Account</h2>

        <?php if ($error): ?>
            <div class="auth-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
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
            <a href="../Public/home.php"><i class="fa fa-arrow-left"></i> Back to Home</a>
        </div>

    </div>
</div>

</body>
</html>