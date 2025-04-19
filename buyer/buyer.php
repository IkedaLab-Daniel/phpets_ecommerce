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
                <h1>Left</h1>
            </div>
            <div class="right">
                <h1>Right</h1>
            </div>
        </div>
        
        <img src="../uploads/<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Picture" width="100">

        <p><strong>Full Name:</strong> <?php echo $first_name . ' ' . $middle_name . ' ' . $last_name; ?></p>
        <p><strong>Address:</strong> <?php echo $address; ?></p>
        <p><strong>Role:</strong> <?php echo ucfirst($_SESSION['role']); ?></p>

        <h2>🛒 Cart Items</h2>
        <ul>
        <?php while ($item = mysqli_fetch_assoc($cart_result)): ?>
            <li>
                <?php echo $item['name']; ?> —
                Qty: <?php echo $item['quantity']; ?> —
                ₱<?php echo $item['price']; ?> each
            </li>
        <?php endwhile; ?>
        </ul>

        <hr>

        <h2>✅ Purchased Orders (Delivered)</h2>
        <ul>
        <?php while ($order = mysqli_fetch_assoc($purchased_result)): ?>
            <li>
                Order #<?php echo $order['order_id']; ?> —
                ₱<?php echo $order['total_price']; ?> —
                Status: <?php echo $order['status']; ?>
            </li>
        <?php endwhile; ?>
        </ul>

        <hr>

        <h2>📦 All Transactions</h2>
        <ul>
        <?php while ($order = mysqli_fetch_assoc($all_orders_result)): ?>
            <li>
                Order #<?php echo $order['order_id']; ?> —
                ₱<?php echo $order['total_price']; ?> —
                Status: <?php echo $order['status']; ?>
            </li>
        <?php endwhile; ?>
        </ul>
    </body>
    
</html>

<?php 
    echo '<pre>';
    print_r($_SESSION);
    echo '</pre>';    
?>