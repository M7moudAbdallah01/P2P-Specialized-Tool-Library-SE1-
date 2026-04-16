<?php 
require_once "../conn.php";
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <title>Tool Library</title>
</head>

<body>

<?php include_once('header.php'); ?>

    <div class="hero">
        <div class="hero-content">
            <h1>Rent Tools, Save Money</h1>
            <p>Access the tools you need without buying, and save money every time</p>
            <a href="auth/register.php" class="btn">Get Started</a>
        </div>
    </div>

    <div class="container">
        <h2 class="section-title">Why Choose Us</h2>

        <div class="features">
            <div class="feature-box">
                <i class="fas fa-tools"></i>
                <h3>Wide Range</h3>
                <p>Thousands of tools available in one place</p>
            </div>

            <div class="feature-box">
                <i class="fas fa-money-bill-wave"></i>
                <h3>Save Money</h3>
                <p>Rent instead of buying and reduce costs</p>
            </div>

            <div class="feature-box">
                <i class="fas fa-shield-alt"></i>
                <h3>Secure System</h3>
                <p>Safe platform with ratings and guarantees</p>
            </div>
        </div>
    </div>

    <div class="container">
        <h2 class="section-title">Available Tools</h2>

        <div class="cards">

            <div class="card">
                <h3>Drill Machine</h3>
                <p>Price: 50 EGP</p>
                <a href="tool_de.php" class="btn">View</a>
            </div>

            <div class="card">
                <h3>3D Printer</h3>
                <p>Price: 150 EGP</p>
                <a href="tool_de.php" class="btn">View</a>
            </div>

        </div>
    </div>

    <div class="cta">
        <h2>Start sharing your tools today</h2>
        <p>Join Tool Library and make better use of your equipment</p>
        <a href="auth/register.php" class="btn">Join Now</a>
    </div>

    
<?php include_once('footer.php'); ?>
</body>
</html>