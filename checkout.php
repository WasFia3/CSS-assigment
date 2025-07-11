<?php
// Start session (if you need to clear cart)
session_start();

// Clear cart (optional)
unset($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Success</title>
    
</head>
<body>
    <div class="success-box">
        <div class="checkmark">✓</div>
        <h1>Order Successful!</h1>
        <p>Thank you for your purchase.</p>
        <p>Your order has been placed successfully.</p>
        <a href="products.php" class="btn">Back to Shopping</a>
    </div>
</body>
</html>