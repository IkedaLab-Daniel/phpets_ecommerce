<?php
$status = $_GET['status'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head><title>Checkout Result</title></head>
<body>
    <?php if ($status == 'success'): ?>
        <h2 style="color: green;">Checkout Successful! 🟢</h2>
        <p>Your order has been placed.</p>
    <?php else: ?>
        <h2 style="color: red;">Checkout Failed! 🔴</h2>
        <p>Something went wrong. Please try again.</p>
    <?php endif; ?>
    <a href="buyer.php">Return to Dashboard</a>
</body>
</html>
