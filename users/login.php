<?php
session_start();
require_once('../includes/db_connect.php');

$msg = "";
$redirectUrl = ""; // store where to redirect

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Start session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Choose redirect page by role
                if ($user['role'] === 'admin') {
                    $msg = "✅ Login successful! Redirecting to Admin Dashboard...";
                    $redirectUrl = "../users/admin/admin_dashboard.php";
                } else {
                    $msg = "✅ Login successful! Redirecting to Menu...";
                    $redirectUrl = "../food_management/menu.php";
                }

                // Remember Me
                if ($remember) {
                    setcookie('email', $email, time() + (86400 * 30), "/");
                    setcookie('password', $password, time() + (86400 * 30), "/");
                } else {
                    setcookie('email', '', time() - 3600, "/");
                    setcookie('password', '', time() - 3600, "/");
                }

            } else {
                $msg = "❌ Invalid password!";
            }
        } else {
            $msg = "❌ Email not registered!";
        }
    } else {
        $msg = "⚠️ Please fill all fields!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - MealMate</title>
    <link rel="stylesheet" href="../assets/form.css?v=1">
    <link rel="stylesheet" href="../assets/style.css">

    <?php if (!empty($redirectUrl)): ?>
        <meta http-equiv="refresh" content="2;url=<?= $redirectUrl ?>">
    <?php endif; ?>
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
            <?php if (!empty($redirectUrl)): ?>
                <h2><?= $msg ?></h2>
            <?php else: ?>
                <h2>User Login</h2>

                <?php if ($msg != ""): ?>
                    <div class="msg"><?= $msg ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <input type="email" name="email" placeholder="Email Address" required
                            value="<?= isset($_COOKIE['email']) ? $_COOKIE['email'] : '' ?>">
                    <input type="password" name="password" placeholder="Password" required
                            value="<?= isset($_COOKIE['password']) ? $_COOKIE['password'] : '' ?>">

                    <div class="remember-me">
                        <input type="checkbox" name="remember" id="remember" <?= isset($_COOKIE['email']) ? 'checked' : '' ?>>
                        <label for="remember">Remember Me</label>
                    </div>

                    <button type="submit">Login</button>
                </form>

                <p class="login-link">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/simple_footer.php'; ?>
</body>
</html>