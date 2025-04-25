<?php
    session_start();
    include '../includes/db_connect.php';
    include '../includes/header.php';
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
        header("Location: ../login.php");
        exit();
    }

    $seller_id = $_SESSION['user_id'];
    $first_name = $_SESSION['first_name'];
    $middle_name = $_SESSION['middle_name'];
    $last_name = $_SESSION['last_name'];
    $address = $_SESSION['address'];
    $email = $_SESSION['email'];
    $profile_photo = $_SESSION['profile_photo']; 

    // ? Fetch all products listed by the seller
    $products_query = "SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($products_query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $products_result = $stmt->get_result();
?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/phpets/assets/css/index.css" />
        <link rel="stylesheet" href="/phpets/assets/css/seller.css" />
        <link rel="icon" type="image/svg" href="/phpets/assets/images/paw.svg" />
        <title><?php echo $first_name . ' ' . $last_name; ?> </title>
    </head>

    <body>
        <div id="buyer">
            <div class="left">
                <div class="user-details">
                    <img src="../uploads/<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Picture" width="100">
                    <span class="fullname"> <?php echo $first_name . ' ' . $middle_name . ' ' . $last_name; ?></span>
                    <span class="role"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <span class="address"><?php echo $address; ?></span>
                </div>
                
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#cart-details">
                        <img src="/phpets/assets/images/cart-bag.svg">
                        <span>My Products</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#purchased-details">
                        <img src="/phpets/assets/images/purchase.svg">
                        <span>Pending Orders</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#all-transactions">
                        <img src="/phpets/assets/images/transaction.svg">
                        <span>All Transactions</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#edit-profile">
                        <img src="/phpets/assets/images/edit-profile.svg">
                        <span>Edit Profile</span>
                    </a>
                </div>

                
            </div>
            <div class="right">
                <div id="cart-details">
                    <div class="heading" style="margin-top: 10px;">
                        <img src="/phpets/assets/images/cart-bag.svg" alt="">
                        <h2>My Listings</h2>
                    </div>
                    <div class="list-table-content">
                        <?php if ($products_result->num_rows > 0): ?>
                            <?php while ($product = $products_result->fetch_assoc()): ?>
                                <div class="product-card">
                                    <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" width="100">
                                    <span class="category-tag"> <?php echo $product['category_id']; ?></span>
                                    <div class="product-card-detail">
                                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p> <?php echo htmlspecialchars($product['description']); ?></p>
                                        <p>Stock: <?php echo $product['stock']; ?> pcs</p>
                                        <p>₱<?php echo number_format($product['price'], 2); ?></p>
                                        <p class="<?php echo ($product['status']); ?> status"> <?php echo ucfirst($product['status']); ?></p>
                                    </div>      
                                    <div class="product-card-footer">
                                        <button class="delete cool-btn">Unlist</button>
                                    </div>                              
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No products listed yet. <a href="/phpets/seller/add_product.php" class="add-product-link">Add a Product</a></p>
                        <?php endif; ?>
                    </div>
                    

                </div>
            </div>
        </div>
    </body>
    
</html>

<?php 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>