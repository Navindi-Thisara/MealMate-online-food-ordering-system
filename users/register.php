<?php
// users/register.php
include "../includes/db_connect.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $contact_no = $_POST['contact_no'];
    $address = $_POST['address'];

    $sql = "INSERT INTO users (full_name, email, password, contact_no, address) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $full_name, $email, $password, $contact_no, $address);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Registration successful! <a href='login.php'>Login here</a>.";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - MealMate</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <h1>MealMate</h1>
        <nav>
            <a href="../index.php">Home</a>
            <a href="login.php">Login</a>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h2>User Registration</h2>

            <?php if($message) { echo "<p class='alert alert-success'>$message</p>"; } ?>

            <form method="post" action="">
                <label>Full Name</label>
                <input type="text" name="full_name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>Contact No</label>
                <input type="text" name="contact_no" required>

                <label>Address</label>
                <textarea name="address" required></textarea>

                <button type="submit">Register</button>
            </form>
        </div>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> MealMate. All rights reserved.
    </footer>
</body>
</html>
