<?php
    include 'db_connect.php';
    include './error_catch.php';

    if (isset($_SESSION['user_id'])) {

        $user_id = $_SESSION['user_id'];
        $status_query = "SELECT status FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($status_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['status'] === 'banned') {
            // Log the user out
            session_unset();
            session_destroy();
            header("Location: /phpets/includes/banned.php");
            exit();
        }
    }
    
?>