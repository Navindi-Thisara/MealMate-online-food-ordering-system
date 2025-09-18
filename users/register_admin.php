<?php
session_start();
require_once('../includes/db_connect.php');

$msg = "";
$redirect_to_login = false; // Flag to control redirection

// Simple security check to prevent public access
// This can be a secret password or key known only to you
$secret_key = "1234";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['secret_key']) && $_POST['secret_key'] === $secret_key) {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $contact_no = trim($_POST['contact_no']);
        $address = trim($_POST['address']);

        // Set role to 'admin' for this registration
        $role = 'admin';

        if (!empty($full_name) && !empty($email) && !empty($password) && !empty($contact_no) && !empty($address)) {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $msg = "❌ This email is already registered!";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new admin user into database
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, contact_no, address, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $full_name, $email, $hashed_password, $contact_no, $address, $role);

                if ($stmt->execute()) {
                    $msg = "✅ Admin registration successful! You will be redirected to the login page shortly.";
                    $redirect_to_login = true;
                } else {
                    $msg = "❌ Error: " . $stmt->error;
                }
            }
            $stmt->close();
        } else {
            $msg = "⚠️ Please fill all fields!";
        }
    } else {
        $msg = "❌ Invalid secret key. Access denied!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration</title>
    <link rel="stylesheet" href="../assets/form.css?v=1">
    <link rel="stylesheet" href="../assets/style.css">
    <?php if ($redirect_to_login): ?>
        <meta http-equiv="refresh" content="3;url=login.php">
    <?php endif; ?>
</head>
<body>
    <header>
        <h1 class="nav-logo">MealMate</h1>
        <nav class="nav-menu">
            <a href="../index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </header>

    <div class="form-container">
        <h2>Admin Registration</h2>

        <?php if ($msg != ""): ?>
            <div class="msg"><?= $msg ?></div>
        <?php endif; ?>

        <?php if (!$redirect_to_login): ?>
            <form action="register_admin.php" method="POST">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="contact_no" placeholder="Contact Number" required>
                <textarea name="address" placeholder="Enter Address" required></textarea>
                <input type="password" name="secret_key" placeholder="Admin Secret Key" required>

                <button type="submit">Register as Admin</button>
            </form>

            <p class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        <?php endif; ?>
    </div>

    <footer>
        &copy; <?= date('Y'); ?> MealMate. All rights reserved.
    </footer>
</body>
</html>