<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Corrected include path to go up two directories
include '../../includes/db_connect.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit();
}

$base_path = '/MealMate-online-food-ordering-system';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - MealMate</title>
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

    display: flex;
    flex-direction: column;
    min-height: 100vh; /* Full viewport height */
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

/* === Content Styling === */
.dashboard-container {
    padding-top: 150px; /* Adjusted to be below the fixed header */
    text-align: center;
    flex: 1; /* Pushes footer down */
}

.dashboard-card {
    background: rgba(20,20,20,0.95);
    padding: 40px;
    border-radius: 12px;
    border: 2px solid #FF4500;
    box-shadow: 0 4px 20px rgba(255,69,0,0.5);
    width: 600px;
    max-width: 90%;
    margin: 50px auto;
    transition: transform 0.3s, box-shadow 0.3s;
}

.dashboard-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 25px rgba(255,69,0,0.7);
}

.dashboard-card h2 {
    color: #fff;
    font-size: 2.5em;
    margin-bottom: 10px;
}

.dashboard-card p {
    color: #aaa;
    font-size: 1.2em;
    margin-bottom: 20px;
}

.dashboard-card .quick-links {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
}

.dashboard-card .quick-links a {
    text-decoration: none;
    background: #ff4500;
    color: #000;
    font-weight: bold;
    padding: 12px 25px;
    border-radius: 8px;
    transition: background 0.3s, transform 0.3s;
}

.dashboard-card .quick-links a:hover {
    background: #e65c00;
    transform: translateY(-2px);
}

/* === Responsive Design === */
@media (max-width: 768px) {
    .navbar {
        padding: 15px 20px;
    }
    .nav-logo {
        font-size: 24px;
    }
    .nav-menu {
        gap: 1.5rem;
    }
    .nav-menu a {
        font-size: 16px;
    }
    .dashboard-container {
        padding-top: 120px;
    }
    .dashboard-card {
        padding: 30px;
    }
    .dashboard-card h2 {
        font-size: 2em;
    }
    .dashboard-card p {
        font-size: 1.1em;
    }
}

@media (max-width: 480px) {
    .navbar {
        padding: 10px 1rem;
    }
    .nav-logo {
        font-size: 20px;
    }
    .nav-menu {
        gap: 1rem;
    }
    .nav-menu a {
        font-size: 14px;
    }
    .dashboard-card {
        padding: 20px;
    }
    .dashboard-card h2 {
        font-size: 1.8em;
    }
    .dashboard-card p {
        font-size: 1em;
    }
    .dashboard-card .quick-links {
        flex-direction: column;
        gap: 10px;
    }
}

/* === Footer Styles === */
.simple-footer {
    background-color: #0d0d0d; /* Match the body background */
    color: #fff;
    padding: 20px 0;
    text-align: center;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    width: 100%;
    position: relative;
    margin-top: auto; /* Keeps footer at bottom */
}

/* Orange line above the footer text */
.simple-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #FF4500;
}
</style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">MealMate</h1>
            <ul class="nav-menu">
                <li><a href="<?php echo $base_path; ?>/index.php">Home</a></li>
                <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
                <li><a href="manage_food.php">Manage Food</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="dashboard-container">
        <div class="dashboard-card">
            <h2>Welcome, Admin <?= htmlspecialchars($_SESSION['full_name']) ?>!</h2>
            <p>This is your administrative control panel.</p>
            <div class="quick-links">
                <a href="manage_food.php">Manage Food</a>
                <a href="manage_users.php">Manage Users</a>
            </div>
        </div>
    </div>
    <div class="simple-footer">
        &copy; 2025 MealMate. All rights reserved.
    </div>
</body>
</html>
