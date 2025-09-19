<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the database connection file exists and is accessible.
// This path is relative to the current file's location.
require_once __DIR__ . '/../../includes/db_connect.php';

// Check if user is logged in and has admin role.
// This is a crucial security check to prevent unauthorized access.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit();
}

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $message = '<div class="alert success-alert">Food item deleted successfully!</div>';
    } elseif ($_GET['msg'] == 'not_found') {
        $message = '<div class="alert error-alert">Error: Food item not found.</div>';
    } elseif ($_GET['msg'] == 'error') {
        $message = '<div class="alert error-alert">Error deleting food item. Please try again.</div>';
    }
}


// Fetch all food items from the database.
// The ORDER BY clause ensures the newest items are at the top.
$food_items = [];
$sql = "SELECT * FROM foods ORDER BY id DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $food_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Food - MealMate Admin</title>
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

/* === Main Content Container === */
.container {
    width: 100%;
    max-width: 1400px;
    margin: 120px auto 2rem auto;
    padding: 0 50px;
}

/* === Header Section === */
.header {
    text-align: center;
    margin-bottom: 2rem;
    padding: 0.5rem 0;
    position: relative;
}

.header h2 {
    color: #ff4500;
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.header p {
    color: #cccccc;
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

.add-food-btn {
    background: #ff4500;
    color: #000;
    padding: 12px 25px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    transition: background 0.3s, transform 0.3s;
    display: inline-block;
    margin-bottom: 20px;
}

.add-food-btn:hover {
    background: #e65c00;
    transform: translateY(-2px);
}

/* === Food Table Styles === */
.food-table-container {
    overflow-x: auto;
    background: rgba(20,20,20,0.95);
    border-radius: 12px;
    border: 2px solid #FF4500;
    box-shadow: 0 4px 20px rgba(255,69,0,0.5);
    margin-top: 20px;
}

.food-table {
    width: 100%;
    border-collapse: collapse;
    color: #fff;
    min-width: 700px; /* Ensures a minimum width for better readability on smaller screens */
}

.food-table th, .food-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #444;
}

.food-table th {
    background-color: #ff4500;
    color: #000;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 14px;
}

.food-table tr:hover {
    background-color: #1a1a1a;
}

.food-table td {
    min-height: 110px; /* Sets a minimum height for consistent rows */
    box-sizing: border-box;
}

/* Use normal table-cell alignment */
.food-table td.image-cell,
.food-table td.actions-cell {
    text-align: center;
    vertical-align: middle;
    height: 100px; /* consistent row height */
    padding: 10px;
}

.food-table td.description-cell {
    vertical-align: top;
}

/* Image sizing */
.food-table td img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 8px;
}

/* Actions centered neatly */
.food-table .actions {
    display: inline-flex;
    gap: 10px;
    align-items: center;
    justify-content: center;
}

.food-table .actions a {
    color: #fff;
    font-size: 1.2rem;
    transition: color 0.3s;
}

.food-table .actions .edit-btn:hover {
    color: #4CAF50; /* Green */
}

.food-table .actions .delete-btn:hover {
    color: #F44336; /* Red */
}
/* Alert Message Styles */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-weight: 600;
    text-align: center;
    animation: fadeInOut 5s forwards;
}

.alert.success-alert {
    background-color: #28a745; /* Darker green */
    color: #fff;
}

.alert.error-alert {
    background-color: #dc3545; /* Darker red */
    color: #fff;
}

@keyframes fadeInOut {
    0% { opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { opacity: 0; }
}


/* === Responsive Design === */
@media (max-width: 768px) {
    .navbar {
        padding: 15px 20px;
    }
    .container {
        margin: 100px auto 1.5rem auto;
        padding: 0 20px;
    }
    .header h2 {
        font-size: 1.8rem;
    }
    .header p {
        font-size: 1rem;
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
    .header h2 {
        font-size: 1.5rem;
    }
    .header p {
        font-size: 0.9rem;
    }
}
/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.6);
    backdrop-filter: blur(5px);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #222;
    padding: 30px;
    border: 2px solid #ff4500;
    border-radius: 10px;
    width: 80%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
}

