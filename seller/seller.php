<?php
    include '../includes/db_connect.php';
    include '../includes/header.php';
    include '../includes/protect.php';

    $view_mode = isset($_COOKIE['view']) ? $_COOKIE['view'] : 'light';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
        header("Location: ../login.php");
        exit();
    }

    if (isset($_SESSION['view'])){
        $_SESSION['view'] == 'light';
    }

    $seller_id = $_SESSION['user_id'];
    $first_name = $_SESSION['first_name'];
    $middle_name = $_SESSION['middle_name'];
    $last_name = $_SESSION['last_name'];
    $address = $_SESSION['address'];
    $email = $_SESSION['email'];
    $profile_photo = $_SESSION['profile_photo']; 

    // ? Fetch all products listed by the seller, excluding 'unlisted' products
    $products_query = "
        SELECT p.*, c.name AS category_name 
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.seller_id = ? AND p.status != 'unlisted'
        ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($products_query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $products_result = $stmt->get_result();

    // ? Fetch all orders related to the seller, including buyer's name
    $orders_query = "
        SELECT DISTINCT o.order_id, o.buyer_id, o.total_price, o.order_date, o.status, u.first_name, u.last_name, u.contact_number
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

    // ? Calculate total pending products for the seller
    $total_pending_products_query = "
        SELECT COUNT(*) AS total_pending
        FROM products
        WHERE seller_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($total_pending_products_query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $total_pending_result = $stmt->get_result();
    $total_pending_row = $total_pending_result->fetch_assoc();
    $total_pending = $total_pending_row['total_pending'] ?? 0; // Default to 0 if no pending products

    // ? Calculate total approved products for the seller
    $total_approved_products_query = "
        SELECT COUNT(*) AS total_approved
        FROM products
        WHERE seller_id = ? AND status = 'approved'";
    $stmt = $conn->prepare($total_approved_products_query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $total_approved_result = $stmt->get_result();
    $total_approved_row = $total_approved_result->fetch_assoc();
    $total_approved = $total_approved_row['total_approved'] ?? 0; // Default to 0 if no approved products

    // ? Update User's Info
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
        $updated_first_name = htmlspecialchars(trim($_POST['first_name']));
        $updated_middle_name = htmlspecialchars(trim($_POST['middle_name']));
        $updated_last_name = htmlspecialchars(trim($_POST['last_name']));
        $updated_email = htmlspecialchars(trim($_POST['email']));
        $updated_address = htmlspecialchars(trim($_POST['address']));

        // ? Update the user's information in the database
        $update_user_query = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, email = ?, address = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_user_query);
        $stmt->bind_param("sssssi", $updated_first_name, $updated_middle_name, $updated_last_name, $updated_email, $updated_address, $buyer_id);

        if ($stmt->execute()) {
            // ? Update session into new variables
            $_SESSION['first_name'] = $updated_first_name;
            $_SESSION['middle_name'] = $updated_middle_name;
            $_SESSION['last_name'] = $updated_last_name;
            $_SESSION['email'] = $updated_email;
            $_SESSION['address'] = $updated_address;

            // Redirect to refresh the page
            header("Location: seller.php#edit-profile");
            echo "<script> User updated success</script>";
            exit();
        } else {
            echo "<script> Update Failed</script>";
            echo "Error updating user information: " . $stmt->error;
        }
    }

    // ? Update user photo
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_photo'])) {
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['profile_photo']['tmp_name'];
            $file_name = $_FILES['profile_photo']['name'];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            // Generate a unique file name
            $unique_file_name = uniqid('profile_', true) . '.' . $file_ext;

            // Define the upload directory
            $upload_dir = '../uploads/';
            $upload_path = $upload_dir . $unique_file_name;

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Update the profile_photo field in the database
                $update_photo_query = "UPDATE users SET profile_photo = ? WHERE user_id = ?";
                $stmt = $conn->prepare($update_photo_query);
                $stmt->bind_param("si", $unique_file_name, $buyer_id);

                if ($stmt->execute()) {
                    // Update the session variable
                    $_SESSION['profile_photo'] = $unique_file_name;

                    // Redirect to refresh the page
                    header("Location: seller.php#edit-profile");
                    exit();
                } else {
                    echo "<script>alert('Failed to update profile photo in the database.');</script>";
                }
            } else {
                echo "<script>alert('Failed to upload the file.');</script>";
            }
        } else {
            echo "<script>alert('No file uploaded or an error occurred.');</script>";
        }
    }
?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php if ($view_mode == 'light'): ?>
            <link rel="stylesheet" href="/phpets/assets/css/seller-light.css" />
            <link rel="stylesheet" href="/phpets/assets/css/index-light.css" />
        <?php else: ?>
            <link rel="stylesheet" href="/phpets/assets/css/seller.css" />
            <link rel="stylesheet" href="/phpets/assets/css/index.css" />
        <?php endif ?>
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
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/add.svg" alt="">
                        <?php else: ?>
                            <img src="/phpets/assets/images/add-dark.svg" alt="">
                        <?php endif ?>
                        <span>Add Product</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#cart-details">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/cart-bag.svg" alt="">
                        <?php else: ?>
                            <img src="/phpets/assets/images/cart-dark.svg" alt="">
                        <?php endif ?>
                        <span>My Listings</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#purchased-details">
                        <?php if ($view_mode == 'dark'): ?>
                           <img src="/phpets/assets/images/purchase.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/orders-dark.svg" alt="">
                        <?php endif ?>   
                        <span>Orders</span>
                    </a>
                </div>

                <div class="animate-fadein-left">
                    <a class="link-navs" href="#edit-profile">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/edit-profile.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/edit-profile-dark.svg">
                        <?php endif ?>
                        <span>Edit Profile</span>
                    </a>
                </div>

                
            </div>
            <div class="right">
                <div class="dashboard">
                    <div id="total-sales" class="data-card">
                        <div class="heading-2">
                            <?php if ($view_mode == 'dark'): ?>
                                <img src="/phpets/assets/images/earning.svg" alt="">
                            <?php else: ?>
                                <img src="/phpets/assets/images/earning-dark.svg" alt="">
                            <?php endif ?>
                            <h2>Total Sales</h2>
                        </div>
                        <p class="strong">₱<?php echo number_format($total_earnings, 2); ?></p>
                    </div>
                    <div id="total-orders" class="data-card">
                        <div class="heading-2">
                            <?php if ($view_mode == 'dark'): ?>
                                <img src="/phpets/assets/images/transaction.svg" alt="">
                            <?php else: ?>
                                <img src="/phpets/assets/images/transaction-dark.svg" alt="">
                            <?php endif ?>
                            <h2>Total Orders</h2>
                        </div>
                        <p class="strong"><?php echo $total_orders; ?></p>
                    </div>
                    <div id="total-products" class="data-card">
                        <div class="heading-2">
                            <?php if ($view_mode == 'dark'): ?>
                                <img src="/phpets/assets/images/cart-bar.svg" alt="">
                            <?php else: ?>
                                <img src="/phpets/assets/images/cart-dark.svg" alt="">
                            <?php endif ?>
                            <h2>Total Products</h2>
                        </div>
                        <div class="wrapper">
                            <p class="strong"><?php echo $total_pending; ?> </p><span class="pending">Pending</span>
                            <p class="strong"><?php echo $total_approved; ?></p><span class="approved">Approved</span>
                        </div>
                        
                    </div>
                </div>
                
                <div id="cart-details">
                    <div class="heading" style="margin-top: 10px;">
                         <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/cart-bar.svg" alt="">
                        <?php else: ?>
                            <img src="/phpets/assets/images/cart-dark.svg" alt="">
                        <?php endif ?>
                        <h2>My Listings</h2>
                    </div>
                    <div class="list-table-content">
                        <?php if ($products_result->num_rows > 0): ?>
                            <?php while ($product = $products_result->fetch_assoc()): ?>
                                <div class="product-card" style="padding-bottom: 80px;">
                                    <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" style="margin-right: 10px;" alt="Product Image" width="100">
                                    <span class="category-tag"> <?php echo htmlspecialchars($product['category_name']); ?></span>
                                    <div class="product-card-detail">
                                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p>
                                            <?php 
                                                echo htmlspecialchars(mb_strlen($product['description']) > 50 
                                                    ? mb_substr($product['description'], 0, 50) . '...' 
                                                    : $product['description']); 
                                            ?>
                                        </p>
                                        <p>Stock: <?php echo $product['stock']; ?> pcs</p>
                                        <p>₱<?php echo number_format($product['price'], 2); ?></p>
                                        <p class="<?php echo ($product['status']); ?> status"> <?php echo ucfirst($product['status']); ?></p>
                                    </div>      
                                    <form method="POST" action="unlist_product.php" class="product-card-footer-seller">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <a href="/phpets/view_product.php?id=<?php echo $product['product_id']; ?>" class="view cool-btn">
                                            View
                                        </a>
                                        <button type="submit" class="delete cool-btn">Unlist</button>
                                    </form>                           
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No products listed yet. <a href="/phpets/seller/add_product.php" class="add-product-link">Add a Product</a></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div id="purchased-details">
                    <div class="heading">
                        <?php if ($view_mode == 'dark'): ?>
                           <img src="/phpets/assets/images/purchase.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/orders-dark.svg" alt="">
                        <?php endif ?>   
                        <h2>Orders</h2>
                    </div>
                    <?php if ($orders_result->num_rows > 0): ?>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <div class="order-box">
                            <div class="order-box-head">
                                <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
                                <p><strong>Buyer:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?> (<?php echo htmlspecialchars($order['contact_number']); ?>)</p>
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

                <div id="edit-profile" style="margin-top: 40px">
                    <div class="heading mb-20">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/edit-profile.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/edit-profile-dark.svg">
                        <?php endif ?>
                        <h2>Edit Profile</h2>
                    </div>
                    <form action="" method="POST">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" value="<?php echo htmlspecialchars($first_name); ?>" required />

                        <label for="middle_name">Middle Name:</label>
                        <input type="text" id="middle_name" name="middle_name" placeholder="Enter your middle name (optional)" value="<?php echo htmlspecialchars($middle_name); ?>" />

                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" value="<?php echo htmlspecialchars($last_name); ?>" required />

                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required />

                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" placeholder="Street, Barangay, Municipal, Province" value="<?php echo htmlspecialchars($address); ?>" required />
                        
                        <div class="save-btn-container">
                            <button type="submit" name="update_user" class="save-btn cool-btn">Save Changes</button>
                        </div>
                    </form>
                    <form method="POST" enctype="multipart/form-data">
                        <h2>Change Profile Photo</h2>
                        <input type="file" name="profile_photo" required>
                        <button type="submit" name="update_photo" class="save-btn cool-btn">Update Photo</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
    
</html>

<?php 
    include ('../includes/view-modal.php');
?>