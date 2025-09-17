<?php
session_start();
include '../includes/db_connect.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT full_name, email, contact_no, address, role, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found!";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile - MealMate</title>
    <link rel="stylesheet" href="../assets/form.css">
    <style>
        /* Profile box specific styling */
        .profile-box {
            border: 2px solid #FF4500; /* Orange border */
            box-shadow: 0 4px 12px rgba(255,69,0,0.5);
        }
        .profile-box p {
            text-align: left;
            margin: 8px 0;
        }
        .profile-box a.edit-btn {
            display: inline-block;
            margin-top: 12px;
            padding: 8px 16px;
            background: #ff6f00;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
        }
        .profile-box a.edit-btn:hover {
            background: #e65c00;
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
        <a href="../cart/cart.php">Cart</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="form-container profile-box">
    <h2>Your Profile</h2>
    <p><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Contact No:</strong> <?= htmlspecialchars($user['contact_no']) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
    <p><strong>Joined On:</strong> <?= htmlspecialchars($user['created_at']) ?></p>

    <a href="edit_user.php" class="edit-btn">Edit Profile</a>
</div>

<footer>
    &copy; <?= date('Y') ?> MealMate. All rights reserved.
</footer>
</body>
</html>