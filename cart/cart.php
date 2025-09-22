<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
require_once 'cart_controller.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../users/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($conn, $user_id);
$cart_total = calculateCartTotal($conn, $user_id);

$page_title = "Shopping Cart - MealMate";
$page_name = "cart"; // For header highlighting

// Include header
include '../includes/menu_header.php';
?>

<!-- Include CSS -->
<link rel="stylesheet" href="cart.css">

<!-- Beautiful Confirmation Modal -->
<div class="confirmation-modal" id="confirmationModal">
    <div class="confirmation-content">
        <button class="close-confirm-btn" onclick="hideConfirmationModal()">&times;</button>
        <div class="confirmation-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="confirmation-title">Remove Item</h3>
        <p class="confirmation-message" id="confirmationMessage">Are you sure you want to remove this item from your cart?</p>
        <div class="confirmation-buttons">
            <button class="confirm-btn" id="confirmRemove">Yes, Remove</button>
            <button class="cancel-btn" id="cancelRemove">Cancel</button>
        </div>
    </div>
</div>

<div class="main-container">
    <div class="content">
        <div class="page-header">
            <i class="fas fa-shopping-cart icon"></i>
            <h1>Your Shopping Cart</h1>
            <p>Review and manage your delicious items before checkout.</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart-page">
                <i class="fas fa-box-open empty-icon"></i>
                <h3>Your cart is empty.</h3>
                <p>Looks like you haven't added anything to your cart yet.</p>
                <a href="../food_management/menu.php" class="btn-primary" style="padding: 1rem 2rem; text-decoration: none; display: inline-block; border-radius: 8px; background: linear-gradient(135deg, #FF4500, #FF6B35); color: #000; font-weight: 600; margin-top: 1rem;">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <div id="cart-items-container">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>">
                                <div class="item-image">
                                    <img src="../assets/images/menu/<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['food_name']); ?>"
                                         onerror="this.src='../assets/images/menu/default.jpg'; this.onerror=null;">
                                </div>
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['food_name']); ?></h3>
                                    <p class="item-description"><?php echo htmlspecialchars($item['description'] ?: 'Delicious food item'); ?></p>
                                    <p class="item-price">Rs.<?php echo htmlspecialchars(number_format($item['price'], 2)); ?> each</p>
                                </div>
                                <div class="quantity-controls">
                                    <button class="qty-btn" onclick="updateQuantity(<?php echo htmlspecialchars($item['cart_id']); ?>, -1)" aria-label="Decrease quantity">-</button>
                                    <input type="text" value="<?php echo htmlspecialchars($item['quantity']); ?>" class="qty-input" readonly>
                                    <button class="qty-btn" onclick="updateQuantity(<?php echo htmlspecialchars($item['cart_id']); ?>, 1)" aria-label="Increase quantity">+</button>
                                </div>
                                <div class="item-total">
                                    Rs.<?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?>
                                </div>
                                <div class="delete-btn-container">
                                    <button class="delete-btn" 
                                            onclick="showRemoveConfirm(<?php echo htmlspecialchars($item['cart_id']); ?>, '<?php echo htmlspecialchars(addslashes($item['food_name'])); ?>')" 
                                            aria-label="Remove <?php echo htmlspecialchars($item['food_name']); ?>">
                                        <i class="fas fa-trash"></i>
                                        <span class="tooltip">Remove Item</span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <h2 class="summary-title">Order Summary</h2>
                    
                    <div class="summary-row">
                        <span>Items (<?php echo count($cart_items); ?>):</span>
                        <span id="cart-subtotal">Rs.<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <span>Rs.250.00</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="cart-total">Rs.<?php echo number_format($cart_total + 250.00, 2); ?></span>
                    </div>
                    
                    <div class="checkout-actions">
                        <a href="checkout.php" class="btn-primary">
                            <i class="fas fa-credit-card" style="margin-right: 0.5rem;"></i>
                            Proceed to Checkout
                        </a>
                        <a href="../food_management/menu.php" class="btn-secondary">
                            <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include JavaScript -->
<script src="cart.js"></script>

<!-- Add Font Awesome if not already included -->
<script>
// Check if Font Awesome is loaded, if not load it
if (!document.querySelector('link[href*="font-awesome"]') && !document.querySelector('script[src*="font-awesome"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
    document.head.appendChild(link);
}
</script>

<?php include __DIR__ . '/../includes/simple_footer.php'; ?>