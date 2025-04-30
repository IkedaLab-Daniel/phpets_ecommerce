<?php
    include '../includes/db_connect.php';
    session_start();

    // ! Check if the user is logged in and is a seller
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
        header("Location: ../login.php");
        exit();
    }

    // ? Check if the product_id is provided
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $seller_id = intval($_SESSION['user_id']);

        // * Update the product's status to 'unlisted'
        $unlist_query = "UPDATE products SET status = 'unlisted' WHERE product_id = ? AND seller_id = ?";
        $stmt = $conn->prepare($unlist_query);
        $stmt->bind_param("ii", $product_id, $seller_id);

        if ($stmt->execute()) {
            echo "<script>alert('Product unlisted successfully!'); window.location.href = 'seller.php';</script>";
        } else {
            echo "<script>alert('Failed to unlist the product. Please try again.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid request.'); window.history.back();</script>";
    }
?>