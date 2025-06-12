<?php 
    include '../includes/db_connect.php';
    include '../includes/header.php';
    session_start();

    $view_mode = isset($_COOKIE['view']) ? $_COOKIE['view'] : 'light';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }

    $admin_id = $_SESSION['user_id'];
    $first_name = $_SESSION['first_name'];
    $middle_name = $_SESSION['middle_name'];
    $last_name = $_SESSION['last_name'];
    $address = $_SESSION['address'];
    $email = $_SESSION['email'];
    $profile_photo = $_SESSION['profile_photo'];

    // ! Fetch total users
    $total_users_query = "SELECT COUNT(*) AS total_users FROM users";
    $total_users_result = $conn->query($total_users_query);
    $total_users_row = $total_users_result->fetch_assoc();
    $total_users = $total_users_row['total_users'] ?? 0; // Default to 0 if no users

    // ? Fetch total buyers
    $total_buyers_query = "SELECT COUNT(*) AS total_buyers FROM users WHERE role = 'buyer'";
    $total_buyers_result = $conn->query($total_buyers_query);
    $total_buyers_row = $total_buyers_result->fetch_assoc();
    $total_buyers = $total_buyers_row['total_buyers'] ?? 0; // Default to 0 if no buyers

    // ? Fetch total sellers
    $total_sellers_query = "SELECT COUNT(*) AS total_sellers FROM users WHERE role = 'seller'";
    $total_sellers_result = $conn->query($total_sellers_query);
    $total_sellers_row = $total_sellers_result->fetch_assoc();
    $total_sellers = $total_sellers_row['total_sellers'] ?? 0; // Default to 0 if no sellers

    // * Fetch all users
    $all_users_query = "SELECT user_id, first_name, last_name, email, role, created_at, status, contact_number, profile_photo FROM users ORDER BY created_at DESC";
    $all_users_result = $conn->query($all_users_query);

    // * Fetch total products
    $total_products_query = "SELECT COUNT(*) AS total_products FROM products";
    $total_products_result = $conn->query($total_products_query);
    $total_products_row = $total_products_result->fetch_assoc();
    $total_products = $total_products_row['total_products'] ?? 0; // Default to 0 if no products

    // * Fetch total orders
    $total_orders_query = "SELECT COUNT(*) AS total_orders FROM orders";
    $total_orders_result = $conn->query($total_orders_query);
    $total_orders_row = $total_orders_result->fetch_assoc();
    $total_orders = $total_orders_row['total_orders'] ?? 0; // Default to 0 if no orders


    // * Fetch all products, with 'pending' products first
    $all_products_query = "
        SELECT product_id, name, description, price, stock, status, created_at 
        FROM products 
        ORDER BY 
            CASE 
                WHEN status = 'pending' THEN 1
                ELSE 2
            END, 
            created_at DESC";
    $all_products_result = $conn->query($all_products_query);

    // ? Update User's Info
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
        $updated_first_name = htmlspecialchars(trim($_POST['first_name']));
        $updated_middle_name = htmlspecialchars(trim($_POST['middle_name']));
        $updated_last_name = htmlspecialchars(trim($_POST['last_name']));
        $updated_email = htmlspecialchars(trim($_POST['email']));
        $updated_address = htmlspecialchars(trim($_POST['address']));

        // ? Update the user's information in the database
        $update_user_query = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, email = ?, address = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_user_query);
        $stmt->bind_param("sssssi", $updated_first_name, $updated_middle_name, $updated_last_name, $updated_email, $updated_address, $buyer_id);

        if ($stmt->execute()) {
            // ? Update session variables
            $_SESSION['first_name'] = $updated_first_name;
            $_SESSION['middle_name'] = $updated_middle_name;
            $_SESSION['last_name'] = $updated_last_name;
            $_SESSION['email'] = $updated_email;
            $_SESSION['address'] = $updated_address;

            // >> Redirect to refresh the page
            header("Location: admin.php#edit-profile");
            echo "<script> User updated success</script>";
            exit();
        } else {
            echo "<script> Update Failed</script>";
            echo "Error updating user information: " . $stmt->error;
        }
    }

    // ? Update user photo
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_photo'])) {
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['profile_photo']['tmp_name'];
            $file_name = $_FILES['profile_photo']['name'];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            // Generate a unique file name
            $unique_file_name = uniqid('profile_', true) . '.' . $file_ext;

            // Define the upload directory
            $upload_dir = '../uploads/';
            $upload_path = $upload_dir . $unique_file_name;

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Update the profile_photo field in the database
                $update_photo_query = "UPDATE users SET profile_photo = ? WHERE user_id = ?";
                $stmt = $conn->prepare($update_photo_query);
                $stmt->bind_param("si", $unique_file_name, $buyer_id);

                if ($stmt->execute()) {
                    // Update the session variable
                    $_SESSION['profile_photo'] = $unique_file_name;

                    // Redirect to refresh the page
                    header("Location: admin.php#edit-profile");
                    exit();
                } else {
                    echo "<script>alert('Failed to update profile photo in the database.');</script>";
                }
            } else {
                echo "<script>alert('Failed to upload the file.');</script>";
            }
        } else {
            echo "<script>alert('No file uploaded or an error occurred.');</script>";
        }
    }

    // --- Create Admin Logic ---
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_admin'])) {
        $email = trim($_POST['admin_email']);
        $password = $_POST['admin_password'];
        $confirm = $_POST['admin_confirm_password'];

        // Basic validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('❌ Please enter a valid email address.'); window.location.href = 'admin.php#create-admin';</script>";
            exit();
        }
        if ($password !== $confirm) {
            echo "<script>alert('❌ Passwords do not match.'); window.location.href = 'admin.php#create-admin';</script>";
            exit();
        }

        // Check if email already exists
        $check_sql = "SELECT user_id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo "<script>alert('❌ Email already exists.'); window.location.href = 'admin.php#create-admin';</script>";
            $check_stmt->close();
            exit();
        }
        $check_stmt->close();

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';
        $first_name = 'Admin';
        $middle_name = '';
        $last_name = '';
        $address = '';
        $contact_number = '';

        $sql = "INSERT INTO users (first_name, middle_name, last_name, email, password, address, role, contact_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $email, $hashed_password, $address, $role, $contact_number);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Admin account created successfully!'); window.location.href = 'admin.php#accounts';</script>";
        } else {
            error_log('SQL Error: ' . $stmt->error);
             echo "<script>
                alert('✅ Admin account created successfully!\\nUsername: " . addslashes($email) . "\\nPassword: " . addslashes($password) . "');
                window.location.href = 'admin.php#accounts';
            </script>";
        }
        $stmt->close();
    }
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php if ($view_mode == 'dark'): ?>
            <link rel="stylesheet" href="/phpets/assets/css/admin.css" />
            <link rel="stylesheet" href="/phpets/assets/css/index.css" />
        <?php else: ?>
            <link rel="stylesheet" href="/phpets/assets/css/admin-light.css" />
            <link rel="stylesheet" href="/phpets/assets/css/index-light.css" />
        <?php endif ?>
        <title>Admin Pannel</title>
    </head>

    <body>
        <div id="admin">
            <div class="left">
                <div class="user-details">
                    <img class="admin-image" src="../uploads/<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Picture" width="100">
                    <span class="fullname"> <?php echo $first_name . ' ' . $middle_name . ' ' . $last_name; ?></span>
                    <div class="role-container">
                        <span class="role">
                            <?php echo ucfirst($_SESSION['role']); ?>
                        </span>
                        <img class="fire" src="/phpets/assets/images/fire.gif" alt="">
                    </div>
                    
                    <span class="address"><?php echo $address; ?></span>
                </div>

                <div class="animate-fadein-left">
                    <a class="link-navs" href="#accounts">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/user.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/user-dark.svg">
                        <?php endif ?>
                        <span>Accounts</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#products">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/cart-bag.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/cart-dark.svg">
                        <?php endif ?>
                        <span>Products</span>
                    </a>
                </div>

                <div class="animate-fadein-left">
                    <a class="link-navs" href="#edit-profile">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/edit-profile.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/edit-profile-dark.svg">
                        <?php endif ?>
                        <span>Edit Profile</span>
                    </a>
                </div>           
                
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#create-admin">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/add.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/add-dark.svg">
                        <?php endif ?>
                        <span>Create Admin</span>
                    </a>
                </div>          
            </div>

            <div class="right">
                <div class="dashboard">
                    <div id="total-accounts" class="data-card">
                        <div class="heading-2">
                            <?php if ($view_mode == 'dark'): ?>
                                <img src="/phpets/assets/images/user.svg">
                            <?php else: ?>
                                <img src="/phpets/assets/images/user-dark.svg">
                            <?php endif ?>
                            <h2>Total Accounts</h2>
                        </div>
                        <p class="strong"><?php echo $total_users; ?></p>
                        <div class="footer">
                            <span>Buyers: <strong><?php echo "$total_buyers"; ?></strong></span>
                            <span>Sellers: <strong><?php echo "$total_sellers"; ?></strong></span>
                        </div>
                    </div>
                    <div id="total-products" class="data-card">
                        <div class="heading-2">
                            <?php if ($view_mode == 'dark'): ?>
                                <img src="/phpets/assets/images/cart-bag.svg">
                            <?php else: ?>
                                <img src="/phpets/assets/images/cart-dark.svg">
                            <?php endif ?>
                            <h2>Total Products</h2>
                        </div>
                        <p class="strong"><?php echo $total_products; ?></p>
                    </div>
                    <div id="total-transactions" class="data-card">
                        <div class="heading-2">
                            <?php if ($view_mode == 'dark'): ?>
                                <img src="/phpets/assets/images/transaction.svg">
                            <?php else: ?>
                               <img src="/phpets/assets/images/transaction-dark.svg">
                            <?php endif ?>
                            <h2>Transactions</h2>
                        </div>
                        <p class="strong"><?php echo $total_orders; ?></p>
                    </div>
                </div>
                
                <div id="accounts">
                    <div class="heading">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/user.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/user-dark.svg">
                        <?php endif ?>
                        <h2>All Accounts</h2>
                    </div>
                    <div class="list-table-content">
                        <?php if ($all_users_result->num_rows > 0): ?>
                            <table class="accounts-table">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact Number</th>
                                        <th>Role</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $all_users_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $user['user_id']; ?></td>
                                            <td>
                                                <img 
                                                    src="../uploads/<?php echo htmlspecialchars($user['profile_photo'] ?? 'default.jpg'); ?>" 
                                                    alt="Profile" 
                                                    style="width:32px; height:32px; border-radius:50%; object-fit:cover; vertical-align:middle; margin-right:8px;"
                                                >
                                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['contact_number'] ?? 'No Data'); ?></td>
                                            <td class="<?php echo ($user['role']); ?>"><?php echo ucfirst($user['role']); ?></td>
                                            <td><?php echo date('F j, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <?php if ($user['status'] === 'good'): ?>
                                                    <form method="POST" action="ban_user.php">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                        <button type="submit" class="ban-btn cool-btn">Ban</button>
                                                    </form>
                                                <?php elseif ($user['status'] === 'banned'): ?>
                                                    <form method="POST" action="unban_user.php">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                        <button type="submit" class="unban-btn cool-btn">Unban</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No accounts found.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="products">
                    <div class="heading" style="margin-top: 10px;">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/cart-bag.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/cart-dark.svg">
                        <?php endif ?>
                        <h2>All Products</h2>
                    </div>
                    <div class="list-table-content">
                        <?php if ($all_products_result->num_rows > 0): ?>
                            <table class="products-table">
                                <thead>
                                    <tr>
                                        <!-- <th>ID</th> -->
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th class="action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($product = $all_products_result->fetch_assoc()): ?>
                                        <tr>
                                            <!-- <td><?php echo $product['product_id']; ?></td> -->
                                            <td class="name-photo">
                                                <?php
                                                    $product_image_query = "SELECT image FROM products WHERE product_id = ?";
                                                    $stmt_img = $conn->prepare($product_image_query);
                                                    $stmt_img->bind_param("i", $product['product_id']);
                                                    $stmt_img->execute();
                                                    $img_result = $stmt_img->get_result();
                                                    $img_row = $img_result->fetch_assoc();
                                                    $image_file = $img_row && $img_row['image'] ? $img_row['image'] : 'default-product.png';
                                                ?>
                                                <img 
                                                    src="../uploads/<?php echo htmlspecialchars($image_file); ?>" 
                                                    alt="Product Image" 
                                                    style="width:100px; height:100px; border-radius:6px; object-fit:cover; vertical-align:middle; margin-right:8px;"
                                                >
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                            <td><?php echo $product['stock']; ?> pcs</td>
                                            <td class="<?php echo $product['status']; ?>"><?php echo ucfirst($product['status']); ?></td>
                                            <td><?php echo date('F j, Y', strtotime($product['created_at'])); ?></td>
                                            <td>
                                                <?php if ($product['status'] === 'pending'): ?>
                                                    <form method="POST" action="approve_product.php">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                                        <button type="submit" class="approve-btn cool-btn">Approve</button>
                                                    </form>
                                                <?php elseif ($product['status'] === 'unlisted'): ?>
                                                    <form method="POST" action="unlist_product.php">
                                                        <button type="submit" class="disabled-btn">Unlist</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" action="unlist_product.php">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                                        <button type="submit" class="unlist-btn cool-btn">Unlist</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No products found.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="edit-profile" style="margin-top: 40px">
                    <div class="heading mb-20">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/edit-profile.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/edit-profile-dark.svg">
                        <?php endif ?>
                        <h2>Edit Profile</h2>
                    </div>
                    <form action="" method="POST">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" value="<?php echo htmlspecialchars($first_name); ?>" required />

                        <label for="middle_name">Middle Name:</label>
                        <input type="text" id="middle_name" name="middle_name" placeholder="Enter your middle name (optional)" value="<?php echo htmlspecialchars($middle_name); ?>" />

                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" value="<?php echo htmlspecialchars($last_name); ?>" required />

                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required />

                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" placeholder="Street, Barangay, Municipal, Province" value="<?php echo htmlspecialchars($address); ?>" required />
                        
                        <div class="save-btn-container">
                            <button type="submit" name="update_user" class="save-btn cool-btn">Save Changes</button>
                        </div>
                    </form>
                    <form method="POST" enctype="multipart/form-data">
                        <h2>Change Profile Photo</h2>
                        <input type="file" name="profile_photo" required>
                        <button type="submit" name="update_photo" class="save-btn cool-btn">Update Photo</button>
                    </form>
                </div>

                <div id="create-admin" style="margin-top: 40px">
                    <div class="heading mb-20">
                        <?php if ($view_mode == 'dark'): ?>
                            <img src="/phpets/assets/images/edit-profile.svg">
                        <?php else: ?>
                            <img src="/phpets/assets/images/edit-profile-dark.svg">
                        <?php endif ?>
                        <h2>Create New Admin</h2>
                    </div>
                    <form action="" method="POST" style="width: 100%;">
                        <label for="admin_email">Email:</label>
                        <input type="email" id="admin_email" name="admin_email" placeholder="Enter admin email" required />

                        <label for="admin_password">Password:</label>
                        <input type="password" id="admin_password" name="admin_password" placeholder="Enter password" required />

                        <label for="admin_confirm_password">Confirm Password:</label>
                        <input type="password" id="admin_confirm_password" name="admin_confirm_password" placeholder="Confirm password" required />

                        <button type="submit" name="create_admin" class="save-btn cool-btn" style="margin-top: 10px;">Create Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>

<?php 
    include ('../includes/view-modal.php');
?>