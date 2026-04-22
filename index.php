<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/login.css">

    <title>Login | Tool Library</title>
</head>
<body>

    <?php require_once('header.php'); ?>

    <div class="form-container">
        <div class="form-box">
            <h2>Welcome Back</h2>
            <p class="subtitle">Login to your account</p>

            <form method="POST">
                <input type="email" name="email" placeholder="Enter your email" required>
                <input type="password" name="password" placeholder="Enter your password" required>

                <button class="btn" name="login">Login</button>
            </form>

            <p class="switch">
                Don't have an account?
                <a href="register.php">Register</a>
            </p>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-bottom">
            <p>© 2026 Tool Library | All Rights Reserved</p>
        </div>
    </footer>

</body>
</html>