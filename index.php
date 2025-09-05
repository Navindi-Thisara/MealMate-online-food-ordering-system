<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MealMate - Home</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>

<header>
    <h1>MealMate</h1>
    <nav>
        <a href="users/register.php">Register</a>
        <a href="users/login.php">Login</a>
        <a href="#">Menu</a>
        <a href="#">Cart</a>
    </nav>
</header>

<div class="intro-text">
    <h1>Welcome to MealMate</h1>
    <p>Delicious food delivered to your doorstep!</p>
</div>

<!-- Features Carousel -->
<div class="features-wrapper">
    <div class="features">
        <div class="feature-box">
            <img src="assets/images/slide1.png" alt="Easy Ordering">
            <h2>Easy Ordering</h2>
            <p>Order your favorite meals in just a few clicks!</p>
        </div>
        <div class="feature-box">
            <img src="assets/images/slide2.jpg" alt="Real-Time Tracking">
            <h2>Real-Time Tracking</h2>
            <p>Track your order live from kitchen to your door!</p>
        </div>
        <div class="feature-box">
            <img src="assets/images/slide3.jpg" alt="Secure Payments">
            <h2>Secure Payments</h2>
            <p>Pay safely online using our secure payment system.</p>
        </div>
        <div class="feature-box">
            <img src="assets/images/slide4.jpg" alt="Delicious Meals">
            <h2>Delicious Meals Delivered</h2>
            <p>Fresh and tasty meals delivered straight to you!</p>
        </div>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> MealMate. All rights reserved.
</footer>

<script src="index.js"></script>
</body>
</html>