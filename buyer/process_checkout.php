<?php
    session_start();
    include '../includes/db_connect.php'; // Include database connection

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkout'])) {
        if (!isset($_SESSION['checkout_product']) && !isset($_SESSION['checkout_items'])) {
            header("Location: /phpets/index.php");
            exit();
        }

        $buyer_id = $_SESSION['user_id'];
        $total_price = 0;

        // Determine if it's a single product checkout or cart checkout
        if (isset($_SESSION['checkout_product'])) {
            $checkout_items = [$_SESSION['checkout_product']]; // Wrap single product in an array for consistency
        } elseif (isset($_SESSION['checkout_items'])) {
            $checkout_items = $_SESSION['checkout_items']; // Multiple products from the cart
        }

        // Step 1: Check stock for all items
        foreach ($checkout_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            $stock_query = "SELECT stock FROM products WHERE product_id = ?";
            $stmt = $conn->prepare($stock_query);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stock_result = $stmt->get_result();
            $product = $stock_result->fetch_assoc();

            if ($product['stock'] < $quantity) {
                $message = "Not enough stock available for product: " . htmlspecialchars($item['name']);
                $status = "failed";
                include 'checkout_status.php'; // Include the status page
                exit();
            }

            // Calculate total price
            $total_price += $item['price'] * $quantity;
        }

        // Step 2: Deduct stock for all items
        foreach ($checkout_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            $update_stock_query = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
            $stmt = $conn->prepare($update_stock_query);
            $stmt->bind_param("ii", $quantity, $product_id);
            $stmt->execute();
        }

        // Step 3: Create the order
        $create_order_query = "INSERT INTO orders (buyer_id, total_price, status, order_date) VALUES (?, ?, 'pending', NOW())";
        $stmt = $conn->prepare($create_order_query);
        $stmt->bind_param("id", $buyer_id, $total_price);
        $stmt->execute();
        $order_id = $stmt->insert_id; // Get the newly created order ID

        // Step 4: Add all items to the order_items table
        foreach ($checkout_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];

            $add_order_item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($add_order_item_query);
            $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt->execute();
        }

        // Step 5: Clear the cart if it's a cart checkout
        if (isset($_SESSION['checkout_items'])) {
            $clear_cart_query = "DELETE FROM cart WHERE buyer_id = ?";
            $stmt = $conn->prepare($clear_cart_query);
            $stmt->bind_param("i", $buyer_id);
            $stmt->execute();
            unset($_SESSION['checkout_items']);
        }

        // Step 6: Clear the session for the single product checkout
        if (isset($_SESSION['checkout_product'])) {
            unset($_SESSION['checkout_product']);
        }

        $message = "Checkout successful!";
        $status = "success";
        include 'checkout_status.php'; // Include the status page
        exit();
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