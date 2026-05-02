<?php
session_start();

require_once "../../Controllers/conn.php";

$db = Database::getInstance();
$conn = $db->getConnection();

$username = "";
$email = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {

        $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already exists";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = "client";

            $stmt = $conn->prepare("INSERT INTO user (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Something went wrong. Try again.";
            }
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tool Hub - Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../assets/Css/auth.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="page-wrapper"></div>
    <div class="login-container">

        <div class="logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
        </div>

        <h2>Create Account</h2>

        <form method="POST" action="register.php">

            <div class="input-box">
                <i class="fa fa-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="input-box">
                <i class="fa fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="input-box">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="input-box">
                <i class="fa fa-lock"></i>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>

            <?php if (!empty($error)) { ?>
                <p style="color:red; text-align:center;"><?php echo $error; ?></p>
            <?php } ?>

            <?php if (!empty($success)) { ?>
                <p style="color:green; text-align:center;"><?php echo $success; ?></p>
            <?php } ?>

            <button type="submit">Register</button>

            <div class="register">
                Already have an account? <a href="../../login.php">Login</a>
            </div>

        </form>

    </div>


    <footer class="footer">
        <p>© 2026 Tool Hub. All Rights Reserved.</p>
    </footer>

</div>

</body>
</html>