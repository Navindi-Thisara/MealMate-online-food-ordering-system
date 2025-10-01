<?php
session_start();
require_once __DIR__ . '/../includes/menu_header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../users/login.php');
    exit();
}

// Check if payment was successful
if (!isset($_SESSION['payment_success']) || !isset($_SESSION['order_details'])) {
    header('Location: checkout.php');
    exit();
}

$order_details = $_SESSION['order_details'];

// Set default values if keys are missing
$order_details = array_merge([
    'address' => 'Address not available',
    'total' => '0.00',
    'order_id' => 'N/A',
    'created_at' => date('Y-m-d H:i:s')
], $order_details ?? []);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - MealMate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        }

        .main-container {
            display: block !important;
            min-height: 100vh;
            padding-top: 80px;
            width: 100%;
            overflow-x: hidden;
        }

        .content {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .success-box {
            background: linear-gradient(135deg, #111, #1a1a1a);
            border-radius: 15px;
            border: 2px solid #28a745;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(40, 167, 69, 0.3);
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745, #32CD32);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon i {
            font-size: 3.5rem;
            color: white;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        h1 {
            font-size: 2.5rem;
            color: #28a745;
            margin: 0 0 1rem;
        }

        .subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
        }

        .order-details-box {
            background: rgba(255, 69, 0, 0.05);
            border: 2px solid rgba(255, 69, 0, 0.3);
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #FF4500;
            font-weight: 600;
        }

        .detail-value {
            color: rgba(255, 255, 255, 0.9);
        }

        .detail-row.total {
            font-size: 1.3rem;
            font-weight: bold;
            color: #FFD700;
            border-top: 2px solid rgba(255, 69, 0, 0.5);
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            padding: 1.2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FF4500, #FF6B35);
            color: #000;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 69, 0, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #444, #666);
            color: #fff;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.15);
        }

        .payment-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 1rem;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }

        @media (max-width: 768px) {
            .success-box {
                padding: 2rem;
            }

            h1 {
                font-size: 2rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="content">
            <div class="success-box">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>

                <h1>Payment Successful!</h1>
                <p class="subtitle">Your order has been confirmed and will be delivered soon</p>

                <div class="payment-badge">
                    <i class="fas fa-credit-card"></i>
                    Payment Completed
                </div>

                <div class="order-details-box">
                    <div class="detail-row">
                        <span class="detail-label">Order Number:</span>
                        <span class="detail-value"><strong><?php echo htmlspecialchars($order_details['order_number']); ?></strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Delivery Address:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order_details['address']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">City:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order_details['city']); ?>, <?php echo htmlspecialchars($order_details['postal_code']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Contact Number:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order_details['phone']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Method:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order_details['payment_method']); ?></span>
                    </div>
                    <?php if (isset($order_details['card_last4'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Card Used:</span>
                        <span class="detail-value">**** **** **** <?php echo htmlspecialchars($order_details['card_last4']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-row total">
                        <span class="detail-label">Amount Paid:</span>
                        <span class="detail-value">Rs.<?php echo number_format($order_details['total'], 2); ?></span>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="../orders/order_details.php?id=<?php echo $order_details['order_id']; ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i>
                        View Order Details
                    </a>
                    <a href="../orders/my_orders.php" class="btn btn-secondary">
                        <i class="fas fa-receipt"></i>
                        My Orders
                    </a>
                </div>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.95rem; margin: 0;">
                        <i class="fas fa-info-circle"></i>
                        A confirmation email has been sent to your registered email address.
                        <br>
                        Estimated delivery time: 30-45 minutes
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Clear payment session data after displaying
        <?php 
        // Keep the order_details for display but mark payment as shown
        unset($_SESSION['payment_success']);
        ?>
        
        // Auto-redirect to orders page after 10 seconds
        setTimeout(function() {
            window.location.href = '../orders/my_orders.php';
        }, 10000);
    </script>
</body>
</html>

<?php 
// Clear order details after page loads
unset($_SESSION['order_details']); 
include __DIR__ . '/../includes/simple_footer.php'; 
?>