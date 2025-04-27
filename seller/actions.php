<?php 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>
<?php 
    include '../includes/db_connect.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $order_id = intval($_POST['order_id']);
        $action = $_POST['action'];

        if ($action === 'shipped') {
            // * WORKING
            // ? Mark order as shipped
            $update_status_sql = "UPDATE orders SET status = 'shipped' WHERE order_id = ?";
            $stmt = $conn->prepare($update_status_sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            echo "<script>alert('Order marked as shipped!'); window.location.href = 'seller.php';</script>";
        } elseif ($action === 'delivered') {
            // * WORKING
            // ? Mark order as delivered
            $update_status_sql = "UPDATE orders SET status = 'delivered' WHERE order_id = ?";
            $stmt = $conn->prepare($update_status_sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            echo "<script>alert('Order marked as delivered!'); window.location.href = 'seller.php';</script>";
        } elseif ($action === 'cancelled') {
            // * WORKING
            // ? Mark order as cancelled and add back to inventory
            $cancel_order_sql = "UPDATE orders SET status = 'cancelled' WHERE order_id = ?";
            $stmt = $conn->prepare($cancel_order_sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            // * WORKING
            // ? Add back to inventory
            $get_items_sql = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
            $stmt = $conn->prepare($get_items_sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $items_result = $stmt->get_result();

            while ($item = $items_result->fetch_assoc()) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];

                $update_stock_sql = "UPDATE products SET stock = stock + ? WHERE product_id = ?";
                $stmt = $conn->prepare($update_stock_sql);
                $stmt->bind_param("ii", $quantity, $product_id);
                $stmt->execute();
            }

            echo "<script>alert('Order marked as cancelled and inventory updated!'); window.location.href = 'seller.php';</script>";
        } elseif ($action === 'delete') {
            // * WORKING
            // ? Delete associated order items first
            $delete_items_sql = "DELETE FROM order_items WHERE order_id = ?";
            $stmt = $conn->prepare($delete_items_sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
        
            // ? Then delete the order
            $delete_order_sql = "DELETE FROM orders WHERE order_id = ?";
            $stmt = $conn->prepare($delete_order_sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
        
            echo "<script>alert('Order deleted successfully!'); window.location.href = 'seller.php';</script>";
        }
    }
?>