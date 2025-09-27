<?php
session_start();
require_once('../includes/db_connect.php');

$msg = "";
$showForm = false;

// Check for token in URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Validate token
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $showForm = true;
        $user = $result->fetch_assoc();
    } else {
        $msg = "❌ Invalid or expired token!";
    }
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $token = $_POST['token'];

    if ($newPassword === $confirmPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL WHERE reset_token=?");
        $stmt->bind_param("ss", $hashedPassword, $token);
        $stmt->execute();

        $msg = "✅ Password reset successfully! You can now <a href='login.php'>login</a>.";
        $showForm = false;
    } else {
        $msg = "❌ Passwords do not match!";
        $showForm = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - MealMate</title>
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
            <h2>Reset Password</h2>

            <?php if ($msg != ""): ?>
                <div class="msg"><?= $msg ?></div>
            <?php endif; ?>

            <?php if ($showForm): ?>
                <form action="reset_password.php" method="POST">
                    <input type="password" name="password" placeholder="New Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <button type="submit">Reset Password</button>
                </form>
            <?php endif; ?>

            <div class="back-login">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>

    <?php include '../includes/simple_footer.php'; ?>
</body>
</html>
