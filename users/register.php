<?php
session_start();
include '../includes/db_connect.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $contact_no = trim($_POST['contact_no']);
    $address    = trim($_POST['address']);

    // === Input Validations ===
    // Full name: only letters and spaces
    if (!preg_match("/^[a-zA-Z ]+$/", $full_name)) {
        $msg = "⚠️ Full Name should only contain letters and spaces.";
    }
    // Contact number: only digits (10 digits)
    elseif (!preg_match("/^[0-9]{10}$/", $contact_no)) {
        $msg = "⚠️ Contact number should contain only digits (10 digits).";
    }
    // Password: at least 1 lowercase, 1 uppercase, 1 digit, 1 special char, min 8 chars
    elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[_\W]).{8,}$/", $password)) {
        $msg = "⚠️ Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    } else {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Check if email already exists
        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $msg = "⚠️ Email already registered!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, contact_no, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $full_name, $email, $hashedPassword, $contact_no, $address);

            if ($stmt->execute()) {
                $msg = "✅ Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $msg = "❌ Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - MealMate</title>
    <link rel="stylesheet" href="../assets/form.css?v=1">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .msg { color: red; margin-bottom: 10px; font-weight: bold; }
        .error { color: red; font-size: 14px; margin-top: -5px; margin-bottom: 8px; display: none; }
        input:invalid, textarea:invalid { border: 1px solid red; }
    </style>
</head>
<body>
    <header>
        <h1 class="nav-logo">MealMate</h1>
        <nav class="nav-menu">
            <a href="../index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="../food_management/menu.php">Menu</a>
            <a href="../cart/cart.php">Cart</a>
        </nav>
    </header>

    <div class="form-container">
        <h2>User Registration</h2>
        <?php if ($msg != ""): ?>
            <div class="msg"><?= $msg ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="registerForm">
            <!-- Full Name -->
            <input type="text" name="full_name" placeholder="Full Name" required>
            <div class="error" id="nameError">Full Name should only contain letters and spaces.</div>

            <!-- Email -->
            <input type="email" name="email" placeholder="Email Address" required>
            <div class="error" id="emailError">Please enter a valid email address.</div>

            <!-- Password -->
            <input type="password" name="password" placeholder="Password" required>
            <div class="error" id="passwordError">Password must be 8+ chars, include uppercase, lowercase, number, and special char.</div>

            <!-- Contact Number -->
            <input type="text" name="contact_no" placeholder="Contact Number" required>
            <div class="error" id="contactError">Contact number must be exactly 10 digits.</div>

            <!-- Address -->
            <textarea name="address" placeholder="Enter Address" required></textarea>
            <div class="error" id="addressError">Address cannot be empty.</div>

            <button type="submit">Register</button>
        </form>

        <p class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </p>
        <p class="login-link">
            Are you an administrator? <a href="register_admin.php">Register as Admin</a>
        </p>
    </div>

    <footer>
        &copy; <?= date('Y') ?> MealMate. All rights reserved.
    </footer>

    <script>
    const form = document.getElementById("registerForm");
    const submitBtn = form.querySelector("button");

    const fields = {
        full_name: { regex: /^[A-Za-z ]+$/, errorId: "nameError" },
        email: { regex: /^[^@]+@[^@]+\.[a-z]{2,}$/i, errorId: "emailError" },
        password: { regex: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[_\W]).{8,}$/, errorId: "passwordError" },
        contact_no: { regex: /^[0-9]{10}$/, errorId: "contactError" },
        address: { regex: /.+/, errorId: "addressError" }
    };

    function validateField(fieldName) {
        const field = form[fieldName];
        const { regex, errorId } = fields[fieldName];
        const isValid = regex.test(field.value.trim());
        document.getElementById(errorId).style.display = isValid ? "none" : "block";
        return isValid;
    }

    function validateForm() {
        let valid = true;
        for (let name in fields) {
            if (!validateField(name)) valid = false;
        }
        submitBtn.disabled = !valid;
    }

    // Add event listeners
    for (let name in fields) {
        form[name].addEventListener("input", validateForm);
    }

    // Initial check
    validateForm();
    </script>

</body>
</html>