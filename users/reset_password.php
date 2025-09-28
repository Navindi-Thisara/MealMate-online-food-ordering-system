<?php
session_start();
require_once('../includes/db_connect.php');

$msg = "";
$showForm = false;
$token = "";

// Check for token in URL
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
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
    $token = trim($_POST['token']);

    // Validate token again for security
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Password strength validation (underscore included as special character)
        if (strlen($newPassword) < 8) {
            $msg = "⚠️ Password must be at least 8 characters long!";
            $showForm = true;
        } elseif (!preg_match("/[A-Z]/", $newPassword)) {
            $msg = "⚠️ Password must contain at least one uppercase letter!";
            $showForm = true;
        } elseif (!preg_match("/[a-z]/", $newPassword)) {
            $msg = "⚠️ Password must contain at least one lowercase letter!";
            $showForm = true;
        } elseif (!preg_match("/[0-9]/", $newPassword)) {
            $msg = "⚠️ Password must contain at least one number!";
            $showForm = true;
        } elseif (!preg_match("/[\W_]/", $newPassword)) { // underscore included
            $msg = "⚠️ Password must contain at least one special character!";
            $showForm = true;
        } elseif ($newPassword !== $confirmPassword) {
            $msg = "❌ Passwords do not match!";
            $showForm = true;
        } else {
            // Hash password and update DB
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL WHERE reset_token=?");
            $stmt->bind_param("ss", $hashedPassword, $token);
            $stmt->execute();

            $msg = "✅ Password reset successfully! You can now <a href='login.php'>login</a>.";
            $showForm = false;
        }
    } else {
        $msg = "❌ Invalid or expired token!";
        $showForm = false;
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
body { display: flex; flex-direction: column; min-height: 100vh; }
.main-content { flex-grow: 1; }
.form-container { flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px; }

.simple-footer { margin-top: auto; background-color:#0d0d0d; color:#fff; padding:20px 0; text-align:center; font-family:'Poppins', sans-serif; font-size:14px; width:100%; position: relative; }
.simple-footer::before { content:''; position:absolute; top:0; left:0; width:100%; height:2px; background-color:#FF4500; }

.nav-logo { color: #FF4500; font-size: 32px; font-weight: 700; text-shadow: 3px 3px 6px #000; margin-right: auto; }
.nav-menu { display: flex; list-style: none; gap: 2rem; }
.nav-menu a { color: #fff; text-decoration: none; font-size: 18px; font-weight: 400; position: relative; }
.nav-menu a::after { content: ''; position: absolute; bottom: -5px; left: 0; width: 0; height: 2px; background: #FF4500; transition: width 0.3s; }
.nav-menu a:hover, .nav-menu a.active { color: #FF4500; }
.nav-menu a:hover::after, .nav-menu a.active::after { width: 100%; }

.msg { margin-bottom: 15px; color: red; text-align: center; }
form input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; }
form button { background-color: #FF4500; color: #fff; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; }
form button:hover { background-color: #e03e00; }
.back-login { margin-top: 15px; font-size: 14px; text-align: center; }
.back-login a { color: #FF4500; text-decoration: none; }
.back-login a:hover { text-decoration: underline; }

/* Password rules box */
.password-rules { 
    background-color: #fff3cd; 
    border: 1px solid #ffeeba; 
    padding: 10px; 
    border-radius: 6px; 
    color: #856404; 
    font-size: 14px; 
    margin-bottom: 15px;
    line-height: 1.4;
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
            <div class="password-rules">
                <strong>Password must contain:</strong>
                <ul>
                    <li>At least 8 characters</li>
                    <li>At least 1 uppercase letter</li>
                    <li>At least 1 lowercase letter</li>
                    <li>At least 1 number</li>
                    <li>At least 1 special character (including _)</li>
                </ul>
            </div>

            <form action="reset_password.php" method="POST" id="resetForm">
                <input type="password" name="password" id="password" placeholder="New Password" required>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
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

<!-- Optional JS: Live password feedback -->
<script>
const passwordInput = document.getElementById('password');
const resetForm = document.getElementById('resetForm');

passwordInput.addEventListener('input', () => {
    const pwd = passwordInput.value;
    let msg = '';

    if(pwd.length < 8) msg += '• At least 8 characters\n';
    if(!/[A-Z]/.test(pwd)) msg += '• At least 1 uppercase letter\n';
    if(!/[a-z]/.test(pwd)) msg += '• At least 1 lowercase letter\n';
    if(!/[0-9]/.test(pwd)) msg += '• At least 1 number\n';
    if(!/[\W_]/.test(pwd)) msg += '• At least 1 special character\n';

    passwordInput.setCustomValidity(msg);
});
</script>
</body>
</html>
