<?php
    session_start();
    include 'db_connect.php'; // Include database connection

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $status_query = "SELECT status FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($status_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if the user's status is 'banned'
    if ($user && $user['status'] === 'banned') {
        // Log the user out
        session_unset();
        session_destroy();
        header("Location: /phpets/includes/banned.php");
        exit();
    }
?>