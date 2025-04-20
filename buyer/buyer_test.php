<?php
    session_start();
    include '../includes/db_connect.php';
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

    if (mysqli_num_rows($all_orders_result) === 0) {
        echo "<p>No orders found for this buyer.</p>";
    }

    echo "<p>Session User ID: $buyer_id</p>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<h2>All Orders</h2>
        <?php while($order = mysqli_fetch_assoc($all_orders_result)): ?>
            <?php echo "<script>console.log('Works here')</script>"; ?>
            <div class="order-box">
                <p><strong>Order ID:</strong> <?= $order['order_id'] ?></p>
                <p><strong>Status:</strong> <?= $order['status'] ?></p>
                <p><strong>Date:</strong> <?= $order['order_date'] ?></p>

                <ul>
                <?php
                    $order_id = $order['order_id'];
                    $item_sql = "SELECT oi.*, p.name, p.image, p.price 
                                FROM order_items oi
                                JOIN products p ON oi.product_id = p.product_id
                                WHERE oi.order_id = $order_id";
                    $item_result = mysqli_query($conn, $item_sql);

                    while($item = mysqli_fetch_assoc($item_result)):
                ?>
                    <li>
                        <img src="../assets/images/<?= $item['image'] ?>" alt="" width="50">
                        <?= $item['name'] ?> - <?= $item['quantity'] ?> pcs - ₱<?= number_format($item['price'], 2) ?>
                    </li>
                <?php endwhile; ?>
                </ul>

                <hr>
            </div>
        <?php endwhile; ?>
</body>
</html>