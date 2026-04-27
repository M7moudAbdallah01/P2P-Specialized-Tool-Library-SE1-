<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tool Hub - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="Css/login.css">

</head>
<body>

<div class="login-container">

    <div class="logo">
        <img src="logo.png" alt="Tool Hub Logo">
    </div>

    <h2>Tool Hub Login</h2>

    <form>

        <div class="input-box">
            <i class="fa fa-user"></i>
            <input type="email" placeholder="Email Address" required>
        </div>

        <div class="input-box">
            <i class="fa fa-lock"></i>
            <input type="password" id="password" placeholder="Password" required>
            <i class="fa fa-eye toggle-password" onclick="togglePassword()"></i>
        </div>

        <div class="remember">
            <label>
                <input type="checkbox"> Remember me
            </label>
            <a href="#">Forgot password?</a>
        </div>

        <button type="submit">Login</button>

        <div class="register">
            Don't have an account? <a href="#">Register</a>
        </div>

    </form>

</div>

</body>
</html>