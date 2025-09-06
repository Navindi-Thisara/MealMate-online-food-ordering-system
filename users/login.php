<?php
session_start();
require_once('../includes/db_connect.php'); 

$msg = "";
$redirect = false;

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
                $msg = "✅ Login successful! Redirecting to menu...";
                $redirect = true;

                // Start session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

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
    <link rel="stylesheet" href="../assets/form.css">
    <?php if ($redirect): ?>
        <meta http-equiv="refresh" content="2;url=../food_management/menu.php">
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header>
        <h1>MealMate</h1>
        <nav>
            <a href="../index.php">Home</a>
            <a href="register.php">Register</a>
            <a href="../food_management/menu.php">Menu</a>
            <a href="../cart.php">Cart</a>
        </nav>
    </header>

    <!-- Login Form -->
    <div class="form-container">
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
    </div>

    <!-- Footer -->
    <footer>
        &copy; <?= date('Y'); ?> MealMate. All rights reserved.
    </footer>
</body>
</html>
