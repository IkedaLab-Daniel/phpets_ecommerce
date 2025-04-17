<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'] ?? null;
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $address = $_POST['address'];
    $role = $_POST['account_type'];

    // Check if passwords match
    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        // Check if email already exists
        $check_sql = "SELECT user_id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo "<script>alert('Email already exists. Please use a different one.');</script>";
        } else {
            // Email is unique, proceed with registration
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (first_name, middle_name, last_name, email, password, address, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $first_name, $middle_name, $last_name, $email, $hashed_password, $address, $role);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful! You can now log in.'); window.location.href='login.php';</script>";
            } else {
                error_log("SQL Error: " . $stmt->error);
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }

            $stmt->close();
        }

        $check_stmt->close();
        $conn->close();
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/index.css" />
    <link rel="stylesheet" href="./assets/css/signup.css" />
    <link rel="icon" type="image/svg" href="./assets/images/paw.svg" />
    <title>Sign Up to PHPets!</title>
</head>
<body>
    <div class="signup-page">
        <div class="signup-main">
            <div class="left">
                <div class="text-content">
                    <div class="logo">
                        <img src="./assets/images/paw.svg" />
                        <span class="violet">PHP</span><span class="white">ets</span>
                    </div>
                    <div class="middle">
                        <p>High-quality supplies tailored for your pet’s health, happiness, and style—delivered to your door.</p>
                        <div class="view-products-wrapper">
                            <a class="view-products" href="index.php">
                                <span >View Products</span>
                            </a>
                        </div>
                    </div>
                    <div></div>
                    
                </div>
                
            </div>
            <div class="right">
                <form class="signup" action="register.php" method="POST">
                    <h1>Sign Up</h1>
                    <div class="page-indicator">
                        <img src="./assets/images/one-closed.svg" class="one">
                        <img src="./assets/images/check-circle-svgrepo-com.svg" class="checkmark hidden" alt="">
                        <span>---------</span>
                        <img src="./assets/images/two.svg" alt="">
                    </div>
                    <div class="signin-part-1">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" required />

                        <label for="middle_name">Middle Name:</label>
                        <input type="text" id="middle_name" name="middle_name" placeholder="Enter your middle name (optional)" />

                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" />

                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required />
                    </div>
                    <div class="signin-part-2 hidden">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required />

                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required />

                        <label for="address">Address:</label>
                        <input type="tex" name="address" placeholder="Street, Barangay, Municipal, Province">

                        <div class="radio-group">
                            <div class="choice selected">
                                <input type="radio" name="account_type" value="buyer" checked required />
                                <span> Buyer</span>
                                <span></span>
                            </div>
                            <div class="choice ">
                                <input type="radio" name="account_type" value="seller" required />
                                <span> Seller</span>
                                <span></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="signup-btn hidden">Sign Up</button>
                    <button class="next-back">Next</button>
                    <a class="white-btn" href="login.php" style="width: 100%;">
                        Already Have an Account? Log In
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script src="./assets/js/register.js"></script>
</body>
</html>