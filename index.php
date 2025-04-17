<?php
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
</head>
<body>
    <h1>Welcome to PHPets!</h1>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Log In</a>
    <?php endif; ?>
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
</body>
</html>
