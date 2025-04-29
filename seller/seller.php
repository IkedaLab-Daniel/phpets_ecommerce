<?php
    session_start();
    include '../includes/db_connect.php';
    include '../includes/header.php';
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
        header("Location: ../login.php");
        exit();
    }

    $seller_id = $_SESSION['user_id'];
    $first_name = $_SESSION['first_name'];
    $middle_name = $_SESSION['middle_name'];
    $last_name = $_SESSION['last_name'];
    $address = $_SESSION['address'];
    $email = $_SESSION['email'];
    $profile_photo = $_SESSION['profile_photo']; 

    // ? Fetch all products listed by the seller, including category name
    $products_query = "
        SELECT p.*, c.name AS category_name 
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.seller_id = ?
        ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($products_query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $products_result = $stmt->get_result();

    // ? Fetch all orders related to the seller, including buyer's name
    $orders_query = "
        SELECT DISTINCT o.order_id, o.buyer_id, o.total_price, o.order_date, o.status, u.first_name, u.last_name
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        JOIN users u ON o.buyer_id = u.user_id
        WHERE p.seller_id = ?
        ORDER BY 
            CASE 
                WHEN o.status = 'pending' THEN 1
                ELSE 2
            END, 
            o.order_date DESC";
    $stmt = $conn->prepare($orders_query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $orders_result = $stmt->get_result();


    // Calculate total earnings from delivered orders
    $total_earnings_query = "
        SELECT SUM(o.total_price) AS total_earnings
        FROM orders o
        WHERE o.order_id IN (
            SELECT DISTINCT o.order_id
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE p.seller_id = ? AND o.status = 'delivered'
        )";
    $stmt = $conn->prepare($total_earnings_query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $total_earnings_result = $stmt->get_result();
    $total_earnings_row = $total_earnings_result->fetch_assoc();
    $total_earnings = $total_earnings_row['total_earnings'] ?? 0; 
    
    // ! Fetch all delivered orders for debugging only
    $debug_orders_query = "
        SELECT DISTINCT o.order_id, o.total_price
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE p.seller_id = ? AND o.status = 'delivered'";
    $stmt = $conn->prepare($debug_orders_query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $debug_orders_result = $stmt->get_result();


    // ? Calculate total orders for the seller
    $total_orders_query = "
        SELECT COUNT(DISTINCT o.order_id) AS total_orders
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE p.seller_id = ?";
    $stmt = $conn->prepare($total_orders_query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $total_orders_result = $stmt->get_result();
    $total_orders_row = $total_orders_result->fetch_assoc();
    $total_orders = $total_orders_row['total_orders'] ?? 0; 
?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/phpets/assets/css/index.css" />
        <link rel="stylesheet" href="/phpets/assets/css/seller.css" />
        <link rel="icon" type="image/svg" href="/phpets/assets/images/paw.svg" />
        <title><?php echo $first_name . ' ' . $last_name; ?> </title>
    </head>

    <body>
        <div id="seller">
            <div class="left">
                <div class="user-details">
                    <img src="../uploads/<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Picture" width="100">
                    <span class="fullname"> <?php echo $first_name . ' ' . $middle_name . ' ' . $last_name; ?></span>
                    <span class="role"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <span class="address"><?php echo $address; ?></span>
                </div>

                <div class="animate-fadein-left">
                    <a class="link-navs" href="add-order.php">
                        <img src="/phpets/assets/images/add.svg">
                        <span>Add Product</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#cart-details">
                        <img src="/phpets/assets/images/cart-bag.svg">
                        <span>My Listings</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#purchased-details">
                        <img src="/phpets/assets/images/purchase.svg">
                        <span>Orders</span>
                    </a>
                </div>

                <div class="animate-fadein-left">
                    <a class="link-navs" href="#edit-profile">
                        <img src="/phpets/assets/images/edit-profile.svg">
                        <span>Edit Profile</span>
                    </a>
                </div>

                
            </div>
            <div class="right">
                <div class="dashboard">
                    <div id="total-sales" class="data-card">
                        <div class="heading-2">
                            <img src="/phpets/assets/images/earning.svg" alt="">
                            <h2>Total Sales</h2>
                        </div>
                        <p><strong>₱<?php echo number_format($total_earnings, 2); ?></strong></p>
                    </div>
                    <div id="total-orders" class="data-card">
                        <div class="heading-2">
                            <img src="/phpets/assets/images/transaction.svg" alt="">
                            <h2>Total Orders</h2>
                        </div>
                        <p><strong><?php echo $total_orders; ?></strong></p>
                    </div>
                </div>
                
                <div id="cart-details">
                    <div class="heading" style="margin-top: 10px;">
                        <img src="/phpets/assets/images/cart-bag.svg" alt="">
                        <h2>My Listings</h2>
                    </div>
                    <div class="list-table-content">
                        <?php if ($products_result->num_rows > 0): ?>
                            <?php while ($product = $products_result->fetch_assoc()): ?>
                                <div class="product-card">
                                    <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" style="margin-right: 10px;" alt="Product Image" width="100">
                                    <span class="category-tag"> <?php echo htmlspecialchars($product['category_name']); ?></span>
                                    <div class="product-card-detail">
                                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p> <?php echo htmlspecialchars($product['description']); ?></p>
                                        <p>Stock: <?php echo $product['stock']; ?> pcs</p>
                                        <p>₱<?php echo number_format($product['price'], 2); ?></p>
                                        <p class="<?php echo ($product['status']); ?> status"> <?php echo ucfirst($product['status']); ?></p>
                                    </div>      
                                    <div class="product-card-footer">
                                        <button class="delete cool-btn">Unlist</button>
                                    </div>                              
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No products listed yet. <a href="/phpets/seller/add_product.php" class="add-product-link">Add a Product</a></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div id="purchased-details">
                    <div class="heading">
                        <img src="/phpets/assets/images/purchase.svg" alt="">
                        <h2>Orders</h2>
                    </div>
                    <?php if ($orders_result->num_rows > 0): ?>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <div class="order-box">
                            <div class="order-box-head">
                                <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
                                <p><strong>Buyer:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                                <p><strong>Total:</strong> ₱<?php echo number_format($order['total_price'], 2); ?></p>
                                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                                <p class="<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></p>
                            </div>

                                <div class="order-box-products">
                                    <?php
                                        $order_id = $order['order_id'];
                                        $items_query = "
                                            SELECT oi.quantity, p.name, p.image, p.price
                                            FROM order_items oi
                                            JOIN products p ON oi.product_id = p.product_id
                                            WHERE oi.order_id = ?";
                                        $items_stmt = $conn->prepare($items_query);
                                        $items_stmt->bind_param("i", $order_id);
                                        $items_stmt->execute();
                                        $items_result = $items_stmt->get_result();

                                        while ($item = $items_result->fetch_assoc()):
                                    ?>
                                        <div class="order-item">
                                            <div class="brix">
                                                <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" width="50">
                                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                                            </div>
                                            <p class="center-33"><?php echo $item['quantity']; ?> pcs</p>
                                            <p class="center-33">₱ <?php echo number_format($item['price'], 2); ?>/pcs</p>
                                        </div>
                                    <?php endwhile; ?>
                                    <div class="action-container">
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <form method="POST" action="actions.php">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <input type="hidden" name="action" value="shipped">
                                                <button class="mark-shipped cool-btn" type="submit">Mark as Shipped</button>
                                            </form>
                                            <form method="POST" action="actions.php">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <input type="hidden" name="action" value="cancelled">
                                                <button class="mark-cancelled cool-btn" type="submit">Mark as Cancelled</button>
                                            </form>
                                            <?php elseif ($order['status'] === 'shipped'): ?>
                                                <form method="POST" action="actions.php">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                    <input type="hidden" name="action" value="delivered">
                                                    <button class="mark-delivered cool-btn" type="submit">Mark as Delivered</button>
                                                </form>
                                                <form method="POST" action="actions.php">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                    <input type="hidden" name="action" value="cancelled">
                                                    <button class="mark-cancelled cool-btn" type="submit">Mark as Cancelled</button>
                                                </form>
                                            <?php elseif ($order['status'] === 'cancelled' || $order['status'] === 'delivered'): ?>
                                                <form method="POST" action="actions.php">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button class="delete-order cool-btn" type="submit">Delete</button>
                                                </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No orders found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    
</html>

<?php 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>