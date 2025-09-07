<?php
session_start();
include '../includes/db_connect.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $contact_no = trim($_POST['contact_no']);
    $address    = trim($_POST['address']);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - MealMate</title>
    <link rel="stylesheet" href="../assets/form.css?v=1">
</head>
<body>
    <!-- Header -->
    <header>
        <h1>MealMate</h1>
        <nav>
            <a href="../index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="../food_management/menu.php">Menu</a>
            <a href="../cart/cart.php">Cart</a>
        </nav>
    </header>

    <!-- Registration Form -->
    <div class="form-container">
        <h2>User Registration</h2>
        <?php if ($msg != ""): ?>
            <div class="msg"><?= $msg ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="contact_no" placeholder="Contact Number" required>
            <textarea name="address" placeholder="Enter Address" required></textarea>
            <button type="submit">Register</button>
        </form>

        <p class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>

    <!-- Footer -->
    <footer>
        &copy; <?= date('Y') ?> MealMate. All rights reserved.
    </footer>
</body>
</html>
