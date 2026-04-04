<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/login.css">

    <title>Register | Tool Library</title>
</head>
<body>

    <div class="navbar">
        <div class="logo">ToolLibrary</div>

        <div class="nav-links">
            <a href="../index.php">Home</a>
        </div>

        <div class="nav-auth">
            <a href="login.php" class="btn-outline">Login</a>
        </div>
    </div>

    <div class="form-container">
        <div class="form-box">
            <h2>Create Account</h2>
            <p class="subtitle">Join Tool Library today</p>

            <form method="POST">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>

                <button class="btn" name="register">Register</button>
            </form>

            <p class="switch">
                Already have an account?
                <a href="login.php">Login</a>
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