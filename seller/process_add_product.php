<?php 
    include ('../includes/error_catch.php');
    include ('../includes/db_connect.php');

    session_start();
    // ? Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve form data
        $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $category_id = intval($_POST['category']);
        $inventory = intval($_POST['inventory']);
        $price = floatval($_POST['price']);
        $seller_id = intval($_SESSION['user_id']); 

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_name = $_FILES['image']['name'];
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_size = $_FILES['image']['size'];
            $image_error = $_FILES['image']['error'];

            // Validate image type and size
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

            if (!in_array($image_extension, $allowed_extensions)) {
                die("Invalid image type. Only JPG, JPEG, PNG, and GIF are allowed.");
            }

            if ($image_size > 2 * 1024 * 1024) { // 2MB limit
                die("Image size exceeds the 2MB limit.");
            }

            // Save the image to the uploads directory
            $upload_dir = '../uploads/';
            $image_new_name = uniqid('product_', true) . '.' . $image_extension;
            $image_path = $upload_dir . $image_new_name;

            if (!move_uploaded_file($image_tmp_name, $image_path)) {
                die("Failed to upload the image.");
            }
        } else {
            die("Image upload failed. Please try again.");
        }


        // ? Insert product into the database
        $insert_product_sql = "
            INSERT INTO products (seller_id, name, description, category_id, stock, price, image, created_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($insert_product_sql);
        $status = 'pending'; // Default status
        $stmt->bind_param("issiiiss", $seller_id, $product_name, $description, $category_id, $inventory, $price, $image_new_name, $status);

        if ($stmt->execute()) {
            echo "<script>alert('Product added successfully!'); window.location.href = 'seller.php';</script>";
        } else {
            echo "<script>alert('Failed to add product. Please try again.'); window.history.back();</script>";
        }
    }
?>