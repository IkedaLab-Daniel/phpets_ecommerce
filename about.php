<?php   
    include ('./includes/db_connect.php');
    include ('./includes/header.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/svg" href="./assets/images/paw.svg" />
        <?php if ($view_mode == 'dark'): ?>
            <link rel="stylesheet" href="assets/css/about.css">
        <?php else: ?>   
            <link rel="stylesheet" href="assets/css/about-light.css">
            <link rel="stylesheet" href="assets/css/index-light.css">
        <?php endif ?>
        <title>About</title>
    </head>

    <body>
            <div class="hero">
                <div class="text-element">
                    <h1>About <span class="violet">PHP</span>ets</h1>

                    <div class="middle">
                        <p><b><span class="violet">PHP</span></b>ets is your one-stop online destination for everything cats, dogs, and other pets. Founded with a deep love for pets and a passion for enhancing their lives, PHPets was created to meet the growing need for a trusted, reliable, and customer-friendly platform dedicated exclusively to pet care essentials. From high-quality food and treats to grooming supplies, toys, accessories, and pet health products, we bring together a carefully curated selection of items designed to keep your furry companions happy, healthy, and pampered.</p>
                    </div>
                    
                    <div class="hero-btn-container">
                        <a href="#developers" class="view-products">Developers</a>
                        <a href="./index.php" class="categories-btn">Back to Home</a>
                    </div>
                </div>
            </div>    
            <div id="developers">
                <div class="section-head">
                    <?php if ($view_mode == 'dark'): ?>
                        <img src="./assets/images/code.svg" >
                    <?php else: ?>
                        <img src="./assets/images/code-dark.svg" >
                    <?php endif ?>
                    <h2>Developers</h2>
                </div>
                <div class="devs-card-container">
                    <!-- Daniel -->
                    <div class="dev-card daniel">
                        <div class="dev-img-container">
                            <img src="./assets/images/daniel2.jpeg" alt="">
                        </div>
                        <div class="dev-details-container">
                            <p class="quote">"Spend nights improving"</p>
                            <p class="name">Callejas</p>
                            <p class="role">Beginner Dev</p>
                            <div class="socials-container">
                                <a href="https://github.com/IkedaLab-Daniel" target="_blank" class="social github">
                                    <img src="./assets/images/github.svg" >
                                    <span>GitHub</span>
                                </a>
                                <a href="https://www.credly.com/users/mark-daniel-callejas" target="_blank" class="social credly">
                                    <img src="./assets/images/credly-2.png" >
                                    <span>Credly</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Brix -->
                    <div class="dev-card brix">
                        <div class="dev-img-container">
                            <img src="./assets/images/brix2.jpeg" alt="">
                        </div>
                        <div class="dev-details-container">
                            <p class="quote">"Write clean code, live a clean life"</p>
                            <p class="name">Ibuna</p>
                            <p class="role">UI / UX Designer</p>
                            <div class="socials-container">
                                <a href="https://www.facebook.com/brixandrew.ibuna" target="_blank" class="social facebook">
                                    <img src="./assets/images/facebook.svg" >
                                    <span>Facebook</span>
                                </a>
                                <a href="https://www.credly.com/users/brix-andrew-ibuna" target="_blank" class="social credly">
                                    <img src="./assets/images/credly-2.png" >
                                    <span>Credly</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="dev-card alvin">
                        <div class="dev-img-container">
                            <img src="./assets/images/alvin.jpg" alt="">
                        </div>
                        <div class="dev-details-container">
                            <p class="quote">"Refactor your thoughts, not just your code."</p>
                            <p class="name">Castro</p>
                            <p class="role">Late-Night Coder</p>
                            <div class="socials-container">
                                <a href="https://www.facebook.com/tewss15" target="_blank" class="social facebook">
                                    <img src="./assets/images/facebook.svg" >
                                    <span>Facebook</span>
                                </a>
                                <a href="https://github.com/devtews" target="_blank" class="social github">
                                    <img src="./assets/images/github.svg" >
                                    <span>GitHub</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </body>
</html>

<?php 
    if (isset($_SESSION['role'])){
        if ($_SESSION['role'] == 'buyer'){
            include ("./includes/cart_modal.php");
        }
    }
    
    include ("./includes/view-modal.php");
?>