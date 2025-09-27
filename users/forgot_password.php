<?php
session_start();
require_once('../includes/db_connect.php');

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Generate a secure token
            $token = bin2hex(random_bytes(50));

            // Store token in DB (ensure column `reset_token` exists in users table)
            $stmt2 = $conn->prepare("UPDATE users SET reset_token=? WHERE email=?");
            $stmt2->bind_param("ss", $token, $email);
            $stmt2->execute();

            // Reset link (update domain accordingly)
            $resetLink = "http://yourdomain.com/users/reset_password.php?token=$token";

            // Send email (replace with PHPMailer if needed)
            mail($email, "Password Reset Request", "Click the link to reset your password: $resetLink");

            $msg = "✅ Password reset link has been sent to your email.";
        } else {
            $msg = "❌ Email not found!";
        }
    } else {
        $msg = "⚠️ Please enter your email!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - MealMate</title>
    <link rel="stylesheet" href="../assets/form.css?v=1">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            flex-grow: 1;
        }

        .form-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .simple-footer {
            margin-top: auto;
            background-color:#0d0d0d;
            color:#fff;
            padding:20px 0;
            text-align:center;
            font-family:'Poppins', sans-serif;
            font-size:14px;
            width:100%;
            position: relative;
        }
        
        .simple-footer::before {
            content:''; 
            position:absolute;
            top:0;
            left:0;
            width:100%;
            height:2px;
            background-color:#FF4500;
        }

        .nav-logo {
            color: #FF4500;
            font-size: 32px;
            font-weight: 700;
            text-shadow: 3px 3px 6px #000;
            margin-right: auto;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-menu a {
            color: #fff;
            text-decoration: none;
            font-size: 18px;
            font-weight: 400;
            position: relative;
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #FF4500;
            transition: width 0.3s;
        }

        .nav-menu a:hover, .nav-menu a.active {
            color: #FF4500;
        }
        
        .nav-menu a:hover::after, .nav-menu a.active::after {
            width: 100%;
        }

        /* Form messages */
        .msg {
            margin-bottom: 15px;
            color: red;
            text-align: center;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form button {
            background-color: #FF4500;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        form button:hover {
            background-color: #e03e00;
        }

        .back-login {
            margin-top: 15px;
            font-size: 14px;
            text-align: center;
        }

        .back-login a {
            color: #FF4500;
            text-decoration: none;
        }

        .back-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <header>
            <h1 class="nav-logo">MealMate</h1>
            <nav class="nav-menu">
                <a href="../index.php">Home</a>
                <a href="register.php">Register</a>
                <a href="../food_management/menu.php">Menu</a>
                <a href="../cart/cart.php">Cart</a>
            </nav>
        </header>

        <div class="form-container">
            <h2>Forgot Password</h2>

            <?php if ($msg != ""): ?>
                <div class="msg"><?= $msg ?></div>
            <?php endif; ?>

            <form action="forgot_password.php" method="POST">
                <input type="email" name="email" placeholder="Enter your registered email" required>
                <button type="submit">Send Reset Link</button>
            </form>

            <div class="back-login">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>

    <?php include '../includes/simple_footer.php'; ?>
</body>
</html>
