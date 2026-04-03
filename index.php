<?php 

require_once "conn.php";


?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/global.css">
        <link rel="stylesheet" href="css/login.css">


        <title>Tool Library</title>
    </head>
    <body>


        <form action="login.php" method="POST">

            <label>Email:</label><br>
            <input type="email" name="email" required><br><br>

            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>

            <button type="submit">Login</button>

        </form>

        <br>

        <a href="#">Forgot Password?</a><br>
        <a href="#">Create New Account</a>
        
    </body>
</html>