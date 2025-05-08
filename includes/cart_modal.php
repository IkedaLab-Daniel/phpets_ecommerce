<?php
    $buyer_id = $_SESSION['user_id'] ?? null;
    $total_items = 0;

    if ($buyer_id) {
        // ! Query to calculate total quantity of items in the cart
        $cart_query = "SELECT SUM(quantity) AS total_quantity FROM cart WHERE buyer_id = ?";
        $stmt = $conn->prepare($cart_query);
        $stmt->bind_param("i", $buyer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_data = $result->fetch_assoc();
        $total_items = $cart_data['total_quantity'] ?? 0; // ? Default to 0 if no items
    }
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <link rel="stylesheet" href="/phpets/assets/css/cart_modal.css" />
    </head>

    <body>
        <a  class="cart-modal" href="/phpets/buyer/buyer.php">
            <div class="cart-count">
                <span><?php echo $total_items; ?></span>
            </div>
            
            <img src="/phpets/assets/images/cart-black.svg" > 
        </a>          
    </body>
</html>