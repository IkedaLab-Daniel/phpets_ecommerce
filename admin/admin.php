<?php 
    include '../includes/db_connect.php';
    include '../includes/header.php';
    session_start();

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
    $all_users_query = "SELECT user_id, first_name, last_name, email, role, created_at, status FROM users ORDER BY created_at DESC";
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

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/phpets/assets/css/admin.css" />
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
                    <a class="link-navs" href="">
                        <img src="/phpets/assets/images/user.svg">
                        <span>Accounts</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#cart-details">
                        <img src="/phpets/assets/images/cart-bag.svg">
                        <span>Products</span>
                    </a>
                </div>
                <div class="animate-fadein-left">
                    <a class="link-navs" href="#purchased-details">
                        <img src="/phpets/assets/images/transaction.svg">
                        <span>Transactions</span>
                    </a>
                </div>

                <div class="animate-fadein-left">
                    <a class="link-navs" href="#edit-profile">
                        <img src="/phpets/assets/images/edit-profile.svg">
                        <span>Edit Profile</span>
                    </a>
                </div>                
            </div>

            <div class="right">
                <div class="dashboard">
                    <div id="total-accounts" class="data-card">
                        <div class="heading-2">
                            <img src="/phpets/assets/images/user.svg">
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
                            <img src="/phpets/assets/images/cart-bag.svg">
                            <h2>Total Products</h2>
                        </div>
                        <p class="strong"><?php echo $total_products; ?></p>
                    </div>
                    <div id="total-transactions" class="data-card">
                        <div class="heading-2">
                            <img src="/phpets/assets/images/transaction.svg">
                            <h2>Transactions</h2>
                        </div>
                        <p class="strong"><?php echo $total_orders; ?></p>
                    </div>
                </div>
                
                <div id="accounts">
                    <div class="heading">
                        <img src="/phpets/assets/images/user.svg" alt="">
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
                                        <th>Role</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $all_users_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $user['user_id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo ucfirst($user['role']); ?></td>
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
                </div>
        </div>
    </body>
</html>

<?php
    include '../includes/error_catch.php';
?>