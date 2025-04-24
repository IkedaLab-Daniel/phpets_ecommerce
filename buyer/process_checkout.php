<?php
    session_start();
    include '../includes/db_connect.php'; // Include database connection

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkout'])) {
        if (!isset($_SESSION['checkout_product'])) {
            header("Location: /phpets/index.php");
            exit();
        }

        $checkout_product = $_SESSION['checkout_product'];
        $buyer_id = $_SESSION['user_id'];
        $product_id = $checkout_product['product_id'];
        $quantity = $checkout_product['quantity'];
        $total_price = $checkout_product['price'] * $quantity;

        // Step 1: Check if the product has enough stock
        $stock_query = "SELECT stock FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($stock_query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stock_result = $stmt->get_result();
        $product = $stock_result->fetch_assoc();

        if ($product['stock'] < $quantity) {
            $message = "Not enough stock available.";
            $status = "failed";
        } else {
            // Step 2: Deduct the stock
            $update_stock_query = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
            $stmt = $conn->prepare($update_stock_query);
            $stmt->bind_param("ii", $quantity, $product_id);
            $stmt->execute();

            // Step 3: Create the order
            $create_order_query = "INSERT INTO orders (buyer_id, total_price, status, order_date) VALUES (?, ?, 'pending', NOW())";
            $stmt = $conn->prepare($create_order_query);
            $stmt->bind_param("id", $buyer_id, $total_price);
            $stmt->execute();
            $order_id = $stmt->insert_id; // Get the newly created order ID

            // Step 4: Add the product to the order_items table
            $add_order_item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($add_order_item_query);
            $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $checkout_product['price']);
            $stmt->execute();

            // Step 5: Clear the session for the checkout product
            unset($_SESSION['checkout_product']);

            $message = "Checkout successful!";
            $status = "success";
        }
    } else {
        header("Location: /phpets/index.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/phpets/assets/css/checkout.css">
        <title>Checkout Status</title>
    </head>
    <body>
        <div class="checkout-status <?php echo $status; ?>">
            <div class="message-box">
                <h2><?php echo $message; ?></h2>
                <?php if ($status === "success"): ?>
                    <a href="/phpets/buyer/buyer.php#all-transactions" class="btn">View Transactions</a>
                <?php else: ?>
                    <a href="/phpets/buyer/checkout.php" class="btn">Go Back</a>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>