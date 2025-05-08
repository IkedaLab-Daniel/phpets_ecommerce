<?php
    session_start();
    include './includes/db_connect.php';
    include './includes/header.php';

    if (!isset($_GET['id'])) {
        echo "No product selected.";
        exit();
    }

    $product_id = intval($_GET['id']);
    $view_mode = isset($_COOKIE['view']) ? $_COOKIE['view'] : 'light';


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

    // ? Fetch reviews
    $review_sql = "SELECT r.*, u.first_name, u.last_name, u.profile_photo 
                FROM reviews r
                JOIN users u ON r.buyer_id = u.user_id
                WHERE r.product_id = ?
                ORDER BY r.review_date DESC";
    $stmt = $conn->prepare($review_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $review_result = $stmt->get_result();

    // ? Average rating
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
    
    // ? Add to cart
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
        $quantity = intval($_POST['quantity']);

        // Insert the specific product into a temporary checkout table or session
        $_SESSION['checkout_product'] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $product['price'],
            'name' => $product['name'],
            'image' => $product['image']
        ];

        // Redirect to the checkout page
        header("Location: /phpets/buyer/checkout.php");
        exit();
    }

    // ?------------------------ Review Logics Here -------------------------------

    // ! Check if the user has already reviewed the product
    $review_check_sql = "SELECT * FROM reviews WHERE buyer_id = ? AND product_id = ?";
    $stmt = $conn->prepare($review_check_sql);
    $stmt->bind_param("ii", $buyer_id, $product_id);
    $stmt->execute();
    $review_check_result = $stmt->get_result();
    $existing_review = $review_check_result->fetch_assoc();

    // ! client-side-unvalidated-url-redirection Allowing unvalidated redirection based on user-specified URLs
    // ? Handle deleting an existing review
    if (isset($_POST['delete_review'])) {
        $delete_review_sql = "DELETE FROM reviews WHERE buyer_id = ? AND product_id = ?";
        $stmt = $conn->prepare($delete_review_sql);
        $stmt->bind_param("ii", $buyer_id, $product_id);

        if ($stmt->execute()) {
            echo "<p>Review deleted successfully!</p>";
            // Optionally, refresh the page to reflect the changes
            echo "<script>window.location.href = window.location.href;</script>";
        } else {
            echo "<p>Failed to delete review. Please try again.</p>";
        }
    }

    // ? Check if the user has purchased the product and the order is delivered
    $purchased_sql = "
        SELECT oi.order_id 
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.buyer_id = ? AND oi.product_id = ? AND o.status = 'delivered'";
    $stmt = $conn->prepare($purchased_sql);
    $stmt->bind_param("ii", $buyer_id, $product_id);
    $stmt->execute();
    $purchased_result = $stmt->get_result();
    $has_purchased = $purchased_result->num_rows > 0;

    // ? Handle adding a new review
    if (isset($_POST['add_review'])) {
        $rating = intval($_POST['rating']);
        $comment = htmlspecialchars(trim($_POST['comment']));

        $add_review_sql = "INSERT INTO reviews (buyer_id, product_id, rating, comment, review_date) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($add_review_sql);
        $stmt->bind_param("iiis", $buyer_id, $product_id, $rating, $comment);

        if ($stmt->execute()) {
            echo "<p>Review added successfully!</p>";
        } else {
            echo "<p>Failed to add review. Please try again.</p>";
        }
    }

    // ? Handle editing an existing review
    if (isset($_POST['edit_review'])) {
        $rating = intval($_POST['rating']);
        $comment = htmlspecialchars(trim($_POST['comment']));

        $edit_review_sql = "UPDATE reviews SET rating = ?, comment = ?, review_date = NOW() WHERE buyer_id = ? AND product_id = ?";
        $stmt = $conn->prepare($edit_review_sql);
        $stmt->bind_param("isii", $rating, $comment, $buyer_id, $product_id);

        if ($stmt->execute()) {
            echo "<p>Review updated successfully!</p>";
        } else {
            echo "<p>Failed to update review. Please try again.</p>";
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/svg" href="./assets/images/paw.svg" />
        <?php if ($view_mode == 'dark'): ?>
            <link rel="stylesheet" href="assets/css/view_product.css">
        <?php else: ?>
            <link rel="stylesheet" href="assets/css/view_product-light.css">
            <link rel="stylesheet" href="assets/css/index-light.css">
        <?php endif ?>
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
                        <!-- <span class="loading">Loading</span> -->
                        <span class="okay">Add to Cart 🛒</span>          
                    </button>
                </div>
                <div class="div3 cool-btn">
                    <button type="submit" name="checkout_now">Check Out 💳</button>
                </div>  
            </form>
        </div>

        <div id="add-edit-review">
            <?php if ($has_purchased): ?>
                <div class="icon-text" style="justify-content: left;">
                    <img src="/phpets/assets/images/write.svg" >
                    <h3><?php echo $existing_review ? "Edit Your Review" : "Add a Review"; ?></h3>
                </div>
                <form method="POST" action="">
                    <div class="rating-container">
                        <label for="rating">Rating:</label>
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" <?php echo $existing_review && $existing_review['rating'] == 5 ? "checked" : ""; ?> />
                            <label for="star5">
                                <img src="/phpets/assets/images/star-grey.svg" alt="5 stars" data-value="5">
                            </label>

                            <input type="radio" id="star4" name="rating" value="4" <?php echo $existing_review && $existing_review['rating'] == 4 ? "checked" : ""; ?> />
                            <label for="star4">
                                <img src="/phpets/assets/images/star-grey.svg" alt="4 stars" data-value="4">
                            </label>

                            <input type="radio" id="star3" name="rating" value="3" <?php echo $existing_review && $existing_review['rating'] == 3 ? "checked" : ""; ?> />
                            <label for="star3">
                                <img src="/phpets/assets/images/star-grey.svg" alt="3 stars" data-value="3">
                            </label>

                            <input type="radio" id="star2" name="rating" value="2" <?php echo $existing_review && $existing_review['rating'] == 2 ? "checked" : ""; ?> />
                            <label for="star2">
                                <img src="/phpets/assets/images/star-grey.svg" alt="2 stars" data-value="2">
                            </label>

                            <input type="radio" id="star1" name="rating" value="1" <?php echo $existing_review && $existing_review['rating'] == 1 ? "checked" : ""; ?> />
                            <label for="star1">
                                <img src="/phpets/assets/images/star-grey.svg" alt="1 star" data-value="1">
                            </label>
                        </div>
                    </div>
                    <div class="comment-container">
                        <label for="comment">Comment:</label>
                        <textarea name="comment" id="comment" required><?php echo $existing_review ? htmlspecialchars($existing_review['comment']) : ""; ?></textarea>
                    </div>
                    <div class="btn-container">
                        <?php if ($existing_review): ?>
                            <button class="delete-btn cool-btn" type="submit" name="delete_review">Delete Review</button>
                        <?php endif; ?>
                        <button class="submit-review cool-btn" type="submit" name="<?php echo $existing_review ? "edit_review" : "add_review"; ?>">
                            <?php echo $existing_review ? "Update Review" : "Submit Review"; ?>
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <p>You can only leave a review if your order has been delivered.</p>
                <?php include('./includes/error_catch.php'); ?>
            <?php endif; ?>
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
                    <p style="margin-top: 10px;">No reviews yet.</p>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const stars = document.querySelectorAll('.star-rating label img');
    const starInputs = document.querySelectorAll('.star-rating input');

    // Function to update the stars based on the selected rating
    function updateStars(selectedValue) {
        stars.forEach(star => {
            const starValue = parseInt(star.getAttribute('data-value'));
            if (starValue <= selectedValue) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }

    // Initialize stars based on the existing review
    const existingRating = <?php echo $existing_review ? $existing_review['rating'] : 0; ?>;
    updateStars(existingRating);

    // Add event listeners to the stars
    stars.forEach(star => {
        star.addEventListener('click', function () {
            const selectedValue = parseInt(this.getAttribute('data-value'));
            updateStars(selectedValue);

            // Update the corresponding radio button
            starInputs.forEach(input => {
                input.checked = parseInt(input.value) === selectedValue;
            });
        });

        star.addEventListener('mouseover', function () {
            const hoverValue = parseInt(this.getAttribute('data-value'));
            updateStars(hoverValue);
        });

        star.addEventListener('mouseout', function () {
            const selectedValue = Array.from(starInputs).find(input => input.checked)?.value || 0;
            updateStars(selectedValue);
        });
    });
});
</script>