<?php
session_start();
require_once 'database.php';
?>
<!DOCTYPE html>
<html lang="en">

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
                <li><a href="../index.php">Home</a></li>
                <li><a href="../login.php">Login</a></li>
                <li><a href="menu.php" class="active">Menu</a></li>
                <li><a href="../cart.php">Cart</a></li>
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
                        <img src="images/' . $row["image"] . '" alt="' . $row["name"] . '" 
                             onerror="this.src=\'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjMzMzIi8+PHRleHQgeD0iNTAlIiB5PSI5MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iI0ZGNDUwMCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+SW1hZ2UgTm90IEZvdW5kPC90ZXh0Pjwvc3Zn+\'">
                    </div>
                    <h3>' . $row["name"] . '</h3>
                    <p>' . $row["description"] . '</p>
                    <div class="item-footer">
                        <span class="price">$' . $row["price"] . '</span>
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
                    ['id' => 1, 'name' => 'Pizza Margherita', 'description' => 'Classic cheese pizza with fresh basil', 'price' => '12.99', 'image' => 'pizza.jpg'],
                    ['id' => 2, 'name' => 'Burger Deluxe', 'description' => 'Beef patty with cheese and veggies', 'price' => '8.99', 'image' => 'burger.jpg'],
                    ['id' => 3, 'name' => 'Pasta Alfredo', 'description' => 'Creamy pasta with parmesan cheese', 'price' => '10.99', 'image' => 'pasta.jpg']
                ];

                foreach ($staticItems as $item) {
                    echo '
            <div class="menu-item">
                <div class="food-image">
                    <img src="images/' . $item["image"] . '" alt="' . $item["name"] . '" 
                         onerror="this.src=\'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjMzMzIi8+PHRleHQgeD0iNTAlIiB5PSI5MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iI0ZGNDUwMCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+SW1hZ2UgTm90IEZvdW5kPC90ZXh0Pjwvc3Zn+\'">
                </div>
                <h3>' . $item["name"] . '</h3>
                <p>' . $item["description"] . '</p>
                <div class="item-footer">
                    <span class="price">$' . $item["price"] . '</span>
                    <button class="add-to-cart" onclick="addToCart(' . $item["id"] . ')">Add to Cart</button>
                </div>
            </div>';
                }
            }
            ?>
        </div>
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

</html>