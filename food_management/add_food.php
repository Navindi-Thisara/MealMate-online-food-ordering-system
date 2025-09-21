<?php
// Start the session to manage user data
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection file
// The path is now one level up to reach the root, then into 'includes'
require_once __DIR__ . '/../includes/db_connect.php';

// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Path to get from food_management to the users folder
    header("Location: ../users/login.php");
    exit();
}

// Initialize variables to store form data and messages
$message = ''; // FIX: Initialized the message variable to an empty string
$name = '';
$description = '';
$price = '';
$category = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate form data
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $category = trim($_POST['category']);

    // Check if all required fields are filled
    if (empty($name) || empty($description) || empty($price) || empty($category)) {
        $message = '<p class="message error">All fields are required.</p>';
    } elseif (!is_numeric($price) || $price <= 0) {
        // Validate price to ensure it is a positive number
        $message = '<p class="message error">Price must be a positive number.</p>';
    } else {
        // --- ADDED: Check for duplicate food name before inserting ---
        $check = $conn->prepare("SELECT id FROM foods WHERE name = ?");
        $check->bind_param("s", $name);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $message = '<p class="message error">Food item with this name already exists.</p>';
        } else {
            $image_filename = 'no-image.jpg'; // Default placeholder image

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $file_info = pathinfo($_FILES['image']['name']);
                $extension = strtolower($file_info['extension']);

                if (in_array($extension, $allowed_extensions)) {
                    // --- ADDED: Rename image to a slugified food name for consistency ---
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
                    $image_filename = $slug . '.' . $extension;

                    // Determine the correct folder for the image based on your folder structure
                    $image_folder = 'miscellaneous'; // Default folder
                    switch ($category) {
                        case 'Appetizers':
                            $image_folder = 'appetizers';
                            break;
                        case 'Burgers and Sandwiches':
                            $image_folder = 'burgers';
                            break;
                        case 'Pizzas':
                            $image_folder = 'pizzas';
                            break;
                        case 'Pastas':
                            $image_folder = 'pastas';
                            break;
                        case 'Desserts':
                            $image_folder = 'desserts';
                            break;
                    }

                    // Corrected path to upload images to the project's assets folder
                    $upload_dir = dirname(__DIR__) . "/assets/images/menu/" . $image_folder;
                    if (!is_dir($upload_dir)) {
                        // Create the directory if it doesn't exist
                        mkdir($upload_dir, 0777, true);
                    }
                    $upload_path = $upload_dir . '/' . $image_filename;

                    // Move the uploaded file to the destination
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $message = '<p class="message error">Failed to upload image. Please try again.</p>';
                        $image_filename = 'no-image.jpg'; // Reset to placeholder on failure
                    }
                } else {
                    $message = '<p class="message error">Invalid image file type. Please upload a JPG, JPEG, PNG, or GIF.</p>';
                }
            }

            // Only proceed with database insertion if there are no errors
            if (empty($message)) {
                // Prepare and execute the SQL query to insert the new food item
                // --- FIXED: Corrected bind types from 'ssdis' to 'ssdss' to match data types ---
                $stmt = $conn->prepare("INSERT INTO foods (name, description, price, category, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdss", $name, $description, $price, $category, $image_filename);

                if ($stmt->execute()) {
                    $_SESSION['message'] = '<p class="message success">Food item added successfully!</p>';
                    // The path now correctly redirects from food_management to users/admin/manage_food
                    header("Location: ../users/admin/manage_food.php");
                    exit();
                } else {
                    $message = '<p class="message error">Error: ' . $stmt->error . '</p>';
                }

                $stmt->close();
            }
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Food Item - MealMate Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Shared Styles for Admin Pages */
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

        /* === Form Styles === */
        .form-container {
            padding-top: 150px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-card {
            background: rgba(20, 20, 20, 0.95);
            padding: 40px;
            border-radius: 12px;
            border: 2px solid #FF4500;
            box-shadow: 0 4px 20px rgba(255, 69, 0, 0.5);
            width: 500px;
            max-width: 90%;
            margin: 20px auto;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .form-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(255, 69, 0, 0.7);
        }

        .form-card h2 {
            text-align: center;
            color: #ff4500;
            font-size: 2em;
            margin-bottom: 20px;
        }

        .form-card label {
            display: block;
            margin-bottom: 5px;
            color: #ccc;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #ff4500;
            background-color: #1a1a1a;
            color: #fff;
            border-radius: 8px;
            font-size: 1rem;
            transition: box-shadow 0.3s, transform 0.2s;
        }

        .form-group select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            padding-right: 40px;
            /* space for custom arrow */
            background-image: url("data:image/svg+xml;utf8,<svg fill='%23ff4500' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            box-shadow: 0 0 12px #ff4500;
            transform: scale(1.01);
        }

        input[type="file"] {
            width: 100%;
            padding: 12px;
            background-color: #1a1a1a;
            border: 2px solid #ff4500;
            border-radius: 8px;
            cursor: pointer;
            color: #fff;
        }

        input[type="file"]:focus {
            outline: none;
            box-shadow: 0 0 12px #ff4500;
        }

        /* Style the browse button */
        input[type="file"]::-webkit-file-upload-button {
            background-color: #ff4500;
            color: #000;
            border: none;
            border-radius: 6px;
            padding: 8px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            background-color: #e65c00;
        }

        .form-actions {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            background-color: #ff4500;
            color: #000;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #e65c00;
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            background-color: #4CAF50;
            color: white;
        }

        .error {
            background-color: #F44336;
            color: white;
        }

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
            margin-top: 50px;
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

<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">MealMate</h1>
            <ul class="nav-menu">
                <!-- Links now correctly reference their location from the root -->
                <li><a href="../index.php">Home</a></li>
                <li><a href="../users/admin/admin_dashboard.php">Dashboard</a></li>
                <li><a href="../users/admin/manage_food.php" class="active">Manage Food</a></li>
                <li><a href="../users/admin/manage_users.php">Manage Users</a></li>
                <li><a href="../users/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="form-container">
        <div class="form-card">
            <h2>Add New Food Item</h2>
            <?php echo $message; ?>
            <form action="add_food.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Food Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?= htmlspecialchars($description); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Price (Rs.)</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($price); ?>" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select a Category</option>
                        <option value="Appetizers" <?= $category === 'Appetizers' ? 'selected' : '' ?>>Appetizers</option>
                        <option value="Burgers and Sandwiches" <?= $category === 'Burgers and Sandwiches' ? 'selected' : '' ?>>Burgers and Sandwiches</option>
                        <option value="Pastas" <?= $category === 'Pastas' ? 'selected' : '' ?>>Pastas</option>
                        <option value="Pizzas" <?= $category === 'Pizzas' ? 'selected' : '' ?>>Pizzas</option>
                        <option value="Desserts" <?= $category === 'Desserts' ? 'selected' : '' ?>>Desserts</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="image">Food Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn"> Add Food Item</button>
                </div>
            </form>
        </div>
    </div>
    <div class="simple-footer"> &copy; <?= date('Y') ?> MealMate. All rights reserved. </div>
</body>

</html>