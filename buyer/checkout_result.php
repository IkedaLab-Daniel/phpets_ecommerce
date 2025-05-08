<?php
    include ('../includes/header.php');
    $status = $_GET['status'] ?? 0;

    $view_mode = isset($_COOKIE['view']) ? $_COOKIE['view'] : 'light';
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Checkout Result</title>
        <?php if ($view_mode == 'dark'): ?>
            <link rel="stylesheet" href="/phpets/assets/css/checkout.css">
        <?php else: ?>
            <link rel="stylesheet" href="/phpets/assets/css/checkout-light.css">
            <link rel="stylesheet" href="/phpets/assets/css/index-light.css">
        <?php endif ?>
    </head>
    <body>
        <div id="checkout-result">
            <?php if ($status == 'success'): ?>
                <div class="container">
                    <img src="/phpets/assets/images/checkmark-green.svg" alt="">
                    <h2 class="success-green">Checkout Successful!</h2>
                    <p>Your order has been placed.</p>
                </div>
            <?php else: ?>
                <div class="container">
                    <img src="/phpets/assets/images/cross.svg" alt="">
                    <h2 class="failed-red">Checkout Failed!</h2>
                    <p>Something went wrong. Please try again.</p>
                </div>
            <?php endif; ?>
            <a class="return cool-btn" href="buyer.php">Go to Dashboard</a>
            <a class="more cool-btn" href="/phpets/index.php#product-section">Browse More</a>
        </div>
        
    </body>
</html>

<?php 
    include ('../includes/view-modal.php');
?>