<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db_connect.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit();
}

$message = '';

// Check for food ID in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../users/admin/manage_food.php");
    exit();
}

$food_id = $_GET['id'];
$food_item = null;

// Fetch food item data
$stmt = $conn->prepare("SELECT * FROM foods WHERE id = ?");
$stmt->bind_param("i", $food_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $food_item = $result->fetch_assoc();
} else {
    $_SESSION['message'] = '<p class="message error">Food item not found.</p>';
    header("Location: ../users/admin/manage_food.php");
    exit();
}
$stmt->close();

// Function to determine folder based on category
function getImageFolder($category)
{
    $map = [
        'Appetizers' => 'appetizers',
        'Burgers and Sandwiches' => 'burgers',
        'Pastas' => 'pastas',
        'Pizzas' => 'pizzas',
        'Desserts' => 'desserts'
    ];
    return $map[$category] ?? 'miscellaneous';
}

// Function to ensure folder exists
function ensureFolderExists($folder)
{
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $category = trim($_POST['category']);
    $current_image = !empty($_POST['current_image']) ? $_POST['current_image'] : 'no-image.jpg';
    $image_filename = $current_image;

    if (empty($name) || empty($description) || empty($price) || empty($category)) {
        $message = '<p class="message error">All fields are required.</p>';
    } elseif (!is_numeric($price) || $price <= 0) {
        $message = '<p class="message error">Price must be a positive number.</p>';
    } else {
        // --- Image Handling Logic (Updated) ---
        $upload_base = $_SERVER['DOCUMENT_ROOT'] . '/MealMate-online-food-ordering-system/assets/images/menu';
        $new_folder = getImageFolder($category);
        $old_folder = getImageFolder($food_item['category']);

        // Ensure the target folder for the new image exists
        ensureFolderExists($upload_base . '/' . $new_folder);

        // Check if a new image was uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($ext, $allowed)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
                $image_filename = $slug . '.' . $ext;

                $upload_path = $upload_base . '/' . $new_folder . '/' . $image_filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Successfully uploaded new image, now delete the old one
                    // Only delete if the old image is not the placeholder and it exists
                    $old_path = $upload_base . '/' . $old_folder . '/' . $current_image;
                    if ($current_image !== 'no-image.jpg' && file_exists($old_path)) {
                        if (!unlink($old_path)) {
                            // Deletion failed, but continue with the rest of the script
                            // Note: The new image is already uploaded
                            $message = '<p class="message error">Failed to delete old image at ' . $old_path . '</p>';
                        }
                    }
                } else {
                    // New image upload failed
                    $message = '<p class="message error">Failed to upload new image to ' . $upload_path . '. Check folder permissions.</p>';
                }
            } else {
                $message = '<p class="message error">Invalid image type. Only JPG, JPEG, PNG, GIF are allowed.</p>';
            }
        }
        // If no new image was uploaded, but the food name or category changed, rename the old file
        elseif ($current_image !== 'no-image.jpg' && ($name !== $food_item['name'] || $category !== $food_item['category'])) {
            $old_path = $upload_base . '/' . $old_folder . '/' . $current_image;
            $ext = pathinfo($current_image, PATHINFO_EXTENSION);
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $new_path = $upload_base . '/' . $new_folder . '/' . $slug . '.' . $ext;

            // Ensure the destination folder for the renamed file exists
            ensureFolderExists(dirname($new_path));

            if (file_exists($old_path)) {
                if (rename($old_path, $new_path)) {
                    $image_filename = basename($new_path);
                } else {
                    $message = '<p class="message error">Failed to rename existing image.</p>';
                }
            }
        }
        // --- End of Image Handling Logic ---

        // Update database only if there were no critical errors
        if (empty($message)) {
            $stmt = $conn->prepare("UPDATE foods SET name=?, description=?, price=?, category=?, image=? WHERE id=?");
            $stmt->bind_param("ssdssi", $name, $description, $price, $category, $image_filename, $food_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = '<p class="message success">Food item updated successfully!</p>';
                // Note: The header redirect needs to be to a valid location, check your file structure
                header("Location: ../users/admin/manage_food.php");
                exit();
            } else {
                $message = '<p class="message error">DB Error: ' . $stmt->error . '</p>';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Food Item - MealMate Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #fff;
            background-color: #0d0d0d;
            background-image: url('../assets/images/bg-dark.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            scroll-behavior: smooth;
            overflow-x: hidden;
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #FF4500;
            padding: 20px 50px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }

        .nav-container {
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
            text-shadow: 3px3px6px #000;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-menu a {
            color: #fff;
            text-decoration: none;
            font-size: 18px;
            font-weight: 400;
            position: relative;
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #FF4500;
            transition: width 0.3s;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            color: #FF4500;
        }

        .nav-menu a:hover::after,
        .nav-menu a.active::after {
            width: 100%;
        }

        .form-container {
            padding-top: 150px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: calc(100vh - 150px);
            padding-bottom: 50px;
        }

        .form-card {
            background: rgba(20, 20, 20, 0.95);
            padding: 40px;
            border-radius: 12px;
            border: 2px solid #FF4500;
            box-shadow: 0 4px 20px rgba(255, 69, 0, 0.5);
            width: 100%;
            max-width: 500px;
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

        .form-card input,
        .form-card textarea,
        .form-card select {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border: 2px solid #ff4500;
            border-radius: 8px;
            background-color: #1a1a1a;
            color: #fff;
            font-size: 1rem;
        }

        .form-card textarea {
            resize: vertical;
        }

        .form-card button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 5px;
            background-color: #ff4500;
            color: #000;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
        }

        .form-card button:hover {
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

        .current-image-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .current-image-container img {
            max-width: 140px;
            height: auto;
            border-radius: 8px;
            border: 2px solid #ff4500;
        }

        .simple-footer {
            background-color: #0d0d0d;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            width: 100%;
            margin-top: 50px;
            position: relative;
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

        @media(max-width:600px) {
            .form-card {
                padding: 25px 15px;
            }

            .nav-container {
                flex-direction: column;
                gap: 15px;
            }

            .nav-menu {
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">MealMate</h1>
            <ul class="nav-menu">
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
            <h2>Edit Food Item</h2>
            <?php echo $message; ?>
            <form action="edit_food.php?id=<?= $food_id ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_image"
                    value="<?= htmlspecialchars($food_item['image'] ?: 'no-image.jpg') ?>">

                <label for="name">Food Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($food_item['name']) ?>" required>

                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"
                    required><?= htmlspecialchars($food_item['description']) ?></textarea>

                <label for="price">Price (Rs.)</label>
                <input type="number" id="price" name="price" step="0.01"
                    value="<?= htmlspecialchars($food_item['price']) ?>" required>

                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="Appetizers" <?= ($food_item['category'] == 'Appetizers') ? 'selected' : '' ?>>Appetizers
                    </option>
                    <option value="Burgers and Sandwiches" <?= ($food_item['category'] == 'Burgers and Sandwiches') ? 'selected' : '' ?>>Burgers and Sandwiches</option>
                    <option value="Pastas" <?= ($food_item['category'] == 'Pastas') ? 'selected' : '' ?>>Pastas</option>
                    <option value="Pizzas" <?= ($food_item['category'] == 'Pizzas') ? 'selected' : '' ?>>Pizzas</option>
                    <option value="Desserts" <?= ($food_item['category'] == 'Desserts') ? 'selected' : '' ?>>Desserts</option>
                </select>

                <div class="current-image-container">
                    <p>Current Image:</p>
                    <?php
                    $image_folder = getImageFolder($food_item['category']);
                    $image_file = $food_item['image'] ?: 'no-image.jpg';
                    $web_path = '/MealMate-online-food-ordering-system/assets/images/menu/' . $image_folder . '/' . $image_file;

                    // Check if the image exists on the server before displaying it
                    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $web_path)) {
                        $web_path = 'https://placehold.co/150x150/0d0d0d/FFFFFF?text=No+Image';
                    }
                    ?>
                    <img src="<?= htmlspecialchars($web_path) ?>" alt="Current image">
                </div>

                <label for="image">Change Image</label>
                <input type="file" id="image" name="image" accept="image/*">

                <button type="submit">Update Food Item</button>
            </form>
        </div>
    </div>

    <div class="simple-footer">
        &copy; <?= date('Y') ?> MealMate. All rights reserved.
    </div>
</body>

</html>