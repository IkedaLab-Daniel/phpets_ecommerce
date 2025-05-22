<?php
    session_start();
    include '../includes/db_connect.php';
    include '../includes/error_catch.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkout'])) {
        if (!isset($_SESSION['checkout_grouped'])) {
            header("Location: /phpets/buyer/buyer.php");
            exit();
        }

        $buyer_id = $_SESSION['user_id'];
        $seller_id = intval($_POST['seller_id']);
        $checkout_items = $_SESSION['checkout_grouped'][$seller_id];
        $total_price = 0;

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
                header("Location: /phpets/buyer/checkout_result.php?status=failed");
                exit();
            }

            $total_price += $item['price'] * $quantity;
        }

        // Step 2: Deduct stock
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
        $order_id = $stmt->insert_id;

        // Step 4: Add order items
        foreach ($checkout_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $add_order_item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($add_order_item_query);
            $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt->execute();
        }

        // Step 5: Remove checked out items from cart
        foreach ($checkout_items as $item) {
            $cart_id = $item['cart_id'];
            $remove_cart_item_query = "DELETE FROM cart WHERE cart_id = ?";
            $stmt = $conn->prepare($remove_cart_item_query);
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
        }

        // Step 6: Remove this seller's items from session
        unset($_SESSION['checkout_grouped'][$seller_id]);
        if (empty($_SESSION['checkout_grouped'])) {
            unset($_SESSION['checkout_grouped']);
        }

        header("Location: /phpets/buyer/checkout_result.php?status=success");
        exit();
    } else {
        header("Location: /phpets/buyer/buyer.php");
        exit();
    }
?>