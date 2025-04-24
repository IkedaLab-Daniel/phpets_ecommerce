<?php
    session_start();

    // ? Check if the session contains checkout data
    if (!isset($_SESSION['checkout_product']) && !isset($_SESSION['checkout_items'])) {
        header("Location: /phpets/index.php");
        exit();
    }

    // ? Determine if it's a single product checkout or a "Buy Again" checkout
    if (isset($_SESSION['checkout_product'])) {
        $checkout_items = [$_SESSION['checkout_product']]; // Wrap single product in an array for consistency
    } elseif (isset($_SESSION['checkout_items'])) {
        $checkout_items = $_SESSION['checkout_items']; // Multiple products from "Buy Again"
}
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
            <div class="product-list">
                <?php foreach ($checkout_items as $item): ?>
                    <div class="product-item">
                        <img src="/phpets/uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Product Image">
                        <p><strong>Product:</strong> <?php echo htmlspecialchars($item['name']); ?></p>
                        <p><strong>Quantity:</strong> <?php echo $item['quantity']; ?></p>
                        <p><strong>Price:</strong> ₱<?php echo number_format($item['price'], 2); ?></p>
                        <p><strong>Total:</strong> ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" action="process_checkout.php">
                <button type="submit" name="confirm_checkout" class="checkout-btn">Confirm Checkout</button>
            </form>
        </div>
    </body>
</html>