<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MealMate - Home</title>
    <link rel="stylesheet" href="index.css">
    <script src="index.js" defer></script>
</head>
<body>

<!-- Header -->
<header>
    <div class="logo">MealMate</div>
    <nav class="landing-nav">
        <a href="#home" class="active">Home</a>
        <a href="#services">Services</a>
        <a href="#about">About</a>
        <a href="#reviews">Reviews</a>
        <a href="#contact">Contact</a>
    </nav>
    <div class="menu-btn" id="menuBtn">&#9776;</div>
</header>

<!-- Side Navigation -->
<div id="sideNav" class="side-nav">
    <a href="javascript:void(0)" class="close-btn" id="closeBtn">&times;</a>
    <a href="users/register.php">Register</a>
    <a href="users/login.php">Login</a>
    <a href="food_management/menu.php">Menu</a>
    <a href="cart/cart.php">Cart</a>
</div>

<!-- Sections -->
<section id="home" class="section home"> 
    <div class="intro-text">
        <h1>Welcome to MealMate</h1>
        <p>Delicious food delivered to your doorstep!</p>
    </div>
</section>

<section id="services" class="section services">
    <h2>Our Services</h2>
    <div class="features-wrapper">
        <div class="features">
            <div class="feature-box">
                <img src="assets/images/slide1.png" alt="Easy Ordering">
                <h3>Easy Ordering</h3>
                <p>Order your favorite meals in just a few clicks!</p>
            </div>
            <div class="feature-box">
                <img src="assets/images/slide2.jpg" alt="Real-Time Tracking">
                <h3>Real-Time Tracking</h3>
                <p>Track your order live from kitchen to your door!</p>
            </div>
            <div class="feature-box">
                <img src="assets/images/slide3.jpg" alt="Secure Payments">
                <h3>Secure Payments</h3>
                <p>Pay safely online using our secure payment system.</p>
            </div>
            <div class="feature-box">
                <img src="assets/images/slide4.jpg" alt="Delicious Meals">
                <h3>Delicious Meals Delivered</h3>
                <p>Fresh and tasty meals delivered straight to you!</p>
            </div>
        </div>
    </div>
</section>

<section id="about" class="section about">
    <h2>About Us</h2>
    <p>We are committed to delivering fresh and tasty meals from your favorite restaurants.</p>
</section>

<section id="reviews" class="section reviews">
    <h2>Customer Reviews</h2>
    <div class="review-card">"Amazing food and quick delivery!" - Jane</div>
    <div class="review-card">"Great experience, will order again!" - Mike</div>
    <div class="review-card">"Highly recommended, excellent service!" - Sarah</div>
</section>

<section id="contact" class="section contact">
    <h2>Contact Us</h2>
    <form>
        <input type="text" placeholder="Your Name" required>
        <input type="email" placeholder="Your Email" required>
        <textarea placeholder="Your Message" required></textarea>
        <button type="submit">Send Message</button>
    </form>
</section>

<footer>
    &copy; <?php echo date('Y'); ?> MealMate. All rights reserved.
</footer>

</body>
</html>
