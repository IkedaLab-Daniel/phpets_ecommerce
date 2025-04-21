<?php
    session_start();
    include './includes/db_connect.php';
    include './includes/error_catch.php';

    if (!isset($_GET['id'])) {
        echo "No product selected.";
        exit();
    }

    $product_id = intval($_GET['id']);

    // Fetch product details
    $product_sql = "SELECT p.*, u.first_name, u.last_name 
                    FROM products p
                    JOIN users u ON p.seller_id = u.user_id
                    WHERE p.product_id = ?";
    $stmt = $conn->prepare($product_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();

    if ($product_result->num_rows === 0) {
        echo "Product not found.";
        exit();
    }

    $product = $product_result->fetch_assoc();

    // Fetch reviews
    $review_sql = "SELECT r.*, u.first_name, u.last_name 
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
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo htmlspecialchars($product['name']); ?> | Product Detail</title>
    </head>
    <body>
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <img src="uploads/<?php echo $product['image']; ?>" alt="Product Image" width="200">
        <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
        <p><strong>Seller:</strong> <?php echo $product['first_name'] . ' ' . $product['last_name']; ?></p>
        <p><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
        <p><strong>Average Rating:</strong> <?php echo $average_rating; ?> ⭐</p>

        <hr>

        <h3>Customer Reviews</h3>
        <?php if ($review_result->num_rows > 0): ?>
            <ul>
                <?php while ($review = $review_result->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo $review['first_name'] . ' ' . $review['last_name']; ?></strong> -
                        <?php echo $review['rating']; ?> ⭐
                        <br>
                        <em><?php echo htmlspecialchars($review['comment']); ?></em><br>
                        <small><?php echo date('F j, Y', strtotime($review['review_date'])); ?></small>
                    </li>
                    <hr>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No reviews yet.</p>
        <?php endif; ?>
    </body>
</html>
