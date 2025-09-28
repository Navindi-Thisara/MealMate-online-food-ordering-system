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

// Define the base path for consistent navigation
$base_path = '/MealMate-online-food-ordering-system';

// Fetch all users
$users = [];
$sql = "SELECT * FROM users ORDER BY user_id ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - MealMate Admin</title>
    <link rel="stylesheet" href="../assets/form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* === Global & Navbar Styles from admin_dashboard.php === */
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
            min-height: 100vh;
        }

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

        /* === Page Specific Styles (Adapted from original code) === */
        .container {
            width: 100%;
            max-width: 1400px;
            margin: 120px auto 20px auto;
            padding: 0 50px;
            flex: 1 0 auto;
        }

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
            /* Adjusted for a larger gap */
            margin-bottom: 2rem;
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

        .search-container {
            padding: 8px 10px;
            background: rgba(20,20,20,0.95);
            position: sticky;
            top: 72px; /* Adjusted to be below the navbar */
            z-index: 10; /* Set z-index to make sure it's on top */
            border-bottom: 2px solid #FF4500;
            border-top: 2px solid #FF4500;
        }

        .search-container input {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #FF4500;
            width: 250px;
            background: #111;
            color: #fff;
            transition: all 0.3s ease;
        }

        .search-container input:focus {
            outline: none;
            border-color: #ff4500;
            box-shadow: 0 0 10px #ff4500;
        }

        /* Removed max-height and overflow-y from this container to allow the body to scroll */
        .user-table-container {
            background: rgba(20, 20, 20, 0.95);
            border-radius: 12px;
            border: 2px solid #FF4500;
            box-shadow: 0 4px 20px rgba(255, 69, 0, 0.5);
            /* Increased margin for the gap as requested */
            margin-top: 40px; 
            margin-bottom: 40px;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            color: #e0e0e0;
            min-width: 900px;
        }

        .user-table th, .user-table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #444;
            font-size: 14px;
        }

        .user-table th {
            background-color: #ff4500;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
            position: sticky;
            /* New sticky position to sit below the search bar. */
            top: 116px;
            z-index: 9;
        }

        .user-table tr:nth-child(even) {
            background-color: #111;
        }

        .user-table tr:hover {
            background-color: #222;
        }

        .user-table .actions {
            display: inline-flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }

        .user-table .actions a {
            color: #fff;
            font-size: 1.2rem;
            transition: color 0.3s;
        }

        .user-table .actions .edit-btn:hover {
            color: #4CAF50;
        }

        .user-table .actions .delete-btn:hover {
            color: #F44336;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
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
        
        /* === Footer styles for the copyright text === */
        .simple-footer {
            background-color: #0d0d0d;
            color: #fff;
            padding: 10px 0;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            position: relative;
            width: 100%;
            margin-top: auto;
        }
        
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
<body class="manage-users">
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">MealMate</h1>
            <ul class="nav-menu">
                <li><a href="<?php echo $base_path; ?>/index.php">Home</a></li>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="manage_food.php">Manage Food</a></li>
                <li><a href="/MealMate-online-food-ordering-system/users/admin/orders/admin_order_details.php">Manage Orders</a></li>
                <li><a href="manage_users.php" class="active">Manage Users</a></li>
                <li><a href="<?php echo $base_path; ?>/users/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h2>Manage Users</h2> 
            <p>View, update, or delete registered users.</p>
        </div>

        <div class="user-table-container">
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search users...">
            </div>
            <table class="user-table" id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['contact_no']) ?></td>
                            <td><?= htmlspecialchars($user['address']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td class="actions">
                                <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="edit-btn"><i class="fas fa-edit"></i></a>
                                <a href="#" class="delete-btn" onclick="showDeleteModal(<?= $user['user_id'] ?>); return false;"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No users found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="confirm" onclick="confirmDelete()">Delete</button>
                <button class="cancel" onclick="hideDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    <div class="simple-footer">
        &copy; 2025 MealMate. All rights reserved.
    </div>

    <script>
        // Delete modal
        let userIdToDelete = null;
        function showDeleteModal(userId) {
            userIdToDelete = userId;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        function hideDeleteModal() {
            userIdToDelete = null;
            document.getElementById('deleteModal').style.display = 'none';
        }
        function confirmDelete() {
            if (userIdToDelete !== null) {
                window.location.href = 'delete_user.php?id=' + userIdToDelete;
            }
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const userTable = document.getElementById('userTable').getElementsByTagName('tbody')[0];

        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            const rows = userTable.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let match = false;
                for (let j = 0; j < cells.length - 1; j++) { // ignore Actions column
                    if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                        match = true;
                        break;
                    }
                }
                rows[i].style.display = match ? '' : 'none';
            }
        });
    </script>
</body>
</html>
