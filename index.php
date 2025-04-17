<?php
include './includes/header.php';
include './includes/db_connect.php'; 

// Fetch products
$query = "SELECT products.*, categories.name AS category, CONCAT(users.first_name, ' ', users.last_name) AS seller
          FROM products 
          JOIN categories ON products.category_id = categories.category_id
          JOIN users ON products.seller_id = users.user_id";

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
                        <span class="view-products">View Products</span>
                        <span class="categories-btn">Categories</span>
                    </div>
                </div>
            </div>

            <div class="product-grid">
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <div class="product-card">
                        <img src="uploads/<?php echo $row['image']; ?>" alt="Product Image">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <p>₱<?php echo number_format($row['price'], 2); ?></p>
                        <p><strong>Category:</strong> <?php echo $row['category']; ?></p>
                        <p><strong>Seller:</strong> <?php echo $row['seller']; ?></p>
                        <a href="product.php?id=<?php echo $row['product_id']; ?>">View</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </main>
    </body>
</html>
