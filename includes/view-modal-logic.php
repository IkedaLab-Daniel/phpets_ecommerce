<?php
    session_start();

    if (isset($_COOKIE['view']) && $_COOKIE['view'] === 'dark') {
        setcookie('view', 'light', time() + (86400 * 30), "/"); // Set to 'dark' for 30 days
    } else {
        setcookie('view', 'dark', time() + (86400 * 30), "/"); // Set to 'light' for 30 days
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
?>