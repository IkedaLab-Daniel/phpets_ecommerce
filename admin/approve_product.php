<?php
    include '../includes/db_connect.php';
    session_start();

    // Check if the user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }

    // Check if the product_id is provided
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);

        // Update the product's status to 'approved'
        $approve_product_query = "UPDATE products SET status = 'approved' WHERE product_id = ?";
        $stmt = $conn->prepare($approve_product_query);
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            echo "<script>alert('Product approved successfully!'); window.location.href = 'admin.php';</script>";
        } else {
            echo "<script>alert('Failed to approve the product. Please try again.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid request.'); window.history.back();</script>";
    }
?>