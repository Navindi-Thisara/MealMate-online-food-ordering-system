<?php
session_start();

include '../includes/menu_header.php';
require_once '../includes/db_connect.php';
?>

<style>
/* === Custom Alert Box Styles === */
.custom-alert {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #28a745;
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    text-align: center;
    transition: opacity 0.5s ease-in-out;
    opacity: 1;
}
.custom-alert.error {
    background-color: #dc3545;
}
</style>

<div class="container">
    <div class="header">
        <h2>🍕 Food Menu</h2>
        <p>Discover our delicious offerings</p>
    </div>

    <?php
    if ($conn !== null && $conn->connect_error === null) {
        // Fetch distinct categories in a specific order
        $sql_categories = "SELECT DISTINCT category FROM foods WHERE available = 1
                           ORDER BY
                               CASE category
                                   WHEN 'Burgers and Sandwiches' THEN 1
                                   WHEN 'Pizzas' THEN 2
                                   WHEN 'Pastas' THEN 3
                                   WHEN 'Appetizers' THEN 4
                                   WHEN 'Desserts' THEN 5
                                   ELSE 6
                               END, category";
        $result_categories = $conn->query($sql_categories);

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
                                <img src="' . htmlspecialchars($image_path) . '" 
                                     alt="' . htmlspecialchars($food_row["name"]) . '"
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
function showAlert(message, isError = false) {
    const alertBox = document.createElement('div');
    alertBox.textContent = message;
    alertBox.classList.add('custom-alert');
    if (isError) {
        alertBox.classList.add('error');
    }
    document.body.appendChild(alertBox);

    // Fade out and remove
    setTimeout(() => {
        alertBox.style.opacity = '0';
        setTimeout(() => alertBox.remove(), 500);
    }, 2000);
}

function addToCart(foodId) {
    fetch('food_controller.php?action=add_to_cart&food_id=' + foodId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Added to cart successfully!');
            } else {
                showAlert('Error: ' + data.message, true);
            }
        })
        .catch(() => {
            showAlert('Error: Failed to connect to server.', true);
        });
}
</script>

<?php
include '../includes/footer.php';
?>