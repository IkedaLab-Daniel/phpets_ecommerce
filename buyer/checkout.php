<?php
    include '../includes/header.php';
    session_start();

    $view_mode = isset($_COOKIE['view']) ? $_COOKIE['view'] : 'light';

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

    // ? Fetch user information from the session
    $user_info = [
        'first_name' => $_SESSION['first_name'],
        'middle_name' => $_SESSION['middle_name'] ?? '',
        'last_name' => $_SESSION['last_name'],
        'email' => $_SESSION['email'],
        'address' => $_SESSION['address'],
        'contact_number' => $_SESSION['contact_number'],
    ];

    // ? calculate total price
    $total_price = 0;
    foreach ($checkout_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    // > Back button - clear $_session data that no need
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_checkout'])) {
        unset($_SESSION['checkout_product']);
        unset($_SESSION['checkout_items']);
        header("Location: /phpets/buyer/buyer.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php if ($view_mode == 'dark'): ?>
            <link rel="stylesheet" href="/phpets/assets/css/checkout.css">
        <?php else: ?>
            <link rel="stylesheet" href="/phpets/assets/css/checkout-light.css">
            <link rel="stylesheet" href="/phpets/assets/css/index-light.css">
        <?php endif ?>
        <title>Checkout</title>
    </head>
    <body>
        <div id="checkout-page">
            <div class="checkout-container">
                <div class="heading">
                    <?php if ($view_mode == 'dark'): ?>
                        <img src="/phpets/assets/images/credit-card.svg" >
                    <?php else: ?>
                        <img src="/phpets/assets/images/credit-card-dark.svg" >
                    <?php endif ?>
                    <h2>Checkout</h2>
                </div>
                
                <div class="product-table-head">
                    <span>Item</span>
                    <span>Quantity</span>
                    <span>Price</span>
                    <span>Subtotal</span>
                </div>
                <div class="product-list">
                    <?php foreach ($checkout_items as $item): ?>
                        <div class="product-item">
                            <div class="img-name" style="display: flex; align-items: center; gap: 10px;">
                                <img src="/phpets/uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Product Image">
                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                            </div>
                            <p><?php echo $item['quantity']; ?></p>
                            <p>₱<?php echo number_format($item['price'], 2); ?></p>
                            <p>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                        </div>
                    <?php endforeach; ?>
                    <div class="total-container">
                        <span>Total:</span>
                        <span class="total">₱<?php echo number_format($total_price, 2) ?></span>
                    </div>
                </div>

                <div class="user-info">
                    <div class="user-wrapper">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/user.svg" alt="">
                        <?php else: ?>
                            <img src="/phpets/assets/images/user-dark.svg" alt="">
                        <?php endif ?>
                        <h3>Shipping Information</h3>
                    </div>
                    
                    <p><strong>Buyer:</strong> <?php echo htmlspecialchars($user_info['first_name'] . ' ' . $user_info['middle_name'] . ' ' . $user_info['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($user_info['address']); ?></p>
                    <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($user_info['contact_number']); ?></p>
                    <p class="cod">Cash on Delivery</p>
                    <a href="/phpets/buyer/buyer.php#edit-profile" class="edit-info">Edit Info</a>
                </div>
                
                <div class="btn-container">
                    <form method="POST" style="display:inline;">
                        <button type="submit" name="clear_checkout" class="edit-btn cool-btn">Back</button>
                    </form>
                    <form method="POST" action="process_checkout.php">
                        <button class="confirm-btn cool-btn" type="submit" name="confirm_checkout" class="checkout-btn">Confirm Checkout</button>
                    </form>
                </div>
        </div>
        
            
        </div>
    </body>
</html>

<?php 
    include ('../includes/view-modal.php');
?>