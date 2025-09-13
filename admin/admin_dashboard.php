<?php
session_start();
include '../includes/db_connect.php';

// --- Admin Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<h2 style='color:red;text-align:center;margin-top:50px;'>❌ Access Denied: Admins only!</h2>";
    exit();
}

// --- Messages ---
$msg = "";

// --- Handle Deletion ---
if (isset($_GET['delete_user_id'])) {
    $uid = intval($_GET['delete_user_id']);
    if ($uid != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
        $stmt->bind_param("i", $uid);
        if ($stmt->execute()) {
            $msg = "✅ User deleted successfully!";
        } else {
            $msg = "❌ Error deleting user: " . $conn->error;
        }
    } else {
        $msg = "❌ Cannot delete your own account!";
    }
}

// --- Handle Edit ---
if (isset($_POST['edit_user'])) {
    $uid = intval($_POST['user_id']);
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE email=? AND user_id<>?");
    $stmt_check->bind_param("si", $email, $uid);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $msg = "❌ Email already exists!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, role=? WHERE user_id=?");
        $stmt->bind_param("sssi", $name, $email, $role, $uid);
        if ($stmt->execute()) {
            $msg = "✅ User updated successfully!";
        } else {
            $msg = "❌ Error updating user: " . $conn->error;
        }
    }
}

// --- Fetch All Users ---
$users = $conn->query("SELECT * FROM users ORDER BY user_id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | MealMate</title>
<link rel="stylesheet" href="../assets/form.css">
<style>
/* Background with overlay */
body {
    font-family: Arial, sans-serif;
    margin:0; padding:0;
    color:#fff;
    background-size: cover;
    position: relative;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
body::before {
    content: "";
    position: fixed;
    top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.7);
    z-index: -1;
}

header, footer { 
    background: rgba(0,0,0,0.9); 
    color: #FF4500; 
    padding: 15px 20px; 
    text-align: center; 
}
header a, footer a { color: #fff; margin: 0 10px; text-decoration: none; }
header a:hover, footer a:hover { color: #FF6F00; }

.container { 
    flex: 1;
    width: 90%; max-width: 1200px; 
    margin: 20px auto; 
    background: rgba(20,20,20,0.9); 
    padding: 25px; 
    border-radius: 12px; 
    border: 2px solid #FF4500; 
    box-shadow: 0 0 20px rgba(255,69,0,0.6); 
    z-index: 1;
    position: relative;
    overflow-y: auto;  /* Scroll bar */
    max-height: 70vh;  /* Keep box height fixed with scroll */
}

.msg { text-align:center; margin-bottom:20px; font-size:15px; color:#ffcc80; }

/* Tabs */
.tabs { display:flex; border-bottom:2px solid #FF4500; margin-bottom:20px; cursor:pointer; }
.tab { padding:10px 20px; margin-right:5px; background:#222; border-radius:8px 8px 0 0; color:#fff; font-weight:bold; }
.tab.active { background:#FF4500; color:#000; }
.tab-content { display:none; }
.tab-content.active { display:block; }

/* Table */
table { width:100%; border-collapse: collapse; margin-top:10px; }
th, td { padding:12px; text-align:left; border-bottom:1px solid #555; }
th { background:#FF4500; color:#000; }
td { background:#2b2b2b; color:#fff; }
tr:hover td { background:#444; }

/* Inline Edit Form */
.inline-form { display:flex; flex-wrap:wrap; gap:5px; align-items:center; }
.inline-form input, .inline-form select { padding:4px 6px; border-radius:4px; background:#333; color:#fff; border:1px solid #FF4500; }
.inline-form button { background:#ff4500; color:#fff; padding:5px 10px; border:none; border-radius:4px; cursor:pointer; }
.inline-form button:hover { background:#e63e00; }

/* Delete Button */
.delete-btn { background:#ff4500; color:#fff; padding:5px 10px; border-radius:4px; text-decoration:none; margin-left:5px; display:inline-block; }
.delete-btn:hover { background:#e63e00; }

/* Footer fixed bottom */
footer {
    font-size: 13px;
    margin-top: auto;
}
@media(max-width:800px){
    table, th, td { font-size:14px; }
    .inline-form { flex-direction:column; align-items:flex-start; }
}
</style>
<script>
function showTab(tabId){
    let tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(t => t.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');

    let tabButtons = document.querySelectorAll('.tab');
    tabButtons.forEach(b => b.classList.remove('active'));
    document.getElementById(tabId+'-btn').classList.add('active');
}
window.onload = function(){ showTab('users'); }
</script>
</head>
<body>

<header>
<h1>MealMate</h1>
<nav>
<a href="../index.php">Home</a>
<a href="admin_dashboard.php">Dashboard</a>
<a href="logout.php">Logout</a>
</nav>
</header>

<div class="container">
<h2 style="text-align:center;">Admin Dashboard</h2>
<?php if($msg!="") echo "<div class='msg'>$msg</div>"; ?>

<div class="tabs">
    <div class="tab active" id="users-btn" onclick="showTab('users')">Users</div>
    <div class="tab" id="food-btn" onclick="showTab('food')">Food</div>
    <div class="tab" id="orders-btn" onclick="showTab('orders')">Orders</div>
    <div class="tab" id="cart-btn" onclick="showTab('cart')">Cart</div>
</div>

<!-- Users Tab -->
<div class="tab-content" id="users">
<?php if($users->num_rows > 0): ?>
<table>
    <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th>
    </tr>
    <?php while($user = $users->fetch_assoc()): ?>
    <tr>
        <td><?= $user['user_id'] ?></td>
        <td><?= htmlspecialchars($user['full_name']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= $user['role'] ?></td>
        <td><?= $user['created_at'] ?></td>
        <td>
            <?php if($user['user_id'] != $_SESSION['user_id']): ?>
            <form method="POST" class="inline-form">
                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                <select name="role">
                    <option value="customer" <?= $user['role']=='customer'?'selected':'' ?>>Customer</option>
                    <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                </select>
                <button type="submit" name="edit_user">Update</button>
            </form>
            <a class="delete-btn" href="?delete_user_id=<?= $user['user_id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
            <?php else: ?>
            <span style="color:#ccc;">You</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
<p style="text-align:center;">No users found.</p>
<?php endif; ?>
</div>

<!-- Placeholder Tabs -->
<div class="tab-content" id="food"><p style="text-align:center;">Food management coming soon.</p></div>
<div class="tab-content" id="orders"><p style="text-align:center;">Orders management coming soon.</p></div>
<div class="tab-content" id="cart"><p style="text-align:center;">Cart management coming soon.</p></div>

</div>

<footer>
<p>&copy; <?= date("Y") ?> MealMate Online Food Ordering System</p>
</footer>

</body>
</html>
