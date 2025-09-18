<?php 
// Correct include path
include 'includes/header.php';
?>
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
        </div>
    </div>
    <div class="carousel-nav">
        <div class="carousel-dot active"></div>
        <div class="carousel-dot"></div>
        <div class="carousel-dot"></div>
        <div class="carousel-dot"></div>
    </div>
</section>

<section id="about" class="section about">
    <h2>About Us</h2>
    <p>We are committed to delivering fresh and tasty meals from your favorite restaurants.</p>
</section>
    
<section id="reviews" class="section reviews">
    <h2>Customer Reviews</h2>
    <div class="review-list">
        <div class="review-card">"Amazing food and quick delivery!" - Jane</div>
        <div class="review-card">"Great experience, will order again!" - Mike</div>
        <div class="review-card">"Highly recommended, excellent service!" - Sarah</div>
    </div>
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

<script src="index.js" defer></script>
</body>
</html>