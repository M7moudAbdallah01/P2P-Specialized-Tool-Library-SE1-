<?php 
require_once "conn.php";
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <title>Tool Library</title>
</head>

<body>

    <div class="navbar">
        <div class="logo">Tool Library</div>

        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="my_tools.php">My Tools</a>
            <a href="add_tool.php">Add Tool</a>
        </div>

        <div class="nav-auth">
            <a href="auth/login.php" class="btn-outline">Login</a>
            <a href="auth/register.php" class="btn">Register</a>
        </div>
    </div>

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

    <footer class="footer">
        <div class="footer-container">

            <div class="footer-col">
                <h3>Tool Library</h3>
                <p>
                    This platform allows community members to lend and borrow high-value tools rather than buying them.
                </p>
            </div>

            <div class="footer-col">
                <h4>Quick Links</h4>
                <a href="index.php">Home</a>
                <a href="my_tools.php">My Tools</a>
                <a href="add_tool.php">Add Tool</a>
            </div>

            <div class="footer-col">
                <h4>Account</h4>
                <a href="auth/login.php">Login</a>
                <a href="auth/register.php">Register</a>
            </div>

            <div class="footer-col">
                <h4>Pages</h4>
                <a href="tool_de.php">Tool Details</a>
                <a href="booking.php">Booking</a>
            </div>

            <div class="footer-col">
                <h4>Contact</h4>
                <p>Email: support@toollibrary.com</p>
                <p>Phone: +20 100 000 000</p>
            </div>

        </div>

        <div class="footer-bottom">
            <p>© 2026 Tool Library | All Rights Reserved</p>
        </div>
    </footer>

</body>
</html>