<?php 
    include ('./includes/header.php');
    include ('./includes/db_connect.php');
    session_start();
    
    $view_mode = isset($_COOKIE['view']) ? $_COOKIE['view'] : 'light';
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

    // Get the full category name or default to "Unknown Category"
    $category_display_name = isset($fullcategoryname[$category]) ? $fullcategoryname[$category] : "Unknown Category";

    // Fetch products based on the category
    $query = "SELECT p.product_id, p.name, p.description, p.price, p.image, c.name AS category, u.first_name AS seller 
              FROM products p
              JOIN categories c ON p.category_id = c.category_id
              JOIN users u ON p.seller_id = u.user_id
              WHERE c.category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get the total number of products
    $total_products = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Category - <?php echo htmlspecialchars($category_display_name); ?></title>
        <?php if ($view_mode == 'dark'): ?>
            <link rel="stylesheet" href="/phpets/assets/css/category.css" >
            <link rel="stylesheet" href="/phpets/assets/css/index.css" >
        <?php else: ?>
            <link rel="stylesheet" href="/phpets/assets/css/category-light.css" >
            <link rel="stylesheet" href="/phpets/assets/css/index-light.css" >
        <?php endif ?>
    </head>

    <body style="margin-top: 4rem;">
        <div class="hero">
            <div class="text-element-2">
                <div class="left">
                    <h1><?php echo "$category_display_name"; ?></h1>
                    <div>
                        <?php if ($total_products > 0): ?>
                            <p><?php echo $total_products; ?> <?php echo "$category_display_name"; ?> for your pet!</p>
                        <?php else: ?>
                            <p><b>No item yet</b></p>
                        <?php endif ?>
                    </div>
                    <div class="hero-btn-container">
                        <a href="/phpets/index.php#categories-scroll" class="categories-btn long-btn">
                            <img src="/phpets/assets/images/back-dark.svg" alt="">
                            <span>Back</span>
                        </a>
                        <a href="#product-section" class="view-products long-btn">
                            <img src="/phpets/assets/images/cart-bag.svg" alt="">
                            <span>Browse</span>
                        </a>
                    </div>
                </div>

                <div class="right">
                    <form class="search-bar" action="search.php" method="GET">
                        <input id="search" name="q" type="search" placeholder="Dog Food, pussy..." required />
                        <button class="cool-btn" type="submit">
                            <img src="/phpets/assets/images/search-dark.svg" alt="">
                        </button>    
                    </form>
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
                </div>
            </div>
        </div> 

         <!-- Product Section -->
         <div id="product-section">
            <div class="section-head">
                <?php if ($view_mode == 'dark'): ?>
                    <img src="./assets/images/cart-bag.svg" >
                <?php else: ?>
                    <img src="./assets/images/cart-dark.svg" >
                <?php endif ?>
                <h2>Products</h2>
            </div>
            <div class="product-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <div class="product-card">
                            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image">
                            <span class="category-tag"><?php echo htmlspecialchars($row['category']); ?></span>
                            <div class="product-card-detail">
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                <p><?php echo htmlspecialchars($row['description']); ?></p>
                                <p><strong>Seller:</strong> <?php echo htmlspecialchars($row['seller']); ?></p>
                            </div>
                            <div class="product-card-footer">
                                <p>₱<?php echo number_format($row['price'], 2); ?></p>
                                <a href="view_product.php?id=<?php echo $row['product_id']; ?>">View</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-item">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/empty-light-2.svg" alt="">
                        <?php else: ?>
                            <img src="/phpets/assets/images/empty-dark-2.svg" alt="">
                        <?php endif ?>
                        <p>No products found in this category.</p>
                    </div>
                    
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>

<?php 
    if ($_SESSION['role'] == 'buyer'){
        include ("./includes/cart_modal.php");
    }
    include ('./includes/view-modal.php');
?>