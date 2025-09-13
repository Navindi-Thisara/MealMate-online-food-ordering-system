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

// Fetch current user info
$stmt = $conn->prepare("SELECT full_name, email, contact_no, address FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found!";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name  = trim($_POST['full_name']);
    $contact_no = trim($_POST['contact_no']);
    $address    = trim($_POST['address']);

    $update = $conn->prepare("UPDATE users SET full_name = ?, contact_no = ?, address = ? WHERE user_id = ?");
    $update->bind_param("sssi", $full_name, $contact_no, $address, $user_id);

    if ($update->execute()) {
        $msg = "✅ Profile updated successfully!";
        // Update local $user array to reflect changes
        $user['full_name'] = $full_name;
        $user['contact_no'] = $contact_no;
        $user['address'] = $address;
    } else {
        $msg = "❌ Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - MealMate</title>
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
        <a href="../cart/cart.php">Cart</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="form-container">
    <h2>Edit Profile</h2>

    <?php if ($msg != ""): ?>
        <div class="msg"><?= $msg ?></div>
    <?php endif; ?>

    <form action="edit_user.php" method="POST">
        <input type="text" name="full_name" placeholder="Full Name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        <input type="text" name="contact_no" placeholder="Contact Number" value="<?= htmlspecialchars($user['contact_no']) ?>" required>
        <textarea name="address" placeholder="Address" required><?= htmlspecialchars($user['address']) ?></textarea>
        <button type="submit">Update Profile</button>
    </form>
</div>

<footer>
    &copy; <?= date('Y') ?> MealMate. All rights reserved.
</footer>
</body>
</html>
