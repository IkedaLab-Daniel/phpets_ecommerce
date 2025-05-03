<?php 
    include ('./includes/header.php');
    include ('./includes/db_connect.php');
    session_start();
    
    $category = $_GET['q'];

    // * Map category query to their actual value
    $fullcategoryname = [
        "1" => "Pet Foods",
        "2" => "Pet Toys",
        "3" => "Pet Accessories",
        "4" => "Pet Health",
        "5" => "Pet Grooming",
        "6" => "Pet Beds",
        "7" => "Pet Clothes",
        "8" => "Other Pet Items"
    ];

    // Get the full category name or default to "Unknown Category"
    $category_display_name = isset($fullcategoryname[$category]) ? $fullcategoryname[$category] : "Unknown Category";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Category - <?php echo htmlspecialchars($category_display_name); ?></title>
        <link rel="stylesheet" href="/phpets/assets/css/category.css" >
    </head>

    <body style="margin-top: 4rem;">
        <div class="hero">
            <div class="text-element-2">
                <div class="left">
                    <h1><?php echo "$category_display_name"; ?></h1>
                    <div>
                        <p><b>10 <?php echo "$category_display_name"; ?> for your pet!</b></p>
                    </div>
                    <div class="hero-btn-container">
                        <a href="/phpets/index.php#categories-scroll" class="categories-btn long-btn">
                            <img src="/phpets/assets/images/back-dark.svg" alt="">
                            <span>Back</span>
                        </a>
                        <a href="/phpets/index.php#categories-scroll" class="view-products long-btn">
                            <img src="/phpets/assets/images/cart-bag.svg" alt="">
                            <span>Browse</span>
                        </a>
                    </div>
                </div>

                <div class="right">
                    <div id="categories">
                        <div class="category-grid-container">
                            <a href="/phpets/category.php?q=1" class="div1 category-btn top-left">
                                <img src="./assets/images/foods.svg" alt="Foods">
                                <span>Foods</span>
                            </a>
                            <a href="/phpets/category.php?q=2" class="div2 category-btn">
                                <img src="./assets/images/toys.svg" alt="Toys">
                                <span>Toys</span>
                            </a>
                            <a href="/phpets/category.php?q=3" class="div3 category-btn">
                                <img src="./assets/images/accessories.svg" alt="Accessories">
                                <span>Accessories</span>
                            </a>
                            <a href="/phpets/category.php?q=4" class="div4 category-btn top-right">
                                <img src="./assets/images/health.svg" alt="Health">
                                <span>Health</span>
                            </a>
                            <a href="/phpets/category.php?q=5" class="div5 category-btn bottom-left">
                                <img src="./assets/images/grooming.svg" alt="Grooming">
                                <span>Grooming</span>
                            </a>
                            <a href="/phpets/category.php?q=6" class="div6 category-btn">
                                <img src="./assets/images/bed.svg" alt="Beds">
                                <span>Beds</span>
                            </a>
                            <a href="/phpets/category.php?q=7" class="div7 category-btn">
                                <img src="./assets/images/cloth.svg" alt="Clothes">
                                <span>Clothes</span>
                            </a>
                            <a href="/phpets/category.php?q=8" class="div8 category-btn bottom-right">
                                <img src="./assets/images/others.svg" alt="Others">
                                <span>Others</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </body>
</html>

<?php 
    include ('./includes/error_catch.php');
?>