<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/index.css" />
    <link rel="stylesheet" href="./assets/css/login.css" />
    <title>Log In to PHPets!</title>
</head>
<body>
    <div class="login-page">
        <div class="login-main">
            <div class="left">
                <div class="text-content">
                    <div class="logo">
                        <img src="./assets/images/paw.svg" />
                        <span class="violet">PHP</span><span class="white">ets</span>
                    </div>
                    <div class="middle">
                        <p>High-quality supplies tailored for your pet’s health, happiness, and style—delivered to your door.</p>
                        <div class="view-products-wrapper">
                            <a class="view-products" href="index.php">
                                <span >View Products</span>
                            </a>
                        </div>
                    </div>
                    <div></div>
                    
                </div>
                
            </div>
            <div class="right">
                <form class="login" action="">
                    <h1>Log In</h1>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required />

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required />

                    <button type="submit" class="black-btn">Log In</button>
                    <a class="white-btn" href="register.php" style="width: 100%;">
                        Don't have an account? Sign Up
                    </a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>