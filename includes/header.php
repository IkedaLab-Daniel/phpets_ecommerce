<?php
    session_start(); 
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/phpets/assets/css/index.css" />
        <link rel="stylesheet" href="/phpets/assets/css/header.css" />
        <link rel="icon" type="image/svg" href="./assets/images/paw.svg" />
        <title>PHPets</title>
    </head>
    <body>
        <nav>
            <div class="logo">
                <a href="/phpets/index.php">
                    <img src="./assets/images/paw.svg" />
                    <span class="violet">PHP</span><span class="white">ets</span>
                </a>
            </div>
            <div class="nav-middle">
                <a href="/phpets/about.php">About</a>
            </div>
            <div class="login-signup-btns">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- If user is logged in, show Logout button -->
                     <?php echo "Welcome back, " . $_SESSION['first_name'] . "" ?>
                    <a href="/phpets/logout.php" class="logout-btn">Log Out</a>
                <?php else: ?>
                    <!-- If user is not logged in, show Login and Sign Up buttons -->
                    <a href="/phpets/login.php" class="login-btn">Log In</a>
                    <a href="/phpets/register.php" class="signup-btn">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </body>
</html>