<?php
session_start();
require_once('../includes/db_connect.php');

$msg = "";
$redirect_to_login = false; // Flag to control redirection
$secret_key = "1234"; // Admin secret key

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['secret_key']) && $_POST['secret_key'] === $secret_key) {
        $full_name  = trim($_POST['full_name']);
        $email      = trim($_POST['email']);
        $password   = trim($_POST['password']);
        $contact_no = trim($_POST['contact_no']);
        $address    = trim($_POST['address']);

        $role = 'admin';

        // Validations
        $errors = [];
        if (!preg_match("/^[a-zA-Z ]+$/", $full_name)) {
            $errors['full_name'] = "Full Name should only contain letters and spaces.";
        }
        if (!preg_match("/^[^@]+@[^@]+\.[a-z]{2,}$/i", $email)) {
            $errors['email'] = "Please enter a valid email address.";
        }
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[_\W]).{8,}$/", $password)) {
            $errors['password'] = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
        }
        if (!preg_match("/^[0-9]{10}$/", $contact_no)) {
            $errors['contact_no'] = "Contact number must be exactly 10 digits.";
        }
        if (empty($address)) {
            $errors['address'] = "Address cannot be empty.";
        }

        if (empty($errors)) {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $msg = "❌ This email is already registered!";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new admin user
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, contact_no, address, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $full_name, $email, $hashed_password, $contact_no, $address, $role);

                if ($stmt->execute()) {
                    $msg = "✅ Admin registration successful! Redirecting to login page...";
                    $redirect_to_login = true;
                } else {
                    $msg = "❌ Error: " . $stmt->error;
                }
            }
            $stmt->close();
        } else {
            $msg = "⚠️ Please fix the highlighted errors.";
        }
    } else {
        $msg = "❌ Invalid secret key. Access denied!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Registration</title>
<link rel="stylesheet" href="../assets/form.css?v=1">
<link rel="stylesheet" href="../assets/style.css">
<style>
    .msg { color: red; margin-bottom: 10px; font-weight: bold; }
    .error { color: red; font-size: 13px; display: none; margin-top: 2px; }
    input:invalid, textarea:invalid { border: 1px solid red; }
    button:disabled { background: #ccc; cursor: not-allowed; }
</style>
<?php if ($redirect_to_login): ?>
<meta http-equiv="refresh" content="3;url=login.php">
<?php endif; ?>
</head>
<body>
<header>
    <h1 class="nav-logo">MealMate</h1>
    <nav class="nav-menu">
        <a href="../index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<div class="form-container">
    <h2>Admin Registration</h2>

    <?php if ($msg != ""): ?>
        <div class="msg"><?= $msg ?></div>
    <?php endif; ?>

    <?php if (!$redirect_to_login): ?>
    <form action="register_admin.php" method="POST" id="adminForm">
        <input type="text" name="full_name" placeholder="Full Name" title="Only letters and spaces allowed" required>
        <div class="error" id="nameError">Full Name should only contain letters and spaces.</div>

        <input type="email" name="email" placeholder="Email Address" title="Enter a valid email" required>
        <div class="error" id="emailError">Please enter a valid email address.</div>

        <input type="password" name="password" placeholder="Password" title="Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special" required>
        <div class="error" id="passwordError">Password must be at least 8 chars, include uppercase, lowercase, number, and special char.</div>

        <input type="text" name="contact_no" placeholder="Contact Number" title="Exactly 10 digits" required>
        <div class="error" id="contactError">Contact number must be exactly 10 digits.</div>

        <textarea name="address" placeholder="Enter Address" title="Enter your address" required></textarea>
        <div class="error" id="addressError">Address cannot be empty.</div>

        <input type="password" name="secret_key" placeholder="Admin Secret Key" title="Enter the secret key" required>
        <div class="error" id="keyError">Invalid secret key.</div>

        <button type="submit" disabled>Register as Admin</button>
    </form>
    <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    <?php endif; ?>
</div>

<footer>
    &copy; <?= date('Y'); ?> MealMate. All rights reserved.
</footer>

<script>
const form = document.getElementById("adminForm");
const submitBtn = form.querySelector("button");

const fields = {
    full_name: { regex: /^[A-Za-z ]+$/, errorId: "nameError" },
    email: { regex: /^[^@]+@[^@]+\.[a-z]{2,}$/i, errorId: "emailError" },
    password: { regex: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[_\W]).{8,}$/, errorId: "passwordError" },
    contact_no: { regex: /^[0-9]{10}$/, errorId: "contactError" },
    address: { regex: /.+/, errorId: "addressError" },
    secret_key: { regex: /^1234$/, errorId: "keyError" } // change if key changes
};

function validateField(fieldName) {
    const field = form[fieldName];
    const { regex, errorId } = fields[fieldName];
    const isValid = regex.test(field.value.trim());
    const errorDiv = document.getElementById(errorId);

    if (!isValid && field.value.trim() !== "") {
        errorDiv.style.display = "block";
        field.style.border = "1px solid red";
    } else {
        errorDiv.style.display = "none";
        field.style.border = "1px solid #ccc";
    }
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
