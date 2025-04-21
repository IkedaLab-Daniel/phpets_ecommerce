<?php
    session_start();
    include '../includes/db_connect.php';
    include '../includes/error_catch.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
        header("Location: ../login.php");
        exit();
    }
    
    $buyer_id = $_SESSION['user_id'];
    
    // Step 1: Get all cart items for this user
    $cart_query = "SELECT c.*, p.price FROM cart c 
                   JOIN products p ON c.product_id = p.product_id 
                   WHERE c.buyer_id = $buyer_id";
    $cart_result = mysqli_query($conn, $cart_query);
    
    // Step 2: Calculate total price
    $total_price = 0;
    $cart_items = [];
    
    while ($row = mysqli_fetch_assoc($cart_result)) {
        $cart_items[] = $row; // store temporarily
        $total_price += $row['price'] * $row['quantity'];
    }
    
    // Step 3: Create order
    $order_query = "INSERT INTO orders (buyer_id, total_price, status) 
                    VALUES ($buyer_id, $total_price, 'pending')";
    mysqli_query($conn, $order_query);
    $order_id = mysqli_insert_id($conn); // get the inserted order ID
    
    // Step 4: Insert order items
    foreach ($cart_items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price']; // now included
    
        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price)
                       VALUES ($order_id, $product_id, $quantity, $price)";
        mysqli_query($conn, $item_query);
    }
    
    // Step 5: Clear cart
    $clear_cart_query = "DELETE FROM cart WHERE buyer_id = $buyer_id";
    mysqli_query($conn, $clear_cart_query);
    
    // Redirect to result page
    header("Location: checkout_result.php?status=success");
    exit();
    ?>
    
?>
