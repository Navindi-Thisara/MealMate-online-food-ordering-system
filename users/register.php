<?php
session_start();
include '../includes/db_connect.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $contact_no = trim($_POST['contact_no']);
    $address    = trim($_POST['address']);

    // === SERVER-SIDE VALIDATION ===
    if (!preg_match("/^[A-Za-z ]+$/", $full_name)) {
        $msg = "⚠️ Full Name should only contain letters and spaces.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "⚠️ Please enter a valid email address.";
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#\$%\^&\*\_\-]).{8,}$/", $password)) {
        $msg = "⚠️ Password must be at least 8 characters, including uppercase, lowercase, number, and special character (e.g., !@#$%^&* or _).";
    } elseif (!preg_match("/^\d{10}$/", $contact_no)) {
        $msg = "⚠️ Contact number must be exactly 10 digits.";
    } elseif (empty($address)) {
        $msg = "⚠️ Address cannot be empty.";
    } else {
        $password = password_hash($password, PASSWORD_BCRYPT);

        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $msg = "⚠️ Email already registered!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, contact_no, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $full_name, $email, $password, $contact_no, $address);

            if ($stmt->execute()) {
                $msg = "✅ Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $msg = "❌ Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - MealMate</title>
<link rel="stylesheet" href="../assets/form.css?v=1">
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    /* === Theme Variables === */
    :root {
        --bg-primary: #0d0d0d;
        --bg-secondary: #1a1a1a;
        --bg-card: #222;
        --bg-header: rgba(0, 0, 0, 0.8);
        --text-primary: #fff;
        --text-secondary: #ddd;
        --text-muted: #ccc;
        --accent-primary: #FF4500;
        --accent-hover: #FF6B35;
        --border-color: #FF4500;
        --shadow-color: rgba(255, 69, 0, 0.3);
        --footer-bg: rgba(0, 0, 0, 0.9);
        --footer-border: #333;
    }

    [data-theme="light"] {
        --bg-primary: #fafafa;
        --bg-secondary: #f0f0f0;
        --bg-card: #fff;
        --bg-header: rgba(255, 255, 255, 0.98);
        --text-primary: #1a1a1a;
        --text-secondary: #333;
        --text-muted: #555;
        --accent-primary: #FF4500;
        --accent-hover: #FF3300;
        --border-color: #FF4500;
        --shadow-color: rgba(255, 69, 0, 0.25);
        --footer-bg: #f8f8f8;
        --footer-border: #ddd;
    }

    body {
        background-color: var(--bg-primary);
        color: var(--text-primary);
        transition: background-color 0.3s ease, color 0.3s ease;
        font-family: 'Poppins', sans-serif;
    }

    header {
        background: var(--bg-header);
        border-bottom: 2px solid var(--border-color);
    }

    .nav-logo { color: var(--accent-primary); }
    .nav-menu a { color: var(--text-primary); }
    .nav-menu a:hover { color: var(--accent-primary); }

    .form-container {
        background: var(--bg-card);
        border: 2px solid var(--border-color);
        box-shadow: 0 10px 30px var(--shadow-color);
    }

    .form-container input,
    .form-container textarea {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .form-container input:focus,
    .form-container textarea:focus {
        border-color: var(--accent-hover);
        background: var(--bg-card);
    }

    .form-container button {
        background: var(--accent-primary);
        color: #000;
    }

    .form-container button:hover {
        background: var(--accent-hover);
    }

    footer {
        background-color: var(--footer-bg);
        border-top: 2px solid var(--border-color);
        color: var(--text-primary);
    }

    .login-link a { color: var(--accent-primary); }

    /* Theme Toggle */
    .theme-toggle-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 9999;
    }
    .theme-toggle-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--accent-primary);
        border: 3px solid var(--bg-card);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
        box-shadow: 0 8px 25px var(--shadow-color);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .theme-toggle-btn:hover { transform: scale(1.1) rotate(15deg); }
    .theme-toggle-btn:active { transform: scale(0.95); }
    .theme-toggle-btn .theme-icon { position: absolute; transition: all 0.3s ease; }
    .theme-toggle-btn .sun-icon { opacity: 0; transform: rotate(-90deg) scale(0); }
    .theme-toggle-btn .moon-icon { opacity: 1; transform: rotate(0deg) scale(1); }
    [data-theme="light"] .theme-toggle-btn .sun-icon { opacity: 1; transform: rotate(0deg) scale(1); }
    [data-theme="light"] .theme-toggle-btn .moon-icon { opacity: 0; transform: rotate(90deg) scale(0); }

    [data-theme="light"] .form-container input,
    [data-theme="light"] .form-container textarea {
        background-color: #fff;
        color: #1a1a1a;
        border: 1px solid var(--border-color);
    }
    [data-theme="light"] .form-container input:focus,
    [data-theme="light"] .form-container textarea:focus {
        background-color: #fff;
        border-color: var(--accent-hover);
    }
    [data-theme="light"] .form-container input::placeholder,
    [data-theme="light"] .form-container textarea::placeholder {
        color: #555;
    }

    .error-msg { color: red; font-size: 0.9em; margin-top: 5px; }
    .success-msg { color: green; }
    input.invalid, textarea.invalid { border-color: red !important; }
    input.valid, textarea.valid { border-color: green !important; }
    .msg { margin-bottom: 15px; text-align: center; font-weight: 500; }

    @media (max-width: 768px) {
        .theme-toggle-container { bottom: 20px; right: 20px; }
        .theme-toggle-btn { width: 50px; height: 50px; font-size: 20px; }
    }
</style>
</head>
<body>
<header>
    <h1 class="nav-logo">MealMate</h1>
    <nav class="nav-menu">
        <a href="../index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="../food_management/menu.php">Menu</a>
        <a href="../cart/cart.php">Cart</a>
    </nav>
</header>

<div class="form-container">
    <h2>User Registration</h2>
    <?php if ($msg != ""): ?>
        <div class="msg <?= strpos($msg, 'successful') !== false ? 'success-msg' : 'error-msg' ?>">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <form id="registerForm" action="register.php" method="POST" novalidate>
        <div>
            <input type="text" name="full_name" placeholder="Full Name" required 
                   title="Enter your full name using letters only (A–Z and spaces)">
            <div class="error-msg" id="fullNameError"></div>
        </div>
        <div>
            <input type="email" name="email" placeholder="Email Address" required 
                   title="Enter a valid email (e.g., example@email.com)">
            <div class="error-msg" id="emailError"></div>
        </div>
        <div>
            <input type="password" name="password" placeholder="Password" required 
                   title="Password must be at least 8 characters long with uppercase, lowercase, number, and special character (!@#$%^&* or _)">
            <div class="error-msg" id="passwordError"></div>
        </div>
        <div>
            <input type="text" name="contact_no" placeholder="Contact Number" required 
                   title="Enter your 10-digit phone number (e.g., 0712345678)">
            <div class="error-msg" id="contactError"></div>
        </div>
        <div>
            <textarea name="address" placeholder="Enter Address" required 
                      title="Enter your complete address (e.g., No. 12, Main Street, Colombo)"></textarea>
            <div class="error-msg" id="addressError"></div>
        </div>
        <button type="submit" title="Click to register your MealMate account">Register</button>
    </form>

    <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    <p class="login-link">Are you an administrator? <a href="register_admin.php">Register as Admin</a></p>
</div>

<div class="theme-toggle-container">
    <button class="theme-toggle-btn" aria-label="Toggle theme" title="Switch theme">
        <i class="fas fa-sun theme-icon sun-icon"></i>
        <i class="fas fa-moon theme-icon moon-icon"></i>
    </button>
</div>

<footer>
    &copy; <?= date('Y') ?> MealMate. All rights reserved.
</footer>

<script src="/MealMate-online-food-ordering-system/theme-toggle.js"></script>
</body>
</html>