.modal-content h3 {
    margin-top: 0;
    color: #ff4500;
}

.modal-buttons {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 15px;
}

.modal-buttons button {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s ease;
}

.modal-buttons .confirm {
    background-color: #F44336;
    color: white;
}

.modal-buttons .confirm:hover {
    background-color: #d32f2f;
}

.modal-buttons .cancel {
    background-color: #555;
    color: white;
}

.modal-buttons .cancel:hover {
    background-color: #777;
}

/* Override background from other stylesheets only on this page */
html body.manage-food {
    background: none !important;
    background-image: none !important;
    background-color: #0d0d0d !important;
}

/* === Footer Styles === */
/* Footer styles for the copyright text */
.simple-footer {
    background-color: #0d0d0d;
    color: #fff;
    padding: 20px 0;
    text-align: center;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    position: relative;
    width: 100%;
    margin: 0;
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

<body class="manage-food">
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">MealMate</h1>
            <ul class="nav-menu">
                <li><a href="/MealMate-online-food-ordering-system/index.php">Home</a></li>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="/MealMate-online-food-ordering-system/food_management/manage_food.php" class="active">Manage Food</a></li>
                <li><a href="/MealMate-online-food-ordering-system/order_management/manage_orders.php">Manage Orders</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="/MealMate-online-food-ordering-system/users/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <div class="header">
            <h2>Manage Food Items</h2>
            <p>Add, edit, or delete food items from your menu.</p>
        </div>
        
        <?php echo $message; ?>
        
        <a href="/MealMate-online-food-ordering-system/food_management/add_food.php" class="add-food-btn">Add New Food Item</a>
        <div class="food-table-container">
            <table class="food-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price (Rs.)</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($food_items)): ?>
                    <?php foreach ($food_items as $item): ?>
                        <?php
                            $image_folder = strtolower($item['category']);
                            if ($image_folder === 'burgers and sandwiches') {
                                $image_folder = 'burgers';
                            } elseif ($image_folder === 'pasta') {
                                $image_folder = 'pastas';
                            }
                            
                            // Construct filesystem path (server)
                            $server_path = $_SERVER['DOCUMENT_ROOT'] . '/MealMate-online-food-ordering-system/assets/images/menu/' . $image_folder . '/' . $item['image'];
                            
                            // Construct web path (for <img src>)
                            $web_path = '/MealMate-online-food-ordering-system/assets/images/menu/' . $image_folder . '/' . $item['image'];
                            
                            // If file doesn’t exist or is empty, use a fallback placeholder image
                            if (empty($item['image']) || !is_file($server_path)) {
                                $web_path = 'https://placehold.co/70x70/0d0d0d/FFFFFF?text=No+Image';
                            }
                        ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($web_path) ?>" alt="<?= htmlspecialchars($item['name']) ?>"></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['description']) ?></td>
                            <td><?= htmlspecialchars(number_format($item['price'], 2)) ?></td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td class="actions">
                                <!-- FIX: Corrected the href URL to pass the ID as a query parameter -->
                                <a href="/MealMate-online-food-ordering-system/food_management/edit_food.php?id=<?= $item['id'] ?>" class="edit-btn"><i class="fas fa-edit"></i></a>
                                <a href="#" class="delete-btn" onclick="showDeleteModal(<?= $item['id'] ?>); return false;">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No food items found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this food item? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="confirm" onclick="confirmDelete()">Delete</button>
                <button class="cancel" onclick="hideDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    <div class="simple-footer">
        &copy; <?= date('Y') ?> MealMate. All rights reserved.
    </div>

    <script>
        let foodIdToDelete = null;

        function showDeleteModal(foodId) {
            foodIdToDelete = foodId;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function hideDeleteModal() {
            foodIdToDelete = null;
            document.getElementById('deleteModal').style.display = 'none';
        }

        function confirmDelete() {
            if (foodIdToDelete !== null) {
                // FIX: Corrected the URL here to pass the ID as a query parameter
                window.location.href = '/MealMate-online-food-ordering-system/food_management/delete_food.php?id=' + foodIdToDelete;
            }
        }
    </script>
</body>
</html>
