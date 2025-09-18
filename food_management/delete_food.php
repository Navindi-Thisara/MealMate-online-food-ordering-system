<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

$food_id = $_GET['id'] ?? 0;

if ($food_id) {
    // Soft delete by setting available to 0
    $sql = "UPDATE foods SET available = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $food_id);
    
    if ($stmt->execute()) {
        $message = "Food item deleted successfully!";
    } else {
        $error = "Error deleting food item: " . $conn->error;
    }
}

header('Location: menu.php');
exit;
?>