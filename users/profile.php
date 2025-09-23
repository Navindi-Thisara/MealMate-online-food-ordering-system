<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Corrected include path for db_connect.php
include '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$msg_profile = $msg_password = "";

// --- Handle Profile Update ---
if (isset($_POST['update_profile'])) {
    $user_id    = $_SESSION['user_id'];
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $contact_no = trim($_POST['contact_no']);
    $address    = trim($_POST['address']);

    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, contact_no=?, address=? WHERE user_id=?");
    $stmt->bind_param("ssssi", $full_name, $email, $contact_no, $address, $user_id);

    if ($stmt->execute()) {
        $msg_profile = "✅ Profile updated successfully!";
        // Update session variables after successful profile update
        $_SESSION['full_name'] = $full_name;
    } else {
        $msg_profile = "❌ Error updating profile: " . $conn->error;
    }
}

// --- Handle Password Change ---
if (isset($_POST['change_password'])) {
    $user_id          = $_SESSION['user_id'];
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
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email, contact_no, address, role, created_at FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$is_admin = $user['role'] === 'admin';

$base_path = '/MealMate-online-food-ordering-system';
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'view_profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Profile - MealMate</title>
<link rel="stylesheet" href="../assets/form.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
/* === Global Styles === */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    color: #fff;
    scroll-behavior: smooth;
    background-color: #0d0d0d;
    overflow-x: hidden;
    position: relative;
}

/* === Navbar Styles === */
.navbar {
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(10px);
    border-bottom: 2px solid #FF4500;
    padding: 20px 50px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
    z-index: 20;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-container {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-logo {
    color: #FF4500;
    font-size: 32px;
    font-weight: 700;
    margin: 0;
    text-shadow: 3px 3px 6px #000;
}

.nav-menu {
    display: flex;
    list-style: none;
    gap: 2rem;
    align-items: center;
}

.nav-menu a {
    color: #fff;
    text-decoration: none;
    font-size: 18px;
    font-weight: 400;
    letter-spacing: 0.5px;
    padding: 0;
    position: relative;
    transition: color 0.3s ease;
}

.nav-menu a::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 0;
    height: 2px;
    background: #FF4500;
    transition: width 0.3s ease;
}

.nav-menu a:hover,
.nav-menu a.active {
    color: #FF4500;
}

.nav-menu a:hover::after,
.nav-menu a.active::after {
    width: 100%;
}


/* === Tabs Styling === */
.tabs {
    display: flex;
    justify-content: center;
    margin-top: 120px; /* Adjusted to be below the fixed header */
    margin-bottom: 20px;
    flex-wrap: wrap;
    z-index: 1;
}
.tab {
    padding: 10px 25px;
    background: #222;
    border-radius: 8px;
    margin: 0 5px;
    color: #ff4500;
    font-weight: bold;
    transition: 0.3s;
    cursor: pointer;
    border: 2px solid transparent;
}
.tab:hover { background: #ff4500; color: #000; }
.tab.active {
    background: #ff4500;
    color: #000;
    border-color: #ff4500;
}

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
.tab-content button { background: #ff4500; color: #000; cursor: pointer; font-weight: bold; }
.tab-content button:hover { background: #e65c00; }

/* === Responsive === */
@media (max-width: 768px) {
    .navbar {
        padding: 15px 20px;
    }
    .tabs {
        margin-top: 100px;
    }
}
@media (max-width: 480px) {
    .navbar {
        padding: 10px 1rem;
    }
    .nav-logo {
        font-size: 24px;
    }
    .nav-menu {
        gap: 1rem;
    }
    .nav-menu a {
        font-size: 12px;
    }
}
</style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">MealMate</h1>
            <ul class="nav-menu">
                <li><a href="<?php echo $base_path; ?>/index.php">Home</a></li>
                <?php if ($is_admin): ?>
                    <!-- Admin Navigation -->
                    <li><a href="<?php echo $base_path; ?>/users/admin/admin_dashboard.php">Dashboard</a></li>
                    <li><a href="profile.php" class="active">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <!-- User Navigation -->
                    <li><a href="<?php echo $base_path; ?>/food_management/menu.php">Menu</a></li>
                    <li><a href="<?php echo $base_path; ?>/cart/cart.php">Cart</a></li>
                    <li><a href="profile.php" class="active">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- === Tabs === -->
    <div class="tabs">
        <div class="tab <?= ($current_tab == 'view_profile') ? 'active' : '' ?>" data-tab="view_profile">View Profile</div>
        <div class="tab <?= ($current_tab == 'edit_profile') ? 'active' : '' ?>" data-tab="edit_profile">Edit Profile</div>
        <div class="tab <?= ($current_tab == 'change_password') ? 'active' : '' ?>" data-tab="change_password">Change Password</div>
    </div>

    <!-- === Tab Contents (Cards) === -->
    <div id="view_profile" class="tab-content <?= ($current_tab == 'view_profile') ? 'active' : '' ?>">
        <h2>Your Profile</h2>
        <p><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Contact No:</strong> <?= htmlspecialchars($user['contact_no']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
        <p><strong>Joined On:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
    </div>

    <div id="edit_profile" class="tab-content <?= ($current_tab == 'edit_profile') ? 'active' : '' ?>">
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

    <div id="change_password" class="tab-content <?= ($current_tab == 'change_password') ? 'active' : '' ?>">
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
        const tabs = document.querySelectorAll('.tab');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab.dataset.tab);
                window.history.pushState({}, '', url);

                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                tab.classList.add('active');
                document.getElementById(tab.dataset.tab).classList.add('active');
            });
        });
    </script>
</body>
</html>