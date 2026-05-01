<?php 
session_start();

require_once "Controllers/conn.php";

$db = Database::getInstance();
$conn = $db->getConnection();

$email = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == "admin") {
                header("Location: Views/Admin/dashboard.php");
            } elseif ($user['role'] == "technical") {
                header("Location: Views/Tech/dashboard.php");
            } else {
                header("Location: Views/Client/dashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "Email not found";
    }

    $stmt->close();
}
?>

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
            <img src="images/logo.png" alt="Tool Hub Logo">
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

            <span style="color:red;"><?php echo $error; ?></span>


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