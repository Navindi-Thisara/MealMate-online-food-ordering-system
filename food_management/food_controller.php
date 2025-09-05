<?php
session_start();
require_once 'database.php';

// Handle different actions
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add_to_cart':
        addToCart();
        break;
    case 'add_food':
        addFood();
        break;
    case 'edit_food':
        editFood();
        break;
    case 'delete_food':
        deleteFood();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function addToCart() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        return;
    }
    
    $food_id = $_GET['food_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if item already in cart
    $sql = "SELECT * FROM cart WHERE user_id = ? AND food_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $food_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $sql = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND food_id = ?";
    } else {
        // Insert new item
        $sql = "INSERT INTO cart (user_id, food_id, quantity) VALUES (?, ?, 1)";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $food_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding to cart']);
    }
}

function addFood() {
    global $conn;
    
    // Only admin can add food
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    
    // This will be implemented in add_food.php
    echo json_encode(['success' => false, 'message' => 'Not implemented yet']);
}
?>