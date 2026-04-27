<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tool Hub - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="Css/auth.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="page-wrapper"></div>

    <div class="login-container">

        <div class="logo">
            <img src="logo.png" alt="Tool Hub Logo">
        </div>

        <h2>Tool Hub Login</h2>

        <form method="POST" action="login.php">

            <div class="input-box">
                <i class="fa fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="input-box">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="remember">
                <label>
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="#">Forgot password?</a>
            </div>

            <button type="submit">Login</button>

            <div class="register">
                Don't have an account? <a href="Views/Auth/register.php">Register</a>
            </div>

        </form>

    </div>

<footer class="footer">
    <p>© 2026 Tool Hub. All Rights Reserved.</p>
</footer>

</div>



</body>
</html>