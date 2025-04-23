<?php
    include './includes/header.php';
    include './includes/db_connect.php'; 

    // Fetch products
    $query = "SELECT products.*, categories.name AS category, CONCAT(users.first_name, ' ', users.last_name) AS seller
            FROM products 
            JOIN categories ON products.category_id = categories.category_id
            JOIN users ON products.seller_id = users.user_id
            WHERE products.status = 'approved'";

    $result = mysqli_query($conn, $query);
    session_start();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>PHPets</title>
        <link rel="icon" type="image/svg" href="/phpets/assets/images/paw.svg" />
        <link rel="stylesheet" href="assets/css/index.css">
        <link rel="icon" type="image/svg" href="./assets/images/paw.svg" />
    </head>
    <body>
        <main>
            <div class="hero">
                <div class="text-element">
                    <h1>Welcome to <span class="violet">PHP</span>ets</h1>

                    <div>
                        <p><b>Shop the Best for Your Best Friend</b></p>
                        <p>From everyday basics to special treats—find everything your pet needs to live their best life.</p>
                    </div>
                    
                    <div class="hero-btn-container">
                        <a href="#product-section" class="view-products">
                            <img src="/phpets/assets/images/cart-bag.svg" alt="">
                            <span>View Products</span>
                        </a>
                        <a href="#categories-scroll" class="categories-btn">
                            <img src="/phpets/assets/images/category-black.svg" alt="">
                            <span>Categories</span>
                        </a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'buyer'): ?>
                            <a href="/phpets/buyer/buyer.php" class="categories-btn">
                                <img src="/phpets/assets/images/cart-bag-black.svg" alt="">
                                <span>My Orders</span>
                            </a>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller'): ?>
                            <a href="/phpets/seller/seller.php" class="categories-btn">
                                My Products
                            </a>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="/phpets/admin/admin.php" class="categories-btn">Admin Panel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="section-head" id="categories-scroll">
                <img src="./assets/images/category.svg" >
                <h2>Categories</h2>
            </div>
            <div id="categories">
                <div class="category-grid-container">
                    <div class="div1 category-btn top-left">
                        <img src="./assets/images/foods.svg" >
                        <span>Foods</span>
                    </div>
                    <div class="div2 category-btn">
                        <img src="./assets/images/toys.svg" >
                        <span>Toys</span>
                    </div>
                    <div class="div3 category-btn">
                        <img src="./assets/images/accessories.svg" >
                        <span>Accessories</span>
                    </div>
                    <div class="div4 category-btn top-right">
                        <img src="./assets/images/health.svg" >
                        <span>Health</span>
                    </div>
                    <div class="div5 category-btn bottom-left">
                        <img src="./assets/images/grooming.svg" >
                        <span>Grooming</span>
                    </div>
                    <div class="div6 category-btn">
                        <img src="./assets/images/bed.svg" >
                        <span>Beds</span>
                    </div>
                    <div class="div7 category-btn">
                        <img src="./assets/images/cloth.svg" >
                        <span>Clothes</span>
                    </div>
                    <div class="div8 category-btn bottom-right">
                        <img src="./assets/images/others.svg" >
                        <span>Others</span>
                    </div>
                </div>
            </div>

            <div id="product-section">
                <div class="section-head">
                    <img src="./assets/images/cart-bag.svg" >
                    <h2>Products</h2>
                </div>
                <div class="product-grid">
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <div class="product-card">
                            <img src="uploads/<?php echo $row['image']; ?>" alt="Product Image">
                            <span class="category-tag"><?php echo $row['category']; ?></span>
                            <div class="product-card-detail">
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                <p><?php echo htmlspecialchars($row['description']); ?></p>
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
    if ($_SESSION['role'] == 'buyer'){
        include ("./includes/cart_modal.php");
    }
?>