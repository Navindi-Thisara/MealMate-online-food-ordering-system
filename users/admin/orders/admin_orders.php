<?php
session_start();
require_once __DIR__ . '/../../../includes/db_connect.php';
require_once __DIR__ . '/../../../orders/order_controller.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../users/login.php");
    exit();
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get orders and statistics
$orders = getAllOrders($conn, $status_filter, $date_from ?: null, $date_to ?: null, $per_page, $offset);
$total_orders = getUserOrdersCount($conn, 0, $status_filter); // 0 for all users
$statistics = getOrderStatistics($conn, $date_from ?: null, $date_to ?: null);
$urgent_orders = getOrdersRequiringAttention($conn);

$page_title = "Orders Dashboard - MealMate Admin";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* === Global Styles === */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #fff;
            scroll-behavior: smooth;
            background-color: #0d0d0d;
            overflow-x: hidden;
            position: relative;
        }

        /* === Navbar Styles === */
        .navbar {
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #FF4500;
            padding: 20px 50px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            z-index: 20;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            color: #FF4500;
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            text-shadow: 3px 3px 6px #000;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-menu a {
            color: #fff;
            text-decoration: none;
            font-size: 18px;
            font-weight: 400;
            letter-spacing: 0.5px;
            padding: 0;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #FF4500;
            transition: width 0.3s ease;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            color: #FF4500;
        }

        .nav-menu a:hover::after,
        .nav-menu a.active::after {
            width: 100%;
        }

        /* === Main Content Container === */
        .admin-container {
            width: 100%;
            max-width: 1400px;
            margin: 120px auto 2rem auto;
            padding: 0 50px;
        }

        /* === Header Section === */
        .dashboard-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 0.5rem 0;
            position: relative;
        }

        .dashboard-header h1 {
            color: #ff4500;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .dashboard-header p {
            color: #cccccc;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .dashboard-header::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            width: 100vw;
            height: 2px;
            background-color: #ff4500;
            margin-left: calc(-50vw + 50%);
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(20,20,20,0.95);
            border-radius: 12px;
            border: 2px solid #FF4500;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(255,69,0,0.5);
            text-align: center;
        }

        .stat-icon {
            font-size: 2.5rem;
            color: #FF4500;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #FFD700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .stat-change {
            font-size: 0.9rem;
            color: #32CD32;
        }

        /* Urgent Orders Alert */
        .urgent-alerts {
            background: rgba(20,20,20,0.95);
            border-radius: 12px;
            border: 2px solid #dc3545;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(220, 53, 69, 0.3);
        }

        .urgent-alerts h3 {
            color: #dc3545;
            margin: 0 0 1rem;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .urgent-order {
            background: rgba(220, 53, 69, 0.1);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .urgent-order:last-child {
            margin-bottom: 0;
        }

        /* Filters Section */
        .filters-section {
            background: rgba(20,20,20,0.95);
            border-radius: 12px;
            border: 2px solid #FF4500;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(255,69,0,0.5);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            color: #FF4500;
            font-weight: 600;
            font-size: 1rem;
        }

        .filter-input,
        .filter-select {
            background: #000;
            border: 2px solid #FF4500;
            color: #fff;
            padding: 0.8rem;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #FF6B35;
            box-shadow: 0 0 0 3px rgba(255, 69, 0, 0.2);
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
            align-self: end;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: #ff4500;
            color: #000;
            box-shadow: 0 4px 12px rgba(255, 69, 0, 0.35);
        }

        .btn-primary:hover {
            background: #e65c00;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(255, 69, 0, 0.5);
        }

        .btn-secondary {
            background: #555;
            color: #fff;
            border: 2px solid #666;
        }

        .btn-secondary:hover {
            background: #777;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.15);
        }

        .btn-success {
            background: #28a745;
            color: #fff;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.35);
        }

        .btn-danger {
            background: #dc3545;
            color: #fff;
            border: 2px solid #dc3545;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        /* Orders Table */
        .orders-table-container {
            background: rgba(20,20,20,0.95);
            border-radius: 12px;
            border: 2px solid #FF4500;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(255,69,0,0.5);
            overflow-x: auto;
            margin-bottom: 2rem;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
            color: #fff;
        }

        .orders-table th,
        .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 69, 0, 0.2);
        }

        .orders-table th {
            background-color: #ff4500;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .orders-table tbody tr {
            transition: all 0.3s ease;
        }

        .orders-table tbody tr:hover {
            background: rgba(255, 69, 0, 0.05);
            transform: translateX(5px);
        }

        .order-number {
            color: #FF4500;
            font-weight: 600;
            text-decoration: none;
        }

        .order-number:hover {
            text-decoration: underline;
        }

        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .customer-name {
            font-weight: 600;
            color: #FFD700;
        }

        .customer-email {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .order-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(255, 215, 0, 0.2);
            color: #FFD700;
            border: 1px solid rgba(255, 215, 0, 0.5);
        }

        .status-confirmed {
            background: rgba(0, 191, 255, 0.2);
            color: #00BFFF;
            border: 1px solid rgba(0, 191, 255, 0.5);
        }

        .status-preparing {
            background: rgba(255, 140, 0, 0.2);
            color: #FF8C00;
            border: 1px solid rgba(255, 140, 0, 0.5);
        }

        .status-ready {
            background: rgba(50, 205, 50, 0.2);
            color: #32CD32;
            border: 1px solid rgba(50, 205, 50, 0.5);
        }

        .status-out_for_delivery {
            background: rgba(255, 107, 53, 0.2);
            color: #FF6B35;
            border: 1px solid rgba(255, 107, 53, 0.5);
        }

        .status-delivered {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }

        .status-cancelled {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }

        .order-amount {
            font-weight: bold;
            color: #FFD700;
            font-size: 1.1rem;
        }

        .order-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 0.3rem 0.6rem;
            border-radius: 5px;
            font-size: 0.8rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            border: none;
            cursor: pointer;
        }

        .action-btn:hover {
            transform: scale(1.05);
        }

        /* Status Update Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: #222;
            margin: 10% auto;
            padding: 2rem;
            border: 2px solid #FF4500;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            color: #fff;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            color: #FF4500;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .close {
            color: #FF4500;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #FF6B35;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 0.8rem 1.2rem;
            background: rgba(20,20,20,0.95);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            border: 2px solid rgba(255, 69, 0, 0.3);
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .pagination a:hover {
            background: #ff4500;
            color: #000;
            transform: translateY(-2px);
        }

        .pagination .current {
            background: #ff4500;
            color: #000;
            border-color: #FF4500;
        }

        /* === Footer Styles === */
        .simple-footer {
            background-color: #0d0d0d;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            position: relative;
            width: 100%;
            margin-top: 3rem;
        }

        .simple-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #FF4500;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .admin-container {
                padding: 1.5rem 20px;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }

            .filters-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .filter-buttons {
                align-self: stretch;
                justify-content: center;
            }

            .orders-table-container {
                padding: 1rem;
            }

            .urgent-order {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            
            .admin-container {
                margin: 100px auto 1.5rem auto;
                padding: 0 15px;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-icon {
                font-size: 2rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .orders-table th,
            .orders-table td {
                padding: 0.8rem 0.5rem;
                font-size: 0.9rem;
            }

            .order-actions {
                flex-direction: column;
                gap: 0.3rem;
            }

            .action-btn {
                text-align: center;
                justify-content: center;
            }

            .modal-content {
                margin: 5% auto;
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 10px 1rem;
            }
            
            .nav-logo {
                font-size: 24px;
            }
            
            .nav-menu {
                gap: 1rem;
            }
            
            .nav-menu a {
                font-size: 14px;
            }

            .admin-container {
                padding: 1rem 10px;
            }

            .dashboard-header h1 {
                font-size: 1.5rem;
            }

            .dashboard-header p {
                font-size: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.8rem;
            }

            .orders-table-container {
                padding: 0.8rem;
            }

            .filters-section,
            .urgent-alerts {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">MealMate</h1>
            <ul class="nav-menu">
                <li><a href="/MealMate-online-food-ordering-system/index.php">Home</a></li>
                <li><a href="../../../users/admin/admin_dashboard.php">Dashboard</a></li>
                <li><a href="../../../users/admin/manage_food.php">Manage Food</a></li>
                <li><a href="/MealMate-online-food-ordering-system/users/admin/orders/admin_orders.php" class="active">Manage Orders</a></li>
                <li><a href="../../../users/admin/manage_users.php">Manage Users</a></li>
                <li><a href="../../../users/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="admin-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-tachometer-alt"></i> Orders Dashboard</h1>
            <p>Manage and monitor all orders in real-time</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-value"><?php echo number_format($statistics['overall']['total_orders']); ?></div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-change">+<?php echo count($orders); ?> today</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-value"><?php echo number_format($statistics['overall']['pending_orders'] + $statistics['overall']['confirmed_orders']); ?></div>
                <div class="stat-label">Active Orders</div>
                <div class="stat-change">Need attention</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
                <div class="stat-value">Rs.<?php echo number_format($statistics['overall']['total_revenue'] ?? 0, 0); ?></div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-change">Rs.<?php echo number_format($statistics['overall']['average_order_value'] ?? 0, 0); ?> avg</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value"><?php echo number_format($statistics['overall']['delivered_orders']); ?></div>
                <div class="stat-label">Delivered</div>
                <div class="stat-change"><?php echo round(($statistics['overall']['delivered_orders'] / max($statistics['overall']['total_orders'], 1)) * 100, 1); ?>% success rate</div>
            </div>
        </div>

        <?php if (!empty($urgent_orders)): ?>
        <div class="urgent-alerts">
            <h3><i class="fas fa-exclamation-triangle"></i> Orders Requiring Attention (<?php echo count($urgent_orders); ?>)</h3>
            <?php foreach (array_slice($urgent_orders, 0, 5) as $urgent): ?>
            <div class="urgent-order">
                <div>
                    <strong>#<?php echo htmlspecialchars($urgent['order_number'] ?? 'N/A'); ?></strong> - 
                    <?php echo htmlspecialchars($urgent['full_name']); ?>
                    <small>(<?php echo $urgent['minutes_since_order']; ?> min ago)</small>
                </div>
                <span class="order-status-badge status-<?php echo $urgent['order_status']; ?>">
                    <?php echo formatOrderStatus($urgent['order_status']); ?>
                </span>
            </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="filters-section">
            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="status">Order Status</label>
                        <select name="status" id="status" class="filter-select">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Orders</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="preparing" <?php echo $status_filter === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                            <option value="ready" <?php echo $status_filter === 'ready' ? 'selected' : ''; ?>>Ready</option>
                            <option value="out_for_delivery" <?php echo $status_filter === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                            <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="date_from">From Date</label>
                        <input type="date" name="date_from" id="date_from" class="filter-input" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="date_to">To Date</label>
                        <input type="date" name="date_to" id="date_to" class="filter-input" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="orders-table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: rgba(255, 255, 255, 0.6);">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                            No orders found matching the selected criteria.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr data-order-id="<?php echo $order['order_id']; ?>">
                            <td>
                                <a href="admin_order_details.php?id=<?php echo $order['order_id']; ?>" class="order-number">
                                    #<?php echo htmlspecialchars($order['order_number']); ?>
                                </a>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-name">
                                        <?php echo htmlspecialchars($order['full_name']); ?>
                                    </div>
                                    <div class="customer-email"><?php echo htmlspecialchars($order['email']); ?></div>
                                </div>
                            </td>
                            <td>
                                <span style="color: #FFD700; font-weight: 600;">
                                    <?php echo $order['item_count']; ?> items
                                </span>
                            </td>
                            <td>
                                <div class="order-amount">Rs.<?php echo number_format($order['grand_total'], 2); ?></div>
                            </td>
                            <td>
                                <span class="order-status-badge status-<?php echo $order['order_status']; ?>">
                                    <i class="fas fa-circle"></i>
                                    <?php echo formatOrderStatus($order['order_status']); ?>
                                </span>
                            </td>
                            <td>
                                <div><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                                <small style="color: rgba(255, 255, 255, 0.6);">
                                    <?php echo date('g:i A', strtotime($order['created_at'])); ?>
                                </small>
                            </td>
                            <td>
                                <div class="order-actions">
                                    <a href="admin_order_details.php?id=<?php echo $order['order_id']; ?>" 
                                       class="action-btn btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if (!in_array($order['order_status'], ['delivered', 'cancelled'])): ?>
                                    <button onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, '<?php echo $order['order_status']; ?>')" 
                                            class="action-btn btn-success">
                                        <i class="fas fa-edit"></i> Update
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_orders > $per_page): ?>
        <div class="pagination">
            <?php 
            $total_pages = ceil($total_orders / $per_page);
            $query_params = http_build_query(array_filter([
                'status' => $status_filter,
                'date_from' => $date_from,
                'date_to' => $date_to
            ]));
            ?>
            
            <?php if ($page > 1): ?>
                <a href="?<?php echo $query_params; ?>&page=1">First</a>
                <a href="?<?php echo $query_params; ?>&page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>

            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?<?php echo $query_params; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?<?php echo $query_params; ?>&page=<?php echo $page + 1; ?>">Next</a>
                <a href="?<?php echo $query_params; ?>&page=<?php echo $total_pages; ?>">Last</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Update Order Status</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="statusUpdateForm">
                <input type="hidden" id="orderId" name="order_id">
                <div class="filter-group" style="margin-bottom: 1rem;">
                    <label for="newStatus">New Status</label>
                    <select id="newStatus" name="new_status" class="filter-select" required>
                        <option value="">Select Status</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="preparing">Preparing</option>
                        <option value="ready">Ready</option>
                        <option value="out_for_delivery">Out for Delivery</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="filter-group" style="margin-bottom: 1.5rem;">
                    <label for="changeReason">Reason (Optional)</label>
                    <input type="text" id="changeReason" name="reason" class="filter-input" 
                           placeholder="Optional reason for status change">
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <div class="simple-footer">
        &copy; <?= date('Y') ?> MealMate. All rights reserved.
    </div>

    <script>
        function updateOrderStatus(orderId, currentStatus) {
            document.getElementById('orderId').value = orderId;
            document.getElementById('statusModal').style.display = 'block';
            
            // Filter status options based on current status
            const statusSelect = document.getElementById('newStatus');
            const options = statusSelect.querySelectorAll('option');
            
            // Define valid transitions
            const validTransitions = {
                'pending': ['confirmed', 'cancelled'],
                'confirmed': ['preparing', 'cancelled'],
                'preparing': ['ready', 'cancelled'],
                'ready': ['out_for_delivery'],
                'out_for_delivery': ['delivered'],
                'delivered': [],
                'cancelled': []
            };
            
            options.forEach(option => {
                if (option.value === '') return; // Keep the default option
                
                const isValid = validTransitions[currentStatus]?.includes(option.value) || false;
                option.style.display = isValid ? 'block' : 'none';
                option.disabled = !isValid;
            });
        }

        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
            document.getElementById('statusUpdateForm').reset();
        }

        // Handle status update form submission
        document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order status updated successfully!');
                    closeModal();
                    location.reload();
                } else {
                    alert('Error updating order status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the order status');
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('statusModal');
            if (event.target === modal) {
                closeModal();
            }
        });

        // Auto-refresh the page every 60 seconds for real-time updates
        setInterval(function() {
            // Only auto-refresh if no modals are open
            if (document.getElementById('statusModal').style.display !== 'block') {
                location.reload();
            }
        }, 60000);

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>