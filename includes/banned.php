<?php
    echo "<div id='toast-data' data-message=' ❌ GET OUT: Account Banned!' data-type='banned' data-img='/phpets/assets/images/getout.png'></div>";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php
            // Use the same style logic as checkout_result.php
            $view_mode = isset($_COOKIE['view']) ? $_COOKIE['view'] : 'light';
            if ($view_mode == 'dark') {
                echo '<link rel="stylesheet" href="/phpets/assets/css/checkout.css">';
                echo '<link rel="stylesheet" href="/phpets/assets/css/index.css">';
            } else {
                echo '<link rel="stylesheet" href="/phpets/assets/css/checkout-light.css">';
                echo '<link rel="stylesheet" href="/phpets/assets/css/index-light.css">';
            }
        ?>
        <title>Account Banned</title>
    </head>
    <body>
        <div id="checkout-result">
            <div class="container">
                <img src="/phpets/assets/images/getout.png" alt="Banned" style="width: 100px; height: 100px;">
                <h2 class="failed-red">Account Banned</h2>
                <p>Your account has been banned.</p>
            </div>
            <a class="return cool-btn" href="/phpets/login.php">Log In</a>
            <a class="more cool-btn" href="/phpets/register.php">Sign Up</a>
        </div>
        <div id="toast-container"></div>
        <script src="/phpets/assets/js/toast.js"></script>
        <?php
            // Show toast for banned
            echo "<div id='toast-data' data-message='❌ GET OUT: Account Banned!' data-type='banned' data-img='/phpets/assets/images/getout.png'></div>";
        ?>
    </body>
</html>

<?php 
    include ('../includes/view-modal.php');
?>