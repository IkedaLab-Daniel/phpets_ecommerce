<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);

    $stmt->execute();
    $result = $stmt->get_result();

    // If user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Store user info in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['middle_name'] = $user['middle_name'];
            $_SESSION['address'] = $user['address'];    
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email']; 
            $_SESSION['profile_photo'] = $user['profile_photo'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin/admin.php");
                echo "<div id='toast-data' data-message=' Logged In' data-type='success'></div>";
                echo "<script>console.log('logged in')</script>";
            } elseif ($user['role'] === 'seller') {
                header("Location: index.php");
                echo "<div id='toast-data' data-message=' Logged In' data-type='success'></div>";
            } else {
                header("Location: index.php");
                echo "<div id='toast-data' data-message=' Logged In' data-type='success'></div>";
            }
            exit;
        } else {
            echo "<div id='toast-data' data-message=' ❌ Hell Nah: Email or Password Incorrect' data-type='error' data-img='/phpets/hell_nah.png'></div>";
        }
    } else {
        echo "<div id='toast-data' data-message=' ❌ Hell Nah: Email or Password Incorrect' data-type='error' data-img='/phpets/hell_nah.png'></div>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/index.css" />
    <link rel="stylesheet" href="./assets/css/login.css" />
    <link rel="icon" type="image/svg" href="./assets/images/paw.svg" />
    <title>Log In to PHPets!</title>
</head>
<body>
    <div class="login-page">
        <div class="login-main">
            <div class="left">
                <div class="text-content">
                    <div class="logo">
                        <img src="./assets/images/paw.svg" />
                        <span class="violet">PHP</span><span class="white">ets</span>
                    </div>
                    <div class="middle">
                        <p>High-quality supplies tailored for your pet’s health, happiness, and style—delivered to your door.</p>
                        <div class="view-products-wrapper">
                            <a class="view-products" href="/phpets/index.php">
                                <span >View Products</span>
                            </a>
                        </div>
                    </div>
                    <div></div>
                    
                </div>
                
            </div>
            <div class="right">
                <form class="login" action="login.php" method="POST">
                    <h1>Log In</h1>
                    <label for="email">Email:</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email" 
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                        required 
                    />

                    <label for="password">Password:</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password" 
                        required 
                    />

                    <button type="submit" class="black-btn">Log In</button>
                    <a class="white-btn" href="register.php" style="width: 100%;">
                        Don't have an account? Sign Up
                    </a>
                </form>
            </div>
        </div>
    </div>
    <div id="toast-container"></div>
    <script src="./assets/js/toast.js"></script>
</body>
</html>