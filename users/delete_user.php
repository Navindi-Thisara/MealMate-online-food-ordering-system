<?php
session_start();
include '../includes/db_connect.php';

// --- Admin Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<h2 style='color:red;text-align:center;margin-top:50px;'>❌ Access Denied: Admins only!</h2>";
    exit();
}

$msg = "";

// --- Handle Deletion ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    if ($delete_id == $_SESSION['user_id']) {
        $msg = "❌ You cannot delete your own account!";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: delete_user.php?msg=User+deleted+successfully!");
            exit();
        } else {
            $msg = "❌ Error deleting user: " . $conn->error;
        }
    }
}

if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
}

// --- Fetch Users ---
$result = $conn->query("SELECT user_id, full_name, email, role, created_at FROM users ORDER BY user_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Manage Users | MealMate</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #1a1a1a;
    color: #fff;
    margin: 0; padding: 0;
}
header {
    background: #000;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #FF6F00;
}
header h1 { color: #FF6F00; margin:0; font-size:28px; }
nav a { color: #fff; margin-left: 20px; text-decoration: none; font-weight: bold; }
nav a:hover { color: #ffb84d; }

.container {
    width: 95%;
    max-width: 1000px;
    margin: 40px auto;
    padding: 20px;
    background: #222;
    border-radius: 12px;
    box-shadow: 0 6px 25px rgba(255,69,0,0.5);
}

h2 { text-align:center; color: #ffb84d; margin-bottom:25px; }
.msg { text-align:center; color: #ffcc80; font-size:16px; margin-bottom:20px; }

/* User Card */
.user-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #333;
    padding: 20px;
    margin-bottom: 15px;
    border: 2px solid #FF6F00;
    border-radius: 10px;
    transition: transform 0.2s;
}
.user-card:hover { transform: scale(1.02); }
.user-info p { margin:4px 0; }
.user-info p strong { color:#FF6F00; }

.delete-btn {
    background: #ff4500;
    color: #fff;
    padding: 10px 18px;
    border-radius: 6px;
    font-weight: bold;
    text-decoration: none;
}
.delete-btn:hover { background: #e63e00; }

/* Responsive */
@media(max-width: 700px){
    .user-card { flex-direction: column; align-items: flex-start; }
    .delete-btn { width: 100%; margin-top:10px; text-align:center; }
}
</style>
</head>
<body>

<header>
<h1>MealMate Admin</h1>
<nav>
<a href="../index.php">Home</a>
<a href="dashboard.php">Dashboard</a>
<a href="logout.php">Logout</a>
</nav>
</header>

<div class="container">
<h2>Manage Users</h2>

<?php if($msg != ""): ?>
    <div class="msg"><?= $msg ?></div>
<?php endif; ?>

<?php if($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="user-card">
            <div class="user-info">
                <p><strong>ID:</strong> <?= htmlspecialchars($row['user_id']) ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($row['full_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
                <p><strong>Role:</strong> <?= htmlspecialchars($row['role']) ?></p>
                <p><strong>Joined:</strong> <?= htmlspecialchars($row['created_at']) ?></p>
            </div>
            <div>
                <?php if($row['user_id'] != $_SESSION['user_id']): ?>
                    <a class="delete-btn" href="delete_user.php?delete_id=<?= $row['user_id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                <?php else: ?>
                    <span style="color:#ccc;">N/A</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="text-align:center;">No users found.</p>
<?php endif; ?>

</div>
</body>
</html>
