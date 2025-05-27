<?php
    include './includes/header.php';
    include './includes/db_connect.php'; 
    include './includes/protect.php';

    $view_mode = isset($_COOKIE['view']) ? $_COOKIE['view'] : 'light';

    $query = "SELECT products.*, categories.name AS category, CONCAT(users.first_name, ' ', users.last_name) AS seller
            FROM products 
            JOIN categories ON products.category_id = categories.category_id
            JOIN users ON products.seller_id = users.user_id
            WHERE products.status = 'approved'";

    $result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>PHPets</title>
        <link rel="icon" type="image/svg" href="/phpets/assets/images/paw.svg" />
        <?php if ($view_mode === 'light'): ?>
            <link rel="stylesheet" href="/phpets/assets/css/index-light.css">
        <?php else: ?>
            <link rel="stylesheet" href="/phpets/assets/css/index.css">
        <?php endif ?>
        <link rel="icon" type="image/svg" href="./assets/images/paw.svg" />
    </head>
    <body>
        <main>
            <div class="hero">
                <div class="text-element">
                    <h1>Welcome to <span class="violet">PHP</span>ets</h1>
                    <div>
                        <p class="bold-text">Shop the Best for Your Best Friend</p>
                        <p>From everyday basics to special treats—find everything your pet needs to live their best life.</p>
                    </div>

                    <form class="search-bar" action="search.php" method="GET">
                        <input id="search" name="q" type="search" placeholder="Dog Food, pussy..." required />
                        <button class="cool-btn" type="submit">
                            <img src="/phpets/assets/images/search-dark.svg" alt="">
                        </button>    
                    </form>
                    
                    <div class="hero-btn-container">
                        <a href="#product-section" class="view-products cool-btn">
                            <img src="/phpets/assets/images/cart-bag.svg" alt="">
                            <span>View Products</span>
                        </a>
                        <a href="#categories-scroll" class="categories-btn cool-btn">
                            <img src="/phpets/assets/images/category-black.svg" alt="">
                            <span>Categories</span>
                        </a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'buyer'): ?>
                            <a href="/phpets/buyer/buyer.php" class="categories-btn cool-btn">
                                <img src="/phpets/assets/images/cart-bag-black.svg" alt="">
                                <span>My Orders</span>
                            </a>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller'): ?>
                            <a href="/phpets/seller/seller.php" class="categories-btn cool-btn">
                                <img src="/phpets/assets/images/bone.svg" alt="">
                                <span>My Products</span>
                            </a>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="/phpets/admin/admin.php" class="categories-btn cool-btn">Admin Panel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="section-head" id="categories-scroll">
                <?php if ($view_mode === 'dark'): ?>
                    <img src="./assets/images/category.svg" >
                <?php else: ?>
                    <img src="./assets/images/category-dark.svg" >
                <?php endif ?>
                <h2>Categories</h2>
            </div>
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
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="./assets/images/others.svg" alt="Others">
                        <?php else: ?>
                            <img src="./assets/images/others-dark.svg" width="20px" alt="Others">
                        <?php endif ?>
                        <span>Others</span>
                    </a>
                </div>
            </div>

            <div id="product-section">
                <div class="section-head">
                    <?php if ($view_mode === 'dark'): ?>
                        <img src="./assets/images/cart-bag.svg" >
                    <?php else: ?>
                        <img src="./assets/images/cart-dark.svg" >
                    <?php endif ?>
                    <h2>Products</h2>
                </div>
                <div class="product-grid">
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <div class="product-card">
                            <img src="uploads/<?php echo $row['image']; ?>" alt="Product Image">
                            <span class="category-tag"><?php echo $row['category']; ?></span>
                            <div class="product-card-detail">
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                <p class="product-description">
                                    <?php 
                                        echo htmlspecialchars(mb_strlen($row['description']) > 50 
                                            ? mb_substr($row['description'], 0, 50) . '...' 
                                            : $row['description']); 
                                        ?>
                                </p>
                                <p><strong>Seller:</strong> <?php echo $row['seller']; ?></p>
                            </div>
                            <div class="product-card-footer">
                                <p>₱<?php echo number_format($row['price'], 2); ?></p>
                                <a href="view_product.php?id=<?php echo $row['product_id']; ?>">View</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </body>
</html>

<?php 
    if (isset($_SESSION['role'])){
        if ($_SESSION['role'] == 'buyer'){
            include ("./includes/cart_modal.php");
        }
    }
    
    include ('./includes/view-modal.php');
?>