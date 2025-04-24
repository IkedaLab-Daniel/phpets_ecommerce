<?php
    session_start();
    include '../includes/db_connect.php';
    include '../includes/header.php';
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
        header("Location: ../login.php");
        exit();
    }

    $buyer_id = $_SESSION['user_id'];
    $first_name = $_SESSION['first_name'];
    $middle_name = $_SESSION['middle_name'];
    $last_name = $_SESSION['last_name'];
    $address = $_SESSION['address'];
    $email = $_SESSION['email'];
    $profile_photo = $_SESSION['profile_photo']; 

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
        $quantity = intval($_POST['quantity']);
        $_SESSION['checkout_product_id'] = $product_id;
        $_SESSION['checkout_quantity'] = $quantity;
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
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/phpets/assets/css/index.css" />
        <link rel="stylesheet" href="/phpets/assets/css/buyer.css" />
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
                    <span class="address"><?php echo $address; ?></span>
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
                        <img src="/phpets/assets/images/edit-profile.svg">
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
                        <span style="width: 140px;">Name</span>
                        <span>Quantity</span>
                        <span>Price</span>
                        <span>Action</span>
                    </div>
                    <div class="cart-table-row">
                        <?php if (mysqli_num_rows($cart_result) > 0): ?>
                            <?php while ($item = mysqli_fetch_assoc($cart_result)): ?>
                                <li>
                                    <span><?php echo $item['name']; ?></span>
                                    <span><?php echo $item['quantity']; ?></span>
                                    <span>₱<?php echo number_format($item['price'], 2); ?></span>
                                    <form action="" method="POST">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>"> <!-- Pass cart_id -->
                                        <button type="submit" name="remove_item" class="remove cool-btn">Remove</button>
                                    </form>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="empty-cart-message">Your Cart is Empty <a class="shop-now cool-btn" href="../index.php">Shop Now</a></p>
                        <?php endif; ?>
                    </div>
                    <form method="POST" class="checkout-btn-container">
                        <?php if (mysqli_num_rows($cart_result) > 0): ?>
                            <span>Total: <b>₱<?php echo number_format($total_price, 2); ?></b></span>
                            <button class="clear-btn cool-btn">Clear All</button>
                            <button class="checkout-btn cool-btn" type="submit" name="checkout_now">Check Out</button>
                        <?php endif; ?>
                    </form>
                </div>

                <div id="purchased-details">
                    <div class="heading">
                        <img src="/phpets/assets/images/purchase.svg" alt="">
                        <h2>Purchased</h2>
                    </div>
                    <div class="cart-table-head">
                        <span>Order</span>
                        <span>Total</span>
                        <span>Status</span>
                        <span>Action</span>
                    </div>
                    <div class="purchased-table-row">
                        <?php while ($order = mysqli_fetch_assoc($purchased_result)): ?>
                            <li>
                                <span style="width: 80px;"><?php echo $order['order_id']; ?></span>
                                <span>₱ <?php echo $order['total_price']; ?></span>
                                <span><?php echo $order['status']; ?></span>
                                <button class="order-again ">Order Again</button>
                            </li>
                        <?php endwhile; ?>
                    </div>
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
                                        <span>₱ <?= number_format($item['price'], 2) ?></span>
                                        
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
                        <img src="/phpets/assets/images/edit-profile.svg" alt="">
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
                            <button type="submit" class="save-btn cool-btn">Save Changes</button>
                        </div>
                    </form>
                    <h2>Upload Image</h2>
                    <form action="../includes/upload.php" method="POST" enctype="multipart/form-data">
                        <input type="file" name="uploaded_file" required>
                        <div class="save-btn-container">
                            <button type="submit" name="upload" class="save-btn cool-btn">Upload</button>
                        </div>
                    </form>
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