<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data to check role
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Redirect admin users to the admin dashboard
if ($user && $user['role'] === 'admin') {
    header("Location: ../admin/admin_dashboard.php");
    exit();
}

$msg_profile = $msg_password = "";

// --- Handle Profile Update ---
if (isset($_POST['update_profile'])) {
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $contact_no = trim($_POST['contact_no']);
    $address    = trim($_POST['address']);

    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, contact_no=?, address=? WHERE user_id=?");
    $stmt->bind_param("ssssi", $full_name, $email, $contact_no, $address, $user_id);

    if ($stmt->execute()) {
        $msg_profile = "✅ Profile updated successfully!";
    } else {
        $msg_profile = "❌ Error updating profile: " . $conn->error;
    }
}

// --- Handle Password Change ---
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_pass = $result->fetch_assoc();

    if (!$user_pass || !password_verify($current_password, $user_pass['password'])) {
        $msg_password = "❌ Current password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $msg_password = "❌ New password and confirm password do not match!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt_update = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $stmt_update->bind_param("si", $hashed_password, $user_id);

        if ($stmt_update->execute()) {
            $msg_password = "✅ Password changed successfully!";
        } else {
            $msg_password = "❌ Error updating password: " . $conn->error;
        }
    }
}

// Fetch user data again, as it might have been updated
$stmt = $conn->prepare("SELECT full_name, email, contact_no, address, role, created_at FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard - MealMate</title>
<link rel="stylesheet" href="../assets/form.css">
<link rel="stylesheet" href="../assets/style.css">
<style>
/* === Tabs Styling === */
.tabs {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    z-index: 20;
}
.tab {
    padding: 10px 25px;
    background: #222;
    border-radius: 8px 8px 0 0;
    margin: 0 5px;
    color: #ff6f00;
    font-weight: bold;
    transition: 0.3s;
    cursor: pointer;
}
.tab:hover { background: #ff6f00; color: #000; }
.tab.active { background: #ff6f00; color: #000; }

/* === Card Styling === */
.tab-content {
    display: none;
    background: rgba(20,20,20,0.95);
    padding: 25px;
    border-radius: 12px;
    border: 2px solid #FF4500; /* Orange border */
    box-shadow: 0 4px 20px rgba(255,69,0,0.5);
    width: 400px;
    max-width: 90%;
    margin: 10px auto 50px auto;
    position: relative;
    z-index: 15;
    transition: transform 0.3s, box-shadow 0.3s;
}
.tab-content.active { display: block; }
.tab-content:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 25px rgba(255,69,0,0.7);
}

/* === Form Inputs === */
.tab-content input,
.tab-content textarea,
.tab-content button {
    width: 95%;
    padding: 10px;
    margin: 8px 0;
    border-radius: 6px;
    border: none;
    font-size: 14px;
}
.tab-content input, .tab-content textarea { background: rgba(255,255,255,0.1); color: #fff; }
.tab-content button { background: #ff6f00; color: #000; cursor: pointer; font-weight: bold; }
.tab-content button:hover { background: #e65c00; }

/* === Messages === */
.msg { margin-bottom: 15px; font-size: 14px; color: #ffcc80; }

/* === Responsive === */
@media (max-width: 480px) {
    .tabs { flex-direction: column; }
    .tab { margin: 5px 0; text-align: center; }
}
</style>
</head>
<body>
<header>
<h1 class="nav-logo">MealMate</h1>
<nav class="nav-menu">
<a href="../index.php">Home</a>
<a href="dashboard.php">Dashboard</a>
<a href="../cart/cart.php">Cart</a>
<a href="logout.php">Logout</a>
</nav>
</header>

<!-- === Tabs === -->
<div class="tabs">
<div class="tab active" data-tab="view_profile">View Profile</div>
<div class="tab" data-tab="edit_profile">Edit Profile</div>
<div class="tab" data-tab="change_password">Change Password</div>
</div>

<!-- === Tab Contents (Cards) === -->
<div id="view_profile" class="tab-content active">
<h2>Your Profile</h2>
<p><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
<p><strong>Contact No:</strong> <?= htmlspecialchars($user['contact_no']) ?></p>
<p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
<p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
<p><strong>Joined On:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
</div>

<div id="edit_profile" class="tab-content">
<h2>Edit Profile</h2>
<?php if ($msg_profile != ""): ?><div class="msg"><?= $msg_profile ?></div><?php endif; ?>
<form method="POST">
<input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
<input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
<input type="text" name="contact_no" value="<?= htmlspecialchars($user['contact_no']) ?>" required>
<textarea name="address" required><?= htmlspecialchars($user['address']) ?></textarea>
<button type="submit" name="update_profile">Update Profile</button>
</form>
</div>

<div id="change_password" class="tab-content">
<h2>Change Password</h2>
<?php if ($msg_password != ""): ?><div class="msg"><?= $msg_password ?></div><?php endif; ?>
<form method="POST">
<input type="password" name="current_password" placeholder="Current Password" required>
<input type="password" name="new_password" placeholder="New Password" required>
<input type="password" name="confirm_password" placeholder="Confirm New Password" required>
<button type="submit" name="change_password">Update Password</button>
</form>
</div>

<footer>
&copy; <?= date('Y') ?> MealMate. All rights reserved.
</footer>

<script>
// Tab switching logic
window.onload = function() {
    const tabs = document.querySelectorAll('.tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });
};
</script>
</body>
</html>
