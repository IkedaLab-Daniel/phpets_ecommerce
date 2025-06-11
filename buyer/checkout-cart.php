<?php
    include '../includes/header.php';
    include '../includes/db_connect.php';

    $view_mode = isset($_COOKIE['view']) ? $_COOKIE['view'] : 'light';

    // ? Check for grouped checkout
    if (isset($_SESSION['checkout_grouped'])) {
        $checkout_grouped = $_SESSION['checkout_grouped'];
    } else {
        header("Location: /phpets/buyer/buyer.php");
        exit();
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
                    <h2>Checkout</h2>
                </div>
                <?php foreach ($checkout_grouped as $seller_id => $items): ?>
                    <div class="seller-checkout-box">
                        <h3>
                            Seller: 
                            <?php
                                // Fetch seller name
                                $seller_query = "SELECT first_name, middle_name, last_name FROM users WHERE user_id = ?";                                           $stmt = $conn->prepare($seller_query);
                                $stmt->bind_param("i", $seller_id);
                                $stmt->execute();
                                $seller_result = $stmt->get_result();
                                $seller = $seller_result->fetch_assoc();
                                echo htmlspecialchars(trim($seller['first_name'] . ' ' . ($seller['middle_name'] ?? '') . ' ' . $seller['last_name']));                            ?>
                        </h3>
                        <div class="product-table-head">
                            <span>Item</span>
                            <span>Quantity</span>
                            <span>Price</span>
                            <span>Subtotal</span>
                        </div>
                        <div class="product-list">
                            <?php $total_price = 0; ?>
                            <?php foreach ($items as $item): ?>
                            <?php $total_price += $item['price'] * $item['quantity']; ?>
                                <div class="product-item">
                                    <div class="img-name" style="display: flex; align-items: center; gap: 10px;">
                                        <img src="/phpets/uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Product Image">
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                    <span><?php echo $item['quantity']; ?></span>
                                    <span>₱<?php echo number_format($item['price'], 2); ?></span>
                                    <span>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                            <div class="total-container">
                                <span>Total:</span>
                                <span class="total">₱<?php echo number_format($total_price, 2); ?></span>
                            </div>
                        </div>
                        <form class="confirm-container" method="POST" action="process_checkout_cart.php">
                            <input type="hidden" name="seller_id" value="<?php echo $seller_id; ?>">
                            <button class="confirm-btn-buyer cool-btn" type="submit" name="confirm_checkout">Confirm Checkout for this Seller</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <!-- User info section -->
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
                </div>
            </div>
        </div>
        
            
        </div>
    </body>
</html>

<?php 
    include ('../includes/view-modal.php');
?>