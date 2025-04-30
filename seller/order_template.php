<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/phpets/assets/css/index.css" />
        <link rel="stylesheet" href="/phpets/assets/css/seller.css" />
        <title>Document</title>
    </head>
    <body>
        <div class="order-card">
            <div class="order-box-head">
                <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
                <p><strong>Buyer:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                <p><strong>Total:</strong> ₱<?php echo number_format($order['total_price'], 2); ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                <p class="<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></p>
            </div>
            <div class="order-body">
                <p><strong>Total Price:</strong> ₱<?php echo number_format($order['total_price'], 2); ?></p>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
            </div>
            <div class="order-footer">
                <?php if ($order['status'] === 'pending'): ?>
                    <form method="POST" action="process_order.php">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <button type="submit" name="mark_as_shipped" class="cool-btn">Mark as Shipped</button>
                    </form>
                <?php elseif ($order['status'] === 'shipped'): ?>
                    <form method="POST" action="process_order.php">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <button type="submit" name="mark_as_delivered" class="cool-btn">Mark as Delivered</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>