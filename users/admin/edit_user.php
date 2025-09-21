<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection
require_once __DIR__ . '/../../includes/db_connect.php';

// Restrict to admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit();
}

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$user_id = intval($_GET['id']);
$error = "";
$success = "";

// Fetch user data
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_users.php");
    exit();
}

$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $contact_no = trim($_POST['contact_no']);
    $address    = trim($_POST['address']);
    $role       = $_POST['role'];

    // Basic validation
    if (empty($full_name) || empty($email) || empty($contact_no) || empty($address)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Update user in DB
        $update_sql = "UPDATE users SET full_name = ?, email = ?, contact_no = ?, address = ?, role = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssssi", $full_name, $email, $contact_no, $address, $role, $user_id);

        if ($update_stmt->execute()) {
            $success = "User updated successfully!";
            // Refresh user data
            $user['full_name'] = $full_name;
            $user['email'] = $email;
            $user['contact_no'] = $contact_no;
            $user['address'] = $address;
            $user['role'] = $role;
        } else {
            $error = "Failed to update user. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - MealMate Admin</title>
    <link rel="stylesheet" href="../assets/form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body.edit-user {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #000;
            color: #fff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

           /* Navbar */
        .navbar {
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #FF4500;
            padding: 20px 50px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 20;
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

        /* Main container */
        .container {
            width: 100%;
            max-width: 1400px;
            margin: 120px auto 0 auto;
            padding: 0 50px;
            flex: 1 0 auto;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .header h2 {
            color: #ff4500;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #ccc;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .header::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            width: 100vw;
            height: 2px;
            background-color: #ff4500;
            margin-left: calc(-50vw + 50%);
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 120px auto 50px auto;
            padding: 20px;
            background: rgba(20, 20, 20, 0.95);
            border: 2px solid #FF4500;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(255, 69, 0, 0.5);
            flex: 1 0 auto;
        }

        h2 {
            text-align: center;
            color: #ff4500;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input, select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #FF4500;
            background: #111;
            color: #fff;
            font-size: 14px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #ff4500;
            box-shadow: 0 0 8px #ff4500;
        }

        button {
            padding: 12px;
            background: #FF4500;
            color: #000;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #e63e00;
        }

        .message {
            text-align: center;
            font-weight: bold;
        }

        .message.error {
            color: #F44336;
        }

        .message.success {
            color: #4CAF50;
        }

        a.back-link {
            display: inline-block;
            margin-top: 10px;
            color: #FF4500;
            text-decoration: none;
        }

        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="edit-user">
    <!-- Navbar/Header -->
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">MealMate</h1>
            <ul class="nav-menu">
                <li><a href="/MealMate-online-food-ordering-system/index.php">Home</a></li>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="edit_user.php" class="active">Edit Users</a></li>
                <li><a href="/MealMate-online-food-ordering-system/users/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Form Container -->
    <div class="container">
        <h2>Edit User</h2>

        <?php if($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php elseif($success): ?>
            <div class="message success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>" required>
            <input type="text" name="contact_no" placeholder="Contact Number" value="<?= htmlspecialchars($user['contact_no']) ?>" required>
            <input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($user['address']) ?>" required>
            <select name="role" required>
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <button type="submit">Update User</button>
        </form>

        <a href="manage_users.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Manage Users</a>
    </div>

    <!-- Footer -->
    <?php include '../../includes/simple_footer.php'; ?>
</body>
</html>