<style>
/* === Footer Styles === */
footer {
background-color: rgba(0, 0, 0, 0.9);
backdrop-filter: blur(10px);
border-top: 2px solid #FF4500;
padding: 50px 30px 20px;
color: #FF4500;
font-family: 'Poppins', sans-serif;
}

.footer-container {
width: 0 auto;
max-width: 1400px;
margin: 0 auto;
display: flex;
flex-wrap: wrap;
justify-content: space-between;
gap: 40px;
}

.footer-section {
flex: 1;
min-width: 220px;
}

.footer-section h3 {
color: #FF4500;
font-size: 18px;
font-weight: 600;
margin-bottom: 18px;
position: relative;
padding-bottom: 6px;
text-transform: uppercase;
letter-spacing: 0.5px;
/* Centering headings for consistency */
display: inline-block;
width: 100%;
text-align: center;
}

.footer-section h3::after {
content: '';
position: absolute;
bottom: 0;
/* Center the pseudo-element */
left: 50%;
transform: translateX(-50%);
width: 60px;
height: 2px;
background-color: #FF4500;
border-radius: 2px;
}

.footer-section p,
.footer-section ul,
.footer-section a {
font-size: 14px;
color: #FF4500;
line-height: 1.7;
text-decoration: none;
}

.footer-section p {
margin-bottom: 12px;
}

.footer-section ul {
list-style: none;
padding: 0;
margin: 0;
}

.footer-section ul li {
margin-bottom: 10px;
}

.footer-section a {
transition: color 0.3s ease;
}

.footer-section a:hover {
color: #FF4500;
}

/* Footer Logo Section */
.footer-logo {
color: #FF4500;
font-size: 28px;
font-weight: 700;
text-shadow: 2px 2px 5px rgba(0,0,0,0.6);
margin-bottom: 12px;
display: inline-block;
position: relative;
padding-bottom: 6px;
text-align: center;
width: 100%;
}

.footer-logo::after {
content: '';
position: absolute;
bottom: 0;
/* Center the pseudo-element */
left: 50%;
transform: translateX(-50%);
width: 60px;
height: 2px;
background-color: #FF4500;
border-radius: 2px;
}

/* Social Icons */
.social-icons {
display: flex;
gap: 15px;
margin-top: 18px;
justify-content: center;
}

.social-icons a {
color: #fff;
font-size: 18px;
display: flex;
align-items: center;
justify-content: center;
width: 38px;
height: 38px;
border: 1px solid #444;
border-radius: 50%;
transition: all 0.3s ease;
}

.social-icons a:hover {
color: #FF4500;
border-color: #FF4500;
transform: translateY(-3px);
}

/* Copyright Section */
.footer-bottom {
text-align: center;
border-top: 1px solid #333;
padding-top: 20px;
margin-top: 40px;
font-size: 13px;
color: #888;
letter-spacing: 0.5px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
.footer-container {
flex-direction: column;
align-items: center;
text-align: center;
}
}
</style>

<footer>
<div class="footer-container">
<!-- About Section -->
<div class="footer-section">
<h1 class="footer-logo">MealMate</h1>
<p>Your ultimate destination for delicious food delivered right to your doorstep. We are committed to providing the best dining experience.</p>
<div class="social-icons">
<a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
<a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
<a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
<a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
</div>
</div>

    <!-- Quick Links Section -->
    <div class="footer-section">
        <h3>Quick Links</h3>
        <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">Menu</a></li>
            <li><a href="#">Orders</a></li>
            <li><a href="#">Contact Us</a></li>
            <li><a href="#">Privacy Policy</a></li>
        </ul>
    </div>

    <!-- Contact Section -->
    <div class="footer-section">
        <h3>Contact Info</h3>
        <p><strong>Adrress:</strong><br>123 Foodie Street,<br>Cuisine City, 12345</p>
        <p><strong>Phone:</strong> 091 1234567</p>
        <p><strong>Email:</strong> info@mealmate.com</p>
    </div>

    <!-- Working Hours Section -->
    <div class="footer-section">
        <h3>Working Hours</h3>
        <p>
            Monday - Friday: 9am - 10pm<br>
            Saturday: 10am - 11pm<br>
            Sunday: 10am - 9pm
        </p>
    </div>
</div>

<div class="footer-bottom">
    &copy; <?= date('Y') ?> MealMate. All rights reserved.
</div>

</footer>