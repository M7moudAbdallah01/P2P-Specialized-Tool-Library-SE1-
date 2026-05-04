<?php 
session_start();

require_once __DIR__ . "../../../../Core/database.php";

$db = Database::getInstance();
$conn = $db->getConnection();

$email = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
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
                header("Location: ../Admin/dashboard.php");
            } elseif ($user['role'] == "technical") {
                header("Location: ../Tech/dashboard.php");
            } else {
                header("Location: ../Client/dashboard.php");
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

     
      <link rel="stylesheet" href="../../assets/Css/style.css">
      <link rel="stylesheet" href="../../assets/Css/admin.css">


    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>


<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

        <div class="role-badge" style="background:#6366f1;color:#fff;">GUEST</div>



    <div class="sidebar-nav">
        <a href="#" class="nav-link active"><i class="fa fa-home"></i> Home</a>
        <a href="<?= BASE_URL ?>../app/Views/Auth/login.php" class="nav-link"><i class="fa fa-right-to-bracket"></i> Login</a>
        <a href="<?= BASE_URL ?>../app/Views/Auth/register.php" class="nav-link"><i class="fa fa-user-plus"></i> Register</a>
    </div>


</div>

<div class="layout-right">

    <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-title">Dashboard</div>
        </div>

        <div class="main-content">

            <div class="tool-page">

                <div class="login-container full-width">

                    <h2>Tool Hub Login</h2>

                    <form method="POST" action="../Auth/login.php">

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
                            Don't have an account? <a href="../Auth/register.php">Register</a>
                        </div>

                    </form>

                </div>

            </div>

        </div>
</div>


</body>

</html>