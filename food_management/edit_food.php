<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

// Get food item to edit
$food_id = $_GET['id'] ?? 0;
$food = null;

if ($food_id) {
    $sql = "SELECT * FROM foods WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $food_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $food = $result->fetch_assoc();
}

if (!$food) {
    header('Location: menu.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $available = isset($_POST['available']) ? 1 : 0;
    
    // Handle image upload
    $image = $food['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uniqid() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/' . $image);
    }
    
    $sql = "UPDATE foods SET name = ?, description = ?, price = ?, category = ?, image = ?, available = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdssii", $name, $description, $price, $category, $image, $available, $food_id);
    
    if ($stmt->execute()) {
        $message = "Food item updated successfully!";
        // Refresh food data
        $food = array_merge($food, [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'category' => $category,
            'image' => $image,
            'available' => $available
        ]);
    } else {
        $error = "Error updating food item: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Food - MealMate</title>
    <link rel="stylesheet" href="menu.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">MealMate</h1>
            <ul class="nav-menu">
                <li><a href="../index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="add_food.php">Add Food</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h2>✏️ Edit Food Item</h2>
        </div>

        <form method="POST" enctype="multipart/form-data" class="food-form">
            <div class="form-group">
                <label for="name">Food Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($food['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($food['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price ($):</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $food['price']; ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="pizza" <?php echo $food['category'] === 'pizza' ? 'selected' : ''; ?>>Pizza</option>
                    <option value="burger" <?php echo $food['category'] === 'burger' ? 'selected' : ''; ?>>Burger</option>
                    <option value="pasta" <?php echo $food['category'] === 'pasta' ? 'selected' : ''; ?>>Pasta</option>
                    <option value="salad" <?php echo $food['category'] === 'salad' ? 'selected' : ''; ?>>Salad</option>
                    <option value="drink" <?php echo $food['category'] === 'drink' ? 'selected' : ''; ?>>Drink</option>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Food Image:</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if ($food['image'] !== 'default.jpg'): ?>
                    <p>Current image: <?php echo $food['image']; ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group checkbox">
                <label>
                    <input type="checkbox" name="available" <?php echo $food['available'] ? 'checked' : ''; ?>>
                    Available for order
                </label>
            </div>

            <button type="submit" class="add-to-cart">Update Food Item</button>

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