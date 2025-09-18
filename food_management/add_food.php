<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    
    // Handle image upload
    $image = 'default.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uniqid() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/' . $image);
    }
    
    $sql = "INSERT INTO foods (name, description, price, category, image, available) 
            VALUES (?, ?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdss", $name, $description, $price, $category, $image);
    
    if ($stmt->execute()) {
        $message = "Food item added successfully!";
    } else {
        $error = "Error adding food item: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Food - MealMate</title>
    <link rel="stylesheet" href="menu.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">MealMate</h1>
            <ul class="nav-menu">
                <li><a href="../index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="add_food.php" class="active">Add Food</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h2>➕ Add New Food Item</h2>
        </div>

        <form method="POST" enctype="multipart/form-data" class="food-form">
            <div class="form-group">
                <label for="name">Food Name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price ($):</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="pizza">Pizza</option>
                    <option value="burger">Burger</option>
                    <option value="pasta">Pasta</option>
                    <option value="salad">Salad</option>
                    <option value="drink">Drink</option>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Food Image:</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <button type="submit" class="add-to-cart">Add Food Item</button>

            <?php if (isset($message)): ?>
                <p class="success"><?php echo $message; ?></p>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>