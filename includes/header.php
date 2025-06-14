<?php
    session_start(); 

    $view_mode = isset($_COOKIE['view']) ? $_COOKIE['view'] : 'light';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/phpets/assets/css/index.css" />
        <?php if ($view_mode == 'light'): ?>
            <link rel="stylesheet" href="/phpets/assets/css/header-light.css" />
        <?php else: ?>
            <link rel="stylesheet" href="/phpets/assets/css/header.css" />
        <?php endif ?>
       
        <link rel="icon" type="image/svg" href="/phpets/assets/images/paw.svg" />
    </head>
    <body>
        <nav>
            <div class="logo">
                <a href="/phpets/index.php">
                    <img src="/phpets/assets/images/paw.svg" />
                    <span class="violet">PHP</span><span class="white">ets</span>
                </a>
            </div>
            <div class="nav-middle">
                <a class="nav-link" href="/phpets/index.php">Products</a>
                <a class="nav-link" href="/phpets/about.php">About</a>
                <a class="nav-link" href="/phpets/index.php#categories-scroll">Categories</a>
            </div>
            <div class="login-signup-btns">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span 
                        onclick="window.location.href='/phpets/<?php 
                            echo ($_SESSION['role'] === 'buyer') ? 'buyer/buyer.php' : 
                                (($_SESSION['role'] === 'seller') ? 'seller/seller.php' : 'admin/admin.php'); 
                        ?>';"
                        title="Go to your dashboard"
                        class="name"
                    >
                        <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>
                    </span>
                    <a href="/phpets/logout.php" class="logout-btn">Log Out</a>
                <?php else: ?>
                    <a href="/phpets/login.php" class="login-btn">Log In</a>
                    <a href="/phpets/register.php" class="signup-btn">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </body>
</html>