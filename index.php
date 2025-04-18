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
session_start()
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Pet Product Shop</title>
        <link rel="icon" type="image/svg" href="./assets/images/paw.svg" />
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
                        <a href="#product-section" class="view-products">View Products</a>
                        <span class="categories-btn">Categories</span>
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
                                <a href="product.php?id=<?php echo $row['product_id']; ?>">View</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

        </main>
    </body>
</html>
