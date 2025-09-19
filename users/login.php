<?php
session_start();
require_once('../includes/db_connect.php');

$msg = "";
$redirectUrl = "";

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
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    $msg = "✅ Login successful! Redirecting to Admin Dashboard...";
                    $redirectUrl = "/MealMate-online-food-ordering-system/users/admin/admin_dashboard.php";

                } else {
                    $msg = "✅ Login successful! Redirecting to Menu...";
                    $redirectUrl = "../food_management/menu.php";
                }

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../assets/form.css?v=1">
<link rel="stylesheet" href="../assets/style.css">

<?php if (!empty($redirectUrl)): ?>
<meta http-equiv="refresh" content="2;url=<?= $redirectUrl ?>">
<?php endif; ?>

<style>
/* Body and Scroll */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #121212;
    color: #fff;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    overflow-y: auto;
    scroll-behavior: smooth;
    overflow-x: hidden;
}

/* Header */
header {
    background-color: rgba(0,0,0,0.95);
    padding: 30px 50px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.5);
    position: relative;
    z-index: 10;
}
header h1 {
    text-shadow: 3px 3px 6px #000;
    font-size: 32px;
    font-weight: 700;
    color: #FF4500;
    text-decoration: none;
    letter-spacing: 1px;
}
header nav a {
    color: #fff;
    margin: 0 20px;
    text-decoration: none;
    font-size: 20px;
}
header nav a:hover {
    color: #FF4500;
}

/* Form Container */
.form-container {
    background: rgba(17, 17, 17, 0.95);
    padding: 50px;
    border-radius: 12px;
    width: 400px;
    text-align: center;
    margin: 100px auto 50px;
    border: 2px solid #FF4500;
    box-shadow: 0 10px 30px rgba(0,0,0,0.8);
}
.form-container h2 {
    margin-bottom: 30px;
    color: #FF4500;
    font-size: 32px;
}
input, textarea {
    width: 90%;
    padding: 12px;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid #FF4500;
    background: #1f1f1f;
    color: #fff;
}
button {
    width: 95%;
    padding: 16px;
    background: #FF4500;
    color: #000;
    font-size: 18px;
    font-weight: bold;
    border-radius: 8px;
}
button:hover {
    background: #fff;
    color: #FF4500;
}

/* Remember Me */
.remember-me {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin: 10px 0 20px 20px;
    font-size: 14px;
}
.remember-me input {
    margin-right: 8px;
}

/* Footer */
footer {
    background-color: #000;
    color: #FF4500;
    text-align: center;
    padding: 30px 20px;
    font-size: 18px;
    border-top: 2px solid #FF4500;
    margin-top: auto;
}

/* Responsive adjustments */
@media(max-width: 500px){
    header { flex-direction: column; padding: 20px; font-size: 16px; }
    header h1 { font-size: 28px; margin-bottom: 10px; }
    nav a { margin: 5px; font-size: 16px; }
    .form-container { width: 90%; margin: 80px auto 50px; padding: 30px; }
}
</style>
</head>
<body>

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

<footer>
    &copy; <?= date('Y'); ?> MealMate. All rights reserved.
</footer>

</body>
</html>
