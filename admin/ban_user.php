<?php
    include '../includes/db_connect.php';
    session_start();

    // Check if the user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }

    // Check if the user_id is provided
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);

        // Prevent banning the admin themselves
        if ($user_id === $_SESSION['user_id']) {
            echo "<script>alert('You cannot ban yourself!'); window.history.back();</script>";
            exit();
        }

        // Update the user's status to 'banned'
        $ban_user_query = "UPDATE users SET status = 'banned' WHERE user_id = ?";
        $stmt = $conn->prepare($ban_user_query);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            echo "<script>alert('User banned successfully!'); window.location.href = 'admin.php';</script>";
        } else {
            echo "<script>alert('Failed to ban the user. Please try again.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid request.'); window.history.back();</script>";
    }
?>