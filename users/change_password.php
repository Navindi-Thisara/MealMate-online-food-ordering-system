<?php
session_start();
include '../includes/db_connect.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch user's current hashed password
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($current_password, $user['password'])) {
        $msg = "❌ Current password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $msg = "❌ New password and confirm password do not match!";
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update->bind_param("si", $hashed_password, $user_id);

        if ($update->execute()) {
            $msg = "✅ Password changed successfully!";
        } else {
            $msg = "❌ Error updating password: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password - MealMate</title>
    <link rel="stylesheet" href="../assets/form.css">
    <style>
        .form-container {
            border: 2px solid #FF4500;
            box-shadow: 0 4px 12px rgba(255,69,0,0.5);
        }
        .msg {
            margin-bottom: 15px;
            font-size: 14px;
            color: #ffcc80;
        }
    </style>
</head>
<body>
<header>
    <h1>MealMate</h1>
    <nav>
        <a href="../index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="edit_user.php">Edit Profile</a>
        <a href="change_password.php">Change Password</a>
        <a href="../cart/cart.php">Cart</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="form-container">
    <h2>Change Password</h2>

    <?php if ($msg != ""): ?>
        <div class="msg"><?= $msg ?></div>
    <?php endif; ?>

    <form action="change_password.php" method="POST">
        <input type="password" name="current_password" placeholder="Current Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit">Update Password</button>
    </form>
</div>

<footer>
    &copy; <?= date('Y') ?> MealMate. All rights reserved.
</footer>
</body>
</html>