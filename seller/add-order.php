<?php 
    include ('../includes/header.php');
    include ('../includes/db_connect.php');
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../assets/css/add-product.css">
        <title>Add a Product</title>
    </head>

    <body>
        <div id="add-product">
            <div class="add-product-main">
                <h2>Add a Product</h2>
                <form action="process_add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="product_name">Product Name:</label>
                        <input type="text" id="product_name" name="product_name" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select id="category" name="category" required>
                            <option value="" disabled selected>Select a category</option>
                            <?php
                                // Fetch categories from the database
                                $categories_query = "SELECT * FROM categories";
                                $categories_result = mysqli_query($conn, $categories_query);
                                while ($category = mysqli_fetch_assoc($categories_result)) {
                                    echo "<option value='{$category['category_id']}'>{$category['name']}</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="inventory">Inventory:</label>
                        <input type="number" id="inventory" name="inventory" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (₱):</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="image">Product Image:</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>

                    <div class="btn-container">
                        <a class="back cool-btn" href="seller.php">Back</a>
                        <button type="submit" class="submit-btn cool-btn">Add Product</button>
                    </div>
                </form>
            </div>
        </div>    
    </body>
</html>
