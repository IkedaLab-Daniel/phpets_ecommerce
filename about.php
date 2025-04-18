<?php
    include ('./includes/header.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/svg" href="./assets/images/paw.svg" />
        <link rel="stylesheet" href="assets/css/about.css">
        <title>About</title>
    </head>

    <body>
            <div class="hero">
                <div class="text-element">
                    <h1>About <span class="violet">PHP</span>ets</h1>

                    <div class="middle">
                        <p><b><span class="violet">PHP</span></b>ets is a web-based e-commerce platform developed as the final project for the college course “Web Development.”
                        Built with PHP and MySQL, this project demonstrates the power of back-end technologies in creating dynamic and functional websites.
                        Crafted with passion and precision by the <b>most driven and creative students (100% Confirmed) in the college</b>, PHPets is more than just a school requirement—it's a showcase of real-world development skills, user-centered design, and love for pets. </p>
                    </div>
                    
                    <div class="hero-btn-container">
                        <a href="#developers" class="view-products">Developers</a>
                        <a href="./index.php" class="categories-btn">Back to Home</a>
                    </div>
                </div>
            </div>    
            <div id="developers">
                <div class="section-head">
                    <img src="./assets/images/code.svg" >
                    <h2>Developers</h2>
                </div>
                <div class="devs-card-container">
                    <!-- Daniel -->
                    <div class="dev-card daniel">
                        <div class="dev-img-container">
                            <img src="./assets/images/ice.jpeg" alt="">
                        </div>
                        <div class="dev-details-container">
                            <p class="quote">"Wala na bang mas mahirap pa dito?"</p>
                            <p class="name">@dev.IceIce</p>
                            <p class="role">Software Engineer</p>
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
                            <img src="./assets/images/brix.jpeg" alt="">
                        </div>
                        <div class="dev-details-container">
                            <p class="quote">"Time is gold"</p>
                            <p class="name">@brixxxxxxx</p>
                            <p class="role">Role Model</p>
                            <div class="socials-container">
                                <a href="https://www.facebook.com/brixandrew.ibuna" target="_blank" class="social facebook">
                                    <img src="./assets/images/facebook.svg" >
                                    <span>Facebook</span>
                                </a>

                            </div>
                        </div>
                    </div>

                    <!-- Alvin -->
                    <div class="dev-card alvin">
                        <div class="dev-img-container">
                            <img src="./assets/images/alvin.jpg" alt="">
                        </div>
                        <div class="dev-details-container">
                            <p class="quote">"di man ako nag review (46/50 mamaya)"</p>
                            <p class="name">@yumi</p>
                            <p class="role">Pa-humble</p>
                            <div class="socials-container">
                                <a href="https://www.facebook.com/tewss15" target="_blank" class="social facebook">
                                    <img src="./assets/images/facebook.svg" >
                                    <span>Facebook</span>
                                </a>
                                <a href="" target="_blank" class="social ph">
                                    <img src="./assets/images/ph.png" >
                                    <span>PronHub</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </body>
</html>