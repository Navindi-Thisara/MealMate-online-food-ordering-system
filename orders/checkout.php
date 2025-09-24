<?php
session_start();
// Include the necessary files
require_once __DIR__ . '/../includes/menu_header.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../cart/cart_controller.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($conn, $user_id);
$cart_total = calculateCartTotal($conn, $user_id);
$delivery_fee = 250.00;
$grand_total = $cart_total + $delivery_fee;

// Handle order confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_order'])) {
        // Process order with address details
        $address = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $postal_code = $_POST['postal_code'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $special_instructions = $_POST['special_instructions'] ?? '';
        
        // Validate required fields
        if (empty($address) || empty($city) || empty($postal_code) || empty($phone)) {
            $_SESSION['order_error'] = "Please fill in all required address fields.";
        } else {
            // In a real application, you would save the order to database
            // For this example, we'll just clear the cart and show success
            if (clearCart($conn, $user_id)) {
                $_SESSION['order_success'] = "Your order has been placed successfully!";
                $_SESSION['order_details'] = [
                    'address' => $address,
                    'city' => $city,
                    'postal_code' => $postal_code,
                    'phone' => $phone,
                    'special_instructions' => $special_instructions,
                    'total' => $grand_total
                ];
                header("Location: checkout.php");
                exit();
            } else {
                $_SESSION['order_error'] = "There was an error placing your order. Please try again.";
                header("Location: checkout.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MealMate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Checkout Page Styles - Matching Cart Theme */
        * {
            box-sizing: border-box;
        }

        body {
            background-color: #000;
            color: #fff;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            font-size: 16px;
        }

        .main-container {
            display: block !important;
            min-height: 100vh;
            padding-top: 80px;
            width: 100%;
            overflow-x: hidden;
        }

        .sidebar {
            display: none !important;
        }

        .content {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            overflow-x: hidden;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .page-header .icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            display: block;
            color: #FF4500;
        }

        .page-header h1 {
            font-size: 2.8rem;
            color: #FF4500;
            margin: 0;
            font-weight: 700;
        }

        .page-header p {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.7);
            margin: 1rem 0;
            line-height: 1.5;
        }

        .checkout-container {
            display: flex;
            flex-direction: row;
            gap: 2rem;
            align-items: flex-start;
            width: 100%;
        }

        /* Order Summary Section */
        .order-summary-section {
            flex: 1;
            background: linear-gradient(135deg, #111, #1a1a1a);
            border-radius: 15px;
            border: 2px solid #FF4500;
            padding: 2rem;
            box-shadow: 0 6px 20px rgba(255, 69, 0, 0.15);
            overflow: hidden;
        }

        .section-title {
            font-size: 1.8rem;
            color: #FF4500;
            border-bottom: 3px solid #FF4500;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        .order-items {
            list-style: none;
            padding: 0;
            margin-bottom: 2rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem;
            border-bottom: 1px solid rgba(255, 69, 0, 0.2);
            transition: all 0.3s ease;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .order-item:hover {
            background: linear-gradient(135deg, rgba(255, 69, 0, 0.05), rgba(255, 107, 53, 0.03));
        }

        .item-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        .item-image {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            overflow: hidden;
            background: linear-gradient(135deg, #FF4500, #FF6B35);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 3px 8px rgba(255, 69, 0, 0.2);
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details h4 {
            font-size: 1.3rem;
            color: #FF4500;
            margin: 0 0 0.3rem;
            font-weight: 600;
        }

        .item-details .item-price {
            font-size: 1.1rem;
            color: #FFD700;
            font-weight: 600;
        }

        .item-quantity {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            background: rgba(255, 69, 0, 0.1);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 69, 0, 0.3);
        }

        .order-totals {
            border-top: 2px dashed rgba(255, 69, 0, 0.3);
            padding-top: 1.5rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }

        .total-row span {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .total-row.grand-total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #FF4500;
            border-top: 2px dashed #FF4500;
            padding-top: 1rem;
            margin-top: 1rem;
        }

        /* Order Details Section */
        .order-details-section {
            flex: 1;
            background: linear-gradient(135deg, #111, #1a1a1a);
            border-radius: 15px;
            border: 2px solid #FF4500;
            padding: 2rem;
            box-shadow: 0 6px 20px rgba(255, 69, 0, 0.15);
            height: fit-content;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 1.1rem;
            color: #FF4500;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 1rem;
            background: #000;
            border: 2px solid #FF4500;
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #FF6B35;
            box-shadow: 0 0 0 3px rgba(255, 69, 0, 0.2);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .required::after {
            content: " *";
            color: #FF4500;
        }

        .checkout-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-primary, .btn-secondary {
            display: block;
            width: 100%;
            padding: 1.2rem;
            text-align: center;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.2rem;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FF4500, #FF6B35);
            color: #000;
            box-shadow: 0 4px 12px rgba(255, 69, 0, 0.35);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #e63e00, #FF5A29);
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(255, 69, 0, 0.5);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #444, #666);
            color: #fff;
            border: 2px solid #666;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #555, #777);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.15);
        }

        /* Alerts */
        .alert {
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #155724, #1e7e34);
            color: #fff;
            border: 2px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #721c24, #c82333);
            color: #fff;
            border: 2px solid #dc3545;
        }

        .empty-cart-message {
            text-align: center;
            padding: 3rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.3rem;
        }

        .empty-cart-message .icon {
            font-size: 4rem;
            color: #FF4500;
            margin-bottom: 1rem;
            display: block;
        }

        /* Success Order Details */
        .order-success-details {
            background: linear-gradient(135deg, rgba(255, 69, 0, 0.1), rgba(255, 107, 53, 0.05));
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            border: 1px solid rgba(255, 69, 0, 0.3);
        }

        .order-success-details h3 {
            color: #FF4500;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid rgba(255, 69, 0, 0.3);
            padding-bottom: 0.5rem;
        }

        .order-detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
        }

        .order-detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .checkout-container {
                flex-direction: column;
            }
            
            .order-summary-section, .order-details-section {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .content {
                padding: 1.5rem;
            }
            
            .page-header h1 {
                font-size: 2.2rem;
            }
            
            .page-header p {
                font-size: 1.1rem;
            }
            
            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .item-info {
                width: 100%;
            }
            
            .item-quantity {
                align-self: flex-end;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="content">
            <div class="page-header">
                <i class="fas fa-credit-card icon"></i>
                <h1>Checkout</h1>
                <p>Complete your order with delivery details</p>
            </div>

            <?php
            // Display success or error messages
            if (isset($_SESSION['order_success'])) {
                echo '<div class="alert alert-success">' . 
                     '<i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>' . 
                     htmlspecialchars($_SESSION['order_success']) . 
                     '</div>';
                unset($_SESSION['order_success']);
            }
            if (isset($_SESSION['order_error'])) {
                echo '<div class="alert alert-danger">' . 
                     '<i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i>' . 
                     htmlspecialchars($_SESSION['order_error']) . 
                     '</div>';
                unset($_SESSION['order_error']);
            }
            ?>

            <?php if (empty($cart_items)): ?>
                <div class="empty-cart-message">
                    <i class="fas fa-shopping-cart icon"></i>
                    <h3>Your cart is empty</h3>
                    <p>Please add items to your cart before proceeding to checkout.</p>
                    <a href="../food_management/menu.php" class="btn-primary" style="margin-top: 1.5rem; display: inline-block; text-decoration: none;">
                        <i class="fas fa-utensils" style="margin-right: 0.5rem;"></i>Browse Menu
                    </a>
                </div>
            <?php else: ?>
                <div class="checkout-container">
                    <div class="order-summary-section">
                        <h2 class="section-title">Order Summary</h2>
                        
                        <ul class="order-items">
                            <?php foreach ($cart_items as $item): ?>
                                <li class="order-item">
                                    <div class="item-info">
                                        <div class="item-image">
                                            <img src="../assets/images/menu/<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['food_name']); ?>"
                                                 onerror="this.src='../assets/images/menu/default.jpg'; this.onerror=null;">
                                        </div>
                                        <div class="item-details">
                                            <h4><?php echo htmlspecialchars($item['food_name']); ?></h4>
                                            <p class="item-price">Rs.<?php echo htmlspecialchars(number_format($item['price'], 2)); ?> each</p>
                                        </div>
                                    </div>
                                    <span class="item-quantity">Qty: <?php echo htmlspecialchars($item['quantity']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="order-totals">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>Rs.<?php echo htmlspecialchars(number_format($cart_total, 2)); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Delivery Fee:</span>
                                <span>Rs.<?php echo htmlspecialchars(number_format($delivery_fee, 2)); ?></span>
                            </div>
                            <div class="total-row grand-total">
                                <span>Total Amount:</span>
                                <span>Rs.<?php echo htmlspecialchars(number_format($grand_total, 2)); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-details-section">
                        <?php if (isset($_SESSION['order_success']) && isset($_SESSION['order_details'])): ?>
                            <h2 class="section-title">Order Confirmed!</h2>
                            <p>Thank you for your order. Here are your order details:</p>
                            
                            <div class="order-success-details">
                                <h3>Delivery Information</h3>
                                <div class="order-detail-item">
                                    <span>Address:</span>
                                    <span><?php echo htmlspecialchars($_SESSION['order_details']['address']); ?></span>
                                </div>
                                <div class="order-detail-item">
                                    <span>City:</span>
                                    <span><?php echo htmlspecialchars($_SESSION['order_details']['city']); ?></span>
                                </div>
                                <div class="order-detail-item">
                                    <span>Postal Code:</span>
                                    <span><?php echo htmlspecialchars($_SESSION['order_details']['postal_code']); ?></span>
                                </div>
                                <div class="order-detail-item">
                                    <span>Phone:</span>
                                    <span><?php echo htmlspecialchars($_SESSION['order_details']['phone']); ?></span>
                                </div>
                                <?php if (!empty($_SESSION['order_details']['special_instructions'])): ?>
                                <div class="order-detail-item">
                                    <span>Special Instructions:</span>
                                    <span><?php echo htmlspecialchars($_SESSION['order_details']['special_instructions']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="order-detail-item grand-total">
                                    <span>Total Paid:</span>
                                    <span>Rs.<?php echo htmlspecialchars(number_format($_SESSION['order_details']['total'], 2)); ?></span>
                                </div>
                            </div>
                            
                            <div class="checkout-actions">
                                <a href="../food_management/menu.php" class="btn-primary">
                                    <i class="fas fa-utensils" style="margin-right: 0.5rem;"></i>Order Again
                                </a>
                                <a href="../index.php" class="btn-secondary">
                                    <i class="fas fa-home" style="margin-right: 0.5rem;"></i>Back to Home
                                </a>
                            </div>
                        <?php else: ?>
                            <h2 class="section-title">Delivery Details</h2>
                            <form action="checkout.php" method="POST">
                                <div class="form-group">
                                    <label for="address" class="required">Delivery Address</label>
                                    <input type="text" id="address" name="address" placeholder="Enter your full address" required 
                                           value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="city" class="required">City</label>
                                    <input type="text" id="city" name="city" placeholder="Enter your city" required
                                           value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="postal_code" class="required">Postal Code</label>
                                    <input type="text" id="postal_code" name="postal_code" placeholder="Enter postal code" required
                                           value="<?php echo isset($_POST['postal_code']) ? htmlspecialchars($_POST['postal_code']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone" class="required">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="special_instructions">Special Instructions (Optional)</label>
                                    <textarea id="special_instructions" name="special_instructions" placeholder="Any special delivery instructions..."><?php echo isset($_POST['special_instructions']) ? htmlspecialchars($_POST['special_instructions']) : ''; ?></textarea>
                                </div>
                                
                                <div class="checkout-actions">
                                    <button type="submit" name="confirm_order" class="btn-primary">
                                        <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>Confirm Order
                                    </button>
                                    <a href="cart.php" class="btn-secondary">
                                        <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>Back to Cart
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Add focus effects to form inputs
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });
            
            // Phone number formatting
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 10) value = value.substring(0, 10);
                    e.target.value = value;
                });
            }
        });
    </script>
</body>
</html>

<?php
include __DIR__ . '/../includes/simple_footer.php';
?>