<?php
    session_start();
    include '../includes/db_connect.php';
    include '../includes/header.php';

    $view_mode = isset($_COOKIE['view']) ? $_COOKIE['view'] : 'light';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
        header("Location: ../login.php");
        exit();
    }

    if (isset($_SESSION['view'])){
        $_SESSION['view'] == 'light';
    }

    $buyer_id = $_SESSION['user_id'];
    $first_name = $_SESSION['first_name'];
    $middle_name = $_SESSION['middle_name'];
    $last_name = $_SESSION['last_name'];
    $address = $_SESSION['address'];
    $email = $_SESSION['email'];
    $profile_photo = $_SESSION['profile_photo']; 
    $contact_number = $_SESSION['contact_number'];


    // Fetch Cart Items
    $cart_sql = "SELECT c.*, p.name, p.price, p.image 
                FROM cart c 
                JOIN products p ON c.product_id = p.product_id 
                WHERE c.buyer_id = $buyer_id";
    $cart_result = mysqli_query($conn, $cart_sql);

    // Fetch Purchased Orders
    $purchased_sql = "SELECT * FROM orders WHERE buyer_id = $buyer_id AND status = 'delivered'";
    $purchased_result = mysqli_query($conn, $purchased_sql);

    // Fetch All Orders
    $all_orders_sql = "SELECT * FROM orders WHERE buyer_id = $buyer_id";
    $all_orders_result = mysqli_query($conn, $all_orders_sql);

    $all_orders = [];
    while ($row = mysqli_fetch_assoc($all_orders_result)) {
        $all_orders[] = $row;
    }

    // ? Buy again
    if (isset($_POST['buy_again'])) {
        $order_id = intval($_POST['order_id']); // Get the order ID from the form

        // Fetch all items in the order
        $order_items_query = "SELECT oi.product_id, oi.quantity, p.price, p.name, p.image 
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.product_id
                            WHERE oi.order_id = ?";
        $stmt = $conn->prepare($order_items_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order_items_result = $stmt->get_result();

        // Store the order items in the session for checkout
        $_SESSION['checkout_items'] = [];
        while ($item = $order_items_result->fetch_assoc()) {
            $_SESSION['checkout_items'][] = $item;
        }

        // Redirect to the checkout page
        header("Location: /phpets/buyer/checkout.php");
        exit();
    }

    // ? Clear all items in the cart
    if (isset($_POST['clear_cart'])) {
        $clear_cart_query = "DELETE FROM cart WHERE buyer_id = ?";
        $stmt = $conn->prepare($clear_cart_query);
        $stmt->bind_param("i", $buyer_id);
        $stmt->execute();

        // ? Refresh the page
        header("Location: buyer.php");
        exit();
    }

    // ? Get total price of all items in cart
    $total_price = 0;
    if (mysqli_num_rows($cart_result) > 0) {
        while ($item = mysqli_fetch_assoc($cart_result)) {
            $total_price += $item['price'] * $item['quantity'];
        }
        // ? Reset the cart result pointer for rendering items
        mysqli_data_seek($cart_result, 0);
    }

    // ? Remove an item on cart
    if (isset($_POST['remove_item'])) {
        $cart_id = intval($_POST['cart_id']); // ? Get the cart ID from the form
        $remove_query = "DELETE FROM cart WHERE cart_id = ?";
        $stmt = $conn->prepare($remove_query);
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        // * Refresh
        header("Location: buyer.php");
        exit();
    }

    // ? Checkout (redirect to separate file)
    if (isset($_POST['checkout_now'])) {
        // Fetch all items in the cart
        $cart_items_query = "SELECT c.product_id, c.quantity, p.price, p.name, p.image 
                            FROM cart c
                            JOIN products p ON c.product_id = p.product_id
                            WHERE c.buyer_id = ?";
        $stmt = $conn->prepare($cart_items_query);
        $stmt->bind_param("i", $buyer_id);
        $stmt->execute();
        $cart_items_result = $stmt->get_result();

        // Store all cart items in the session for checkout
        $_SESSION['checkout_items'] = [];
        while ($item = $cart_items_result->fetch_assoc()) {
            $_SESSION['checkout_items'][] = $item;
        }

        // Redirect to the checkout page
        header("Location: /phpets/buyer/checkout.php");
        exit();
    }

    // ? CANCEL an order
    if (isset($_POST['cancel_order'])) {
        $order_id = intval($_POST['order_id']); // Get the order ID from the form

        // ? Fetch all items in the order to update inventory
        $order_items_query = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($order_items_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order_items_result = $stmt->get_result();

        // * Update inventory for each product in the order
        while ($item = $order_items_result->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            $update_stock_query = "UPDATE products SET stock = stock + ? WHERE product_id = ?";
            $update_stmt = $conn->prepare($update_stock_query);
            $update_stmt->bind_param("ii", $quantity, $product_id);
            $update_stmt->execute();
        }

        // * Update the order status to "cancelled"
        $update_order_status_query = "UPDATE orders SET status = 'cancelled' WHERE order_id = ?";
        $update_stmt = $conn->prepare($update_order_status_query);
        $update_stmt->bind_param("i", $order_id);
        $update_stmt->execute();

        // * Refresh the page
        header("Location: buyer.php#all-transactions");
        exit();
    }

    // ? DELETE ORDER FINAL BOSS: Remove Order
    if (isset($_POST['remove_cancelled_order'])) {
        $order_id = intval($_POST['order_id']); // Get the order ID from the form
    
        // Delete the order items
        $delete_order_items_query = "DELETE FROM order_items WHERE order_id = ?";
        $delete_stmt = $conn->prepare($delete_order_items_query);
        $delete_stmt->bind_param("i", $order_id);
        $delete_stmt->execute();
    
        // Delete the order
        $delete_order_query = "DELETE FROM orders WHERE order_id = ?";
        $delete_stmt = $conn->prepare($delete_order_query);
        $delete_stmt->bind_param("i", $order_id);
        $delete_stmt->execute();

        // ! Render a toast success, but order item stills render below, means need reflesh
        // echo "<div class = 'order-cancelled'>
        //             <img class = 'check' src='/phpets/assets/images/green-check.svg' width = '30'>
        //             <p>Order Cancelled</p>
        //             <img class = 'cat' src='/phpets/assets/images/happy-cat.gif' width = '50'>
        //     </div>";
    
        //  ? Refresh the page
        header("Location: buyer.php#all-transactions");
        exit();
    }

    // ? Update User's Info
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
        $updated_first_name = htmlspecialchars(trim($_POST['first_name']));
        $updated_middle_name = htmlspecialchars(trim($_POST['middle_name']));
        $updated_last_name = htmlspecialchars(trim($_POST['last_name']));
        $updated_email = htmlspecialchars(trim($_POST['email']));
        $updated_address = htmlspecialchars(trim($_POST['address']));
        $updated_contact_number = htmlspecialchars(trim($_POST['contact_number'])); // Correctly handle contact number
    
        // Update the user's information in the database
        $update_user_query = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, email = ?, address = ?, contact_number = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_user_query);
        $stmt->bind_param("ssssssi", $updated_first_name, $updated_middle_name, $updated_last_name, $updated_email, $updated_address, $updated_contact_number, $buyer_id);
    
        if ($stmt->execute()) {
            // Update session variables
            $_SESSION['first_name'] = $updated_first_name;
            $_SESSION['middle_name'] = $updated_middle_name;
            $_SESSION['last_name'] = $updated_last_name;
            $_SESSION['email'] = $updated_email;
            $_SESSION['address'] = $updated_address;
            $_SESSION['contact_number'] = $updated_contact_number;
    
            // Redirect to refresh the page
            header("Location: buyer.php#edit-profile");
            echo "<script> User updated successfully</script>";
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
                    header("Location: buyer.php#edit-profile");
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
            <link rel="stylesheet" href="/phpets/assets/css/buyer-light.css" />
            <link rel="stylesheet" href="/phpets/assets/css/index-light.css" />
        <?php else: ?>
            <link rel="stylesheet" href="/phpets/assets/css/buyer.css" />
            <link rel="stylesheet" href="/phpets/assets/css/index.css" />
        <?php endif ?>
        <link rel="icon" type="image/svg" href="/phpets/assets/images/paw.svg" />
        <title><?php echo $first_name . ' ' . $last_name; ?> </title>
    </head>

    <body>
        <div id="buyer">
            <div class="left">
                <div class="user-details">
                    <img src="../uploads/<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Picture" width="100">
                    <span class="fullname"> <?php echo $first_name . ' ' . $middle_name . ' ' . $last_name; ?></span>
                    <span class="role"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <span class="address"><?php echo $address; ?> - <?php echo $contact_number; ?></span>
                </div>
                
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#cart-details">
                        <img src="/phpets/assets/images/cart-bag.svg">
                        <span>My Cart</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#purchased-details">
                        <img src="/phpets/assets/images/purchase.svg">
                        <span>Purchased</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#all-transactions">
                        <img src="/phpets/assets/images/transaction.svg">
                        <span>My Orders</span>
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
                <div id="cart-details">
                    <div class="heading" style="margin-top: 10px;">
                        <img src="/phpets/assets/images/cart-bag.svg" alt="">
                        <h2>My Cart</h2>
                    </div>
                    
                    <div class="cart-table-head">
                        <span>Name</span>
                        <span>Quantity</span>
                        <span>Price</span>
                        <span>Action</span>
                    </div>
                    <div class="cart-table-row">
                        <?php if (mysqli_num_rows($cart_result) > 0): ?>
                            <?php while ($item = mysqli_fetch_assoc($cart_result)): ?>
                                <li>
                                    <span style="justify-content: left; margin-left: 20px;"><?php echo $item['name']; ?></span>
                                    <span><?php echo $item['quantity']; ?></span>
                                    <span>₱<?php echo number_format($item['price'], 2); ?></span>
                                    <form action="" method="POST">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>"> <!-- Pass cart_id -->
                                        <button type="submit" name="remove_item" class="remove cool-btn">Remove</button>
                                    </form>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="empty-cart-message">Your cart is empty <a class="shop-now cool-btn" href="/phpets/index.php">Shop Now</a></p>
                        <?php endif; ?>
                    </div>
                    <form method="POST" class="checkout-btn-container">
                        <?php if (mysqli_num_rows($cart_result) > 0): ?>
                            <span>Total: <b>₱<?php echo number_format($total_price, 2); ?></b></span>
                            <button class="clear-btn cool-btn" type="submit" name="clear_cart">
                                <img src="/phpets/assets/images/clear.svg" alt="">
                                <p>Clear All</p>  
                            </button>
                            <button class="checkout-btn cool-btn" type="submit" name="checkout_now">
                                <img src="/phpets/assets/images/credit-card.svg">
                                <p>Check Out</p>
                            </button>
                        <?php endif; ?>
                    </form>
                </div>

                <div id="purchased-details">
                    <div class="heading">
                        <img src="/phpets/assets/images/purchase.svg" alt="">
                        <h2>Purchased</h2>
                    </div>
                    <?php while ($order = mysqli_fetch_assoc($purchased_result)): ?>
                        <div class="order-box">
                            <div class="order-box-head">
                                <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
                                <p><strong>Total:</strong> ₱<?php echo number_format($order['total_price'], 2); ?></p>
                                <p class="<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></p>
                            </div>

                            <!-- Render items for this order -->
                            <div class="order-box-products">
                                <?php
                                    $order_id = $order['order_id'];
                                    $item_sql = "SELECT oi.*, p.name, p.image, p.price, p.product_id 
                                                FROM order_items oi
                                                JOIN products p ON oi.product_id = p.product_id
                                                WHERE oi.order_id = $order_id";
                                    $item_result = mysqli_query($conn, $item_sql);

                                    while ($item = mysqli_fetch_assoc($item_result)):
                                ?>
                                    <div class="order-item">
                                        <div class="brix">
                                            <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" width="50">
                                            <span><?php echo htmlspecialchars($item['name']); ?></span>
                                        </div>
                                        <span><?php echo $item['quantity']; ?> pcs</span>
                                        <span>₱ <?php echo number_format($item['price'], 2); ?>/pcs</span>
                                        <a href="/phpets/view_product.php?id=<?php echo $item['product_id']; ?>#add-edit-review" class="rate-btn cool-btn">Rate</a>
                                    </div>
                                <?php endwhile; ?>
                            </div>

                            <div class="order-box-foot">
                                <div class="foot-left">
                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                                </div>
                                <div class="foot-right">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <button class="order-again cool-btn" type="submit" name="buy_again">Buy Again</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div id="all-transactions">
                    <div class="heading">
                        <img src="/phpets/assets/images/transaction.svg" alt="">
                        <h2>My Orders</h2>
                    </div>
                    <?php foreach ($all_orders as $order): ?>
                        <div class="order-box">
                            <div class="order-box-head">
                                <p><strong>Order ID:</strong> <?= $order['order_id'] ?></p>
                                <p></p>
                                <p class="<?php echo $order['status']; ?>"><?php echo $order['status']; ?></p>                            </div>

                            <div class="order-box-products">
                                <?php
                                    $order_id = $order['order_id'];
                                    $item_sql = "SELECT oi.*, p.name, p.image, p.price 
                                                FROM order_items oi
                                                JOIN products p ON oi.product_id = p.product_id
                                                WHERE oi.order_id = $order_id";
                                    $item_result = mysqli_query($conn, $item_sql);

                                    while($item = mysqli_fetch_assoc($item_result)):
                                ?>
                                    <div class="order-item">
                                        <img src="../uploads/<?= $item['image'] ?>" width="50">
                                        <span><?= $item['name'] ?></span>
                                        <span><?= $item['quantity'] ?>pcs</span>
                                        <span>₱ <?= number_format($item['price'], 2) ?>/pcs</span>
                                        
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="order-box-foot">
                                <div class="foot-left">
                                    <!-- <p><strong>Date:</strong> <?= $order['order_date'] ?></p> -->
                                    <p><strong>Total Price:</strong> ₱<?= number_format($order['total_price'], 2) ?></p>
                                </div>

                                <div class="foot-right">
                                    <?php if ($order['status'] == 'pending'): ?>
                                        <!-- Cancel Button -->
                                        <form method="POST">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <button class="cancel cool-btn" type="submit" name="cancel_order">Cancel</button>
                                        </form>
                                    <?php elseif ($order['status'] == 'cancelled'): ?>
                                        <!-- Remove Button for Cancelled Orders -->
                                        <form method="POST">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <button class="remove cool-btn" type="submit" name="remove_cancelled_order">Remove</button>
                                        </form>
                                    <?php else: ?>
                                        <div><span class="disabled">Cancel</span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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

                        <label for="contact_number">Contact Number:</label>
                        <input type="text" id="contact_number" name="contact_number" placeholder="Enter your contact number" value="<?php echo htmlspecialchars($contact_number); ?>" required />

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