<?php
    if (!isset($message) || !isset($status)) {
        header("Location: /phpets/index.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/phpets/assets/css/checkout.css">
        <title>Checkout Status</title>
    </head>
    <body>
        <div class="checkout-status <?php echo htmlspecialchars($status); ?>">
            <div class="message-box">
                <h2><?php echo htmlspecialchars($message); ?></h2>
                <?php if ($status === "success"): ?>
                    <a href="/phpets/buyer/buyer.php#all-transactions" class="btn">View Transactions</a>
                <?php else: ?>
                    <a href="/phpets/buyer/checkout.php" class="btn">Go Back</a>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>