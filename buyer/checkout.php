<?php
session_start();

    if (!isset($_SESSION['checkout_product'])) {
        header("Location: /phpets/index.php");
        exit();
    }

    $checkout_product = $_SESSION['checkout_product'];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/phpets/assets/css/checkout.css">
        <title>Checkout</title>
    </head>
    <body>
        <div class="checkout-container">
            <h2>Checkout</h2>
            <div class="product-details">
                <img src="/phpets/uploads/<?php echo htmlspecialchars($checkout_product['image']); ?>" alt="Product Image">
                <p><strong>Product:</strong> <?php echo htmlspecialchars($checkout_product['name']); ?></p>
                <p><strong>Quantity:</strong> <?php echo $checkout_product['quantity']; ?></p>
                <p><strong>Total Price:</strong> ₱<?php echo number_format($checkout_product['price'] * $checkout_product['quantity'], 2); ?></p>
            </div>
            <form method="POST" action="process_checkout.php">
                <button type="submit" name="confirm_checkout" class="checkout-btn">Confirm Checkout</button>
            </form>
        </div>
    </body>
</html>