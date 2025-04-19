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
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/phpets/assets/css/index.css" />
        <link rel="stylesheet" href="/phpets/assets/css/buyer.css" />
        <link rel="icon" type="image/svg" href="/phpets/assets/images/paw.svg" />
        <title>PHPetsss </title>
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
                    <a class="link-navs" href="#card-details">
                        <img src="/phpets/assets/images/cart-bag.svg" >
                        <span>My Cart</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#card-details">
                        <img src="/phpets/assets/images/purchase.svg" >
                        <span>Purchased</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#card-details">
                        <img src="/phpets/assets/images/transaction.svg" >
                        <span>Transactions</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#card-details">
                        <img src="/phpets/assets/images/edit-profile.svg" >
                        <span>Edit Profile</span>
                    </a>
                </div>
                
            </div>
            <div class="right">
                <div id="cart-details">
                    <div class="heading">
                        <img src="/phpets/assets/images/cart-bag.svg" alt="">
                        <h2>My Cart</h2>
                    </div>
                    
                    <div class="cart-table-head">
                        <span style="width: 140px;">Name</span>
                        <span>Quantity</span>
                        <span>Price</span>
                        <span>Order</span>
                    </div>
                    <div class="cart-table-row">
                        <?php while ($item = mysqli_fetch_assoc($cart_result)): ?>
                            <li>
                                <span><?php echo $item['name']; ?></span>
                                <span><?php echo $item['quantity']; ?></span>
                                <span>₱<?php echo $item['price']; ?></span>
                                <input type="checkbox" name="" class="checkbox">
                            </li>
                        <?php endwhile; ?>
                    </div>
                    <div class="checkout-btn-container">
                        <span>Total: <b>₱0.00</b></span>
                        <button class="clear-btn cool-btn">Clear All</button>
                        <button class="checkout-btn cool-btn">Check Out</button>
                    </div>
                </div>

                <div id="purchased-details" style="margin-top: 20px;">
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

                <div id="transactions-detail" style="margin-top: 40px;">
                    <div class="heading">
                        <img src="/phpets/assets/images/transaction.svg" alt="">
                        <h2>Transactions</h2>
                    </div>
                    <div class="purchased-table-head">
                        <span>Order</span>
                        <span>Total</span>
                        <span>Status</span>
                    </div>
                    <div class="transactions-table-row">
                        <?php while ($order = mysqli_fetch_assoc($all_orders_result)): ?>
                            <li>
                                <span><?php echo $order['order_id']; ?></span>
                                <span>₱ <?php echo $order['total_price']; ?></span>
                                <div class="order-status-wrapper">
                                    <p class="<?php echo $order['status']; ?>"><?php echo $order['status']; ?></p>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div id="edit-profile" style="margin-top: 40px">
                    <div class="heading mb-20">
                        <img src="/phpets/assets/images/transaction.svg" alt="">
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
                </div>
            </div>
        </div>
    </body>
    
</html>
