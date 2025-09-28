<?php
session_start();
require_once('../includes/db_connect.php');
require '../vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

            // Store token in DB (ensure column `reset_token` exists)
            $stmt2 = $conn->prepare("UPDATE users SET reset_token=? WHERE email=?");
            $stmt2->bind_param("ss", $token, $email);
            $stmt2->execute();

            // Reset link (update to your localhost or domain)
            $resetLink = "http://localhost/MealMate-online-food-ordering-system/users/reset_password.php?token=$token";

            // Send email using PHPMailer + Mailtrap
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'sandbox.smtp.mailtrap.io';
                $mail->SMTPAuth   = true;
                $mail->Port       = 2525;
                $mail->Username   = 'fead2ad7782a4b'; // Your Mailtrap username
                $mail->Password   = '159e7fe69f8d04'; // Your Mailtrap password

                $mail->setFrom('no-reply@mealmate.com', 'MealMate');
                $mail->addAddress($email, $user['full_name']);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "
                    <h3>Password Reset Request</h3>
                    <p>Hello {$user['full_name']},</p>
                    <p>We received a request to reset your password.</p>
                    <p>Click the link below to reset it:</p>
                    <p><a href='$resetLink'>$resetLink</a></p>
                    <br>
                    <p><strong>Password Requirements:</strong></p>
                    <ul>
                        <li>At least 8 characters</li>
                        <li>At least 1 uppercase letter</li>
                        <li>At least 1 lowercase letter</li>
                        <li>At least 1 number</li>
                        <li>At least 1 special character (including _)</li>
                    </ul>
                    <p>If you did not request this, please ignore this email.</p>
                ";

                $mail->send();
                $msg = "✅ Password reset link has been sent (check your Mailtrap inbox).";
            } catch (Exception $e) {
                $msg = "❌ Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

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
</head>
<body>
<div class="main-content" style="display:flex; flex-direction:column; min-height:100vh;">
    <header>
        <h1 class="nav-logo">MealMate</h1>
        <nav class="nav-menu">
            <a href="../index.php">Home</a>
            <a href="register.php">Register</a>
            <a href="../food_management/menu.php">Menu</a>
            <a href="../cart/cart.php">Cart</a>
        </nav>
    </header>

    <div class="form-container" style="flex:1; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:20px;">
        <h2>Forgot Password</h2>

        <?php if ($msg != ""): ?>
            <div class="msg"><?= $msg ?></div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST" style="width:100%; max-width:400px;">
            <input type="email" name="email" placeholder="Enter your registered email" required>
            
            <!-- Tooltip / Password guide -->
            <small style="display:block; margin-bottom:10px; color:#555;">
                Note: The password you set after resetting must have at least 8 characters, including uppercase, lowercase, number, and special character (e.g., !@#$%^&* or _).
            </small>
            
            <button type="submit">Send Reset Link</button>

            <!-- Back to login slightly below the button -->
            <div class="back-login" style="margin-top:20px; text-align:center;">
                <a href="login.php" style="color:#FF4500;">Back to Login</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/simple_footer.php'; ?>
</body>
</html>
