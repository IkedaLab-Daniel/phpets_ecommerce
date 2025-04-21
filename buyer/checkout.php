<?php
    session_start();
    include '../includes/db_connect.php';
    include '../includes/error_catch.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
        header("Location: ../login.php");
        exit();
    }
    
    $buyer_id = $_SESSION['user_id'];
    
    // * Step 1: Get all cart items for this user
    // TODO: If no item on Cart, return 
    // ! Causes empty "order" row if checkout wihout cart
    $cart_query = "SELECT c.*, p.price FROM cart c 
                   JOIN products p ON c.product_id = p.product_id 
                   WHERE c.buyer_id = $buyer_id";       
    $cart_result = mysqli_query($conn, $cart_query);   
    
    // * Step 2: Calculate total price
    $total_price = 0;   
    $cart_items = [];   // ? for result data object to array (line 23)
    
    while ($row = mysqli_fetch_assoc($cart_result)) {
        $cart_items[] = $row; // ? store temporarily
        $total_price += $row['price'] * $row['quantity'];
    }
    
    // * tep 3: Create order
    $order_query = "INSERT INTO orders (buyer_id, total_price, status) 
                    VALUES ($buyer_id, $total_price, 'pending')";
    mysqli_query($conn, $order_query);
    $order_id = mysqli_insert_id($conn); // ? get the inserted order ID
    
    // ! Step 4: Insert order items and update product stock (Now working)
    foreach ($cart_items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price']; 

        // Insert order item
        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price)  
                    VALUES ($order_id, $product_id, $quantity, $price)";
        mysqli_query($conn, $item_query);

        // ? Update product stock 
        $update_stock_query = "UPDATE products 
                            SET stock = stock - $quantity 
                            WHERE product_id = $product_id";
        mysqli_query($conn, $update_stock_query);
    }
    
    // * Step 5: Clear cart
    $clear_cart_query = "DELETE FROM cart WHERE buyer_id = $buyer_id";
    mysqli_query($conn, $clear_cart_query);
    
    // ! Redirect to result page
    header("Location: checkout_result.php?status=success"); // TODO: Confirm First before this
    exit();
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assets/css/checkout.css">  <!-- null -->
        <title>Checkout</title>
    </head>
    <body>
        <h2>This page will get all item in cart, make an order, put all item in cart as order_item in order, then delete cart items</h2>
        <h3>To do: Display cart first instead of auto checkout</h3>
    </body>
</html>


