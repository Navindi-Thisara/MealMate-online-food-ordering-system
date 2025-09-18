<?php
session_start();
include '../includes/menu_header.php';
require_once '../includes/db_connect.php';
?>

<<<<<<< HEAD
<div class="container">
    <div class="header">
        <h2>🍕 Food Menu</h2>
        <p>Discover our delicious offerings</p>
=======
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealMate - Food Menu</title>
    <link rel="stylesheet" href="menu.css">
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">MealMate</h1>
            <ul class="nav-menu">
                <li><a href="../index.php">HOME</a></li>
                <li><a href="../login.php">LOGIN</a></li>
                <li><a href="menu.php" class="active">MENU</a></li>
                <li><a href="../cart.php">CART</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h2>🍕 Food Menu</h2>
            <p>Discover our delicious offerings</p>
        </div>

        <div class="menu-grid">
            <?php
            // Check if database connection is available and valid
            if ($conn !== null && $conn->connect_error === null) {
                $sql = "SELECT * FROM foods WHERE available = 1 ORDER BY name";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '
                <div class="menu-item">
                    <div class="food-image">
                        <img src="../assets/images/' . $row["image"] . '" alt="' . $row["name"] . '" 
                             onerror="this.src=\'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjMzMzIi8+PHRleHQgeD0iNTAlIiB5PSI5MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iI0ZGNDUwMCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+SW1hZ2UgTm90IEZvdW5kPC90ZXh0Pjwvc3Zn+\'">
                    </div>
                    <h3>' . $row["name"] . '</h3>
                    <p>' . $row["description"] . '</p>
                    <div class="item-footer">
                        <span class="price">Rs.' . $row["price"] . '</span>
                        <button class="add-to-cart" onclick="addToCart(' . $row["id"] . ')">Add to Cart</button>
                    </div>
                </div>';
                    }
                } else {
                    // Show static menu if database is empty
                    showStaticMenu();
                }
            } else {
                // Show static menu if database connection fails
                showStaticMenu();
            }

            function showStaticMenu()
            {
                $staticItems = [
                    ['id' => 1, 'name' => 'Pizza Margherita', 'description' => 'Classic cheese pizza with fresh basil', 'price' => '1200', 'image' => 'pizza.jpg'],
                    ['id' => 2, 'name' => 'Burger Deluxe', 'description' => 'Beef patty with cheese and veggies', 'price' => '800', 'image' => 'burger.jpg'],
                    ['id' => 3, 'name' => 'Pasta Alfredo', 'description' => 'Creamy pasta with parmesan cheese', 'price' => '1000', 'image' => 'pasta.jpg']
                ];

                foreach ($staticItems as $item) {
                    echo '
            <div class="menu-item">
                <div class="food-image">
                    <img src="../assets/images/' . $item["image"] . '" alt="' . $item["name"] . '" 
                         onerror="this.src=\'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjMzMzIi8+PHRleHQgeD0iNTAlIiB5PSI5MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iI0ZGNDUwMCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+SW1hZ2UgTm90IEZvdW5kPC90ZXh0Pjwvc3Zn+\'">
                </div>
                <h3>' . $item["name"] . '</h3>
                <p>' . $item["description"] . '</p>
                <div class="item-footer">
                    <span class="price">Rs.' . $item["price"] . '</span>
                    <button class="add-to-cart" onclick="addToCart(' . $item["id"] . ')">Add to Cart</button>
                </div>
            </div>';
                }
            }
            ?>
        </div>
>>>>>>> b0ceea530be018fda6373c9e10c1644792d6a83a
    </div>

    <?php
    if ($conn !== null && $conn->connect_error === null) {
        // Fetch distinct categories in a specific order
        $sql_categories = "SELECT DISTINCT category FROM foods WHERE available = 1 
                           ORDER BY 
                               CASE category
                                   WHEN 'Burgers and Sandwiches' THEN 1
                                   WHEN 'Pizzas' THEN 2
                                   WHEN 'Pasta' THEN 3
                                   WHEN 'Appetizers' THEN 4
                                   WHEN 'Desserts' THEN 5
                                   ELSE 6
                               END, category";
        $result_categories = $conn->query($sql_categories);

<<<<<<< HEAD
        if ($result_categories && $result_categories->num_rows > 0) {
            while ($category_row = $result_categories->fetch_assoc()) {
                $category_name = $category_row['category'];
                
                // Display category heading
                echo '<h2 class="category-title">' . htmlspecialchars($category_name) . '</h2>';
                echo '<div class="menu-grid">';
                
                // Fetch food items for the current category
                $sql_foods = "SELECT * FROM foods WHERE available = 1 AND category = ? ORDER BY name";
                $stmt_foods = $conn->prepare($sql_foods);
                $stmt_foods->bind_param("s", $category_name);
                $stmt_foods->execute();
                $result_foods = $stmt_foods->get_result();

                if ($result_foods && $result_foods->num_rows > 0) {
                    while ($food_row = $result_foods->fetch_assoc()) {
                        // Determine the correct image folder name based on the category name
                        $image_folder_name = strtolower($food_row['category']);
                        if ($image_folder_name === 'burgers and sandwiches') {
                            $image_folder_name = 'burgers';
                        } elseif ($image_folder_name === 'pasta') {
                            $image_folder_name = 'pastas';
                        }
                        
                        // Construct the image path relative to the menu.php file
                        $image_path = '../assets/images/menu/' . $image_folder_name . '/' . $food_row['image'];

                        echo '
                        <div class="menu-item">
                            <div class="food-image">
                                <img src="' . htmlspecialchars($image_path) . '" alt="' . htmlspecialchars($food_row["name"]) . '" 
                                     onerror="this.src=\'../assets/images/menu/default.jpg\';">
                            </div>
                            <h3>' . htmlspecialchars($food_row["name"]) . '</h3>
                            <p>' . htmlspecialchars($food_row["description"]) . '</p>
                            <div class="item-footer">
                                <span class="price">Rs.' . htmlspecialchars($food_row["price"]) . '</span>
                                <button class="add-to-cart" onclick="addToCart(' . $food_row["id"] . ')">Add to Cart</button>
                            </div>
                        </div>';
                    }
                }
                echo '</div>'; // close .menu-grid
            }
        } else {
            echo '<p>No food items are currently available.</p>';
        }
    } else {
        echo '<p>Error: Could not connect to the database.</p>';
    }
    ?>
</div>

<script>
    function addToCart(foodId) {
        fetch('food_controller.php?action=add_to_cart&food_id=' + foodId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Added to cart successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }
</script>
</body>
=======
>>>>>>> b0ceea530be018fda6373c9e10c1644792d6a83a
</html>