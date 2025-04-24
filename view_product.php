<?php
    session_start();
    include './includes/db_connect.php';
    include './includes/header.php';

    if (!isset($_GET['id'])) {
        echo "No product selected.";
        exit();
    }

    $product_id = intval($_GET['id']);

    // Fetch product details
    $product_sql = "SELECT p.*, u.first_name, u.last_name, u.profile_photo, c.name AS category_name 
                    FROM products p
                    JOIN users u ON p.seller_id = u.user_id
                    JOIN categories c ON p.category_id = c.category_id
                    WHERE p.product_id = ?";
    $stmt = $conn->prepare($product_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    
    if ($product_result->num_rows === 0) {
        echo "Product not found.";
        exit();
    }
    
    $product = $product_result->fetch_assoc(); // Includes category_name

    // Fetch reviews
    $review_sql = "SELECT r.*, u.first_name, u.last_name, u.profile_photo 
                FROM reviews r
                JOIN users u ON r.buyer_id = u.user_id
                WHERE r.product_id = ?
                ORDER BY r.review_date DESC";
    $stmt = $conn->prepare($review_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $review_result = $stmt->get_result();

    // Average rating
    $avg_sql = "SELECT AVG(rating) AS average_rating 
                FROM reviews 
                WHERE product_id = ?";
    $stmt = $conn->prepare($avg_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $avg_result = $stmt->get_result();
    $avg_row = $avg_result->fetch_assoc();
    $average_rating = $avg_row['average_rating'] ? number_format($avg_row['average_rating'], 1) : "No ratings yet";

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
        header("Location: login.php");
        exit();
    }
    
    $buyer_id = $_SESSION['user_id'];
    $product_id = $_GET['id']; 
    
    // Add to cart
    if (isset($_POST['add_to_cart'])) {
        $quantity = intval($_POST['quantity']);
    
        // ? Check if already in cart -> If yes, don't make another item on cart, but increate QTY instead
        $check_sql = "SELECT * FROM cart WHERE buyer_id = $buyer_id AND product_id = $product_id";
        $check_result = mysqli_query($conn, $check_sql);
    
        if (mysqli_num_rows($check_result) > 0) {
            // * Update quantity here
            $update_sql = "UPDATE cart SET quantity = quantity + $quantity WHERE buyer_id = $buyer_id AND product_id = $product_id";
            mysqli_query($conn, $update_sql);
        } else {
            // Insert new cart entry
            $insert_sql = "INSERT INTO cart (buyer_id, product_id, quantity) VALUES ($buyer_id, $product_id, $quantity)";
            mysqli_query($conn, $insert_sql);
                // TODO - Error catching
        }
    
        echo "<div class = 'added-to-cart'>
                    <img class = 'check' src='/phpets/assets/images/green-check.svg' width = '30'>
                    <p>Added to cart!</p>
                    <img class = 'cat' src='/phpets/assets/images/happy-cat.gif' width = '50'>
            </div>"; // ! Temporarily - Will be updated soon
    }
    
    // ? Checkout (redirect to separate file)
    if (isset($_POST['checkout_now'])) {
        header("Location: /phpets/buyer/checkout.php");
        exit();
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/svg" href="./assets/images/paw.svg" />
        <link rel="stylesheet" href="assets/css/view_product.css">
        <title><?php echo htmlspecialchars($product['name']); ?> | Product Detail</title>

    </head>
    <body style="margin-top: 5rem;">
        <div class="single-item-view">
            <div class="mini-nav">
                <div class="left">
                    <img src="/phpets/assets/images/detail.svg" alt="">
                     <h3>Product Details</h3>
                </div>
                <a href="/phpets/index.php#product-section" class="right">
                    <img src="/phpets/assets/images/back2.svg" alt="">
                    <span>Back</span>
                </a>
                
            </div>
            
            <div class="product-details">
                <div class="left">
                    <img src="uploads/<?php echo $product['image']; ?>" alt="Product Image">
                </div>
                <div class="right">
                    <h2 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h2>
                    <div class="small-rating"> 
                        <img src="/phpets/assets/images/star.svg" width="25px">
                        <span><?php echo $average_rating; ?> </span>
                    </div>
                    <p class="description"><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p> <!-- Render category -->
                    <div class="seller">
                        <img src="/phpets/uploads/<?php echo htmlspecialchars($product['profile_photo']); ?>" alt="Seller Profile Photo">
                        <p><?php echo htmlspecialchars($product['first_name'] . ' ' . $product['last_name']); ?></p>
                    </div>
                    <div class="stock">
                        <img src="/phpets/assets/images/box.svg" alt="">
                        <p><strong> <?php echo $product['stock']; ?></strong> remaining</p> 
                    </div>
                    <p class="category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                    <p class="price">₱ <?php echo number_format($product['price'], 2); ?></p>
                </div>
            </div>    
        </div>

        <div class="add-to-cart-checkout-form">
             <form method="POST" action="">
                <div class="div1">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" required> 
                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                </div>
                <div class="div2 cool-btn">
                    <button type="submit" name="add_to_cart">
                        <span class="loading">Loading</span>
                        <span class="okay">Add to Cart 🛒</span>          
                    </button>
                </div>
                <div class="div3 cool-btn">
                    <button type="submit" name="checkout_now">Check Out 💳</button>
                </div>  
            </form>
        </div>
        
        <div id="reviews">
            <div class="header">
                <div class="icon-text">
                    <img src="/phpets/assets/images/reviews.svg" alt="">
                    <h3>Customer Reviews</h3>
                </div>
                
                <div class="small-rating"> 
                    <img src="/phpets/assets/images/star.svg" width="25px">
                    <span><?php echo $average_rating; ?> / 5.0</span>
                </div>
            </div>  

            <div class="reviews-container">
                <?php if ($review_result->num_rows > 0): ?>
                    <div>
                        <?php while ($review = $review_result->fetch_assoc()): ?>
                            <div class="name-and-profile">
                                <img src="/phpets/uploads/<?php echo htmlspecialchars($review['profile_photo']); ?>" alt="User Profile Photo" class="review-profile-photo">
                                <span class="name"><?php echo $review['first_name'] . ' ' . $review['last_name']; ?></span> 
                            </div>
                            <div class="review">
                                <div class="left">
                                    <img src="/phpets/assets/images/star.svg">
                                    <span><?php echo $review['rating']; ?> </span>
                                </div>
                                <div class="right">
                                    <span class="comment"><?php echo htmlspecialchars($review['comment']); ?></span><br>
                                    <span class="date"><?php echo date('F j, Y', strtotime($review['review_date'])); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No reviews yet.</p>
                <?php endif; ?>
            </div>
            
            
        </div>

        
    </body>
</html>

<?php 
    include('./includes/error_catch.php');
    if ($_SESSION['role'] == 'buyer'){
        include ("./includes/cart_modal.php");
    }
?>
