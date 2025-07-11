<?php
require_once 'database_connection.php';

// Secure session configuration
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => false, // Set to true in production with HTTPS
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

// Add product to cart
if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    
    try {
        // Check product availability
        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product && $product['quantity'] > 0) {
            // Create unique session ID if not exists
            if (!isset($_SESSION['cart_session_id'])) {
                $_SESSION['cart_session_id'] = bin2hex(random_bytes(16));
            }
            
            // Initialize cart if not exists
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
            
            // Sync with database
            $stmt = $pdo->prepare("
                INSERT INTO cart (session_id, product_id, quantity) 
                VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE quantity = quantity + 1
            ");
            $stmt->execute([$_SESSION['cart_session_id'], $product_id]);
        }
        
        header("Location: shopping_cart.php");
        exit();
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred while adding the product to your cart.");
    }
}

// Remove product from cart
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    
    try {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            
            // Remove from database
            if (isset($_SESSION['cart_session_id'])) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ? AND product_id = ?");
                $stmt->execute([$_SESSION['cart_session_id'], $product_id]);
            }
            
            header("Location: shopping_cart.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred while removing the product from your cart.");
    }
}

// Update quantities
if (isset($_POST['update_quantity'])) {
    try {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $product_id = (int)$product_id;
            $quantity = (int)$quantity;
            
            if (isset($_SESSION['cart'][$product_id])) {
                if ($quantity > 0) {
                    $_SESSION['cart'][$product_id] = $quantity;
                    
                    // Update database
                    if (isset($_SESSION['cart_session_id'])) {
                        $stmt = $pdo->prepare("
                            UPDATE cart SET quantity = ? 
                            WHERE session_id = ? AND product_id = ?
                        ");
                        $stmt->execute([$quantity, $_SESSION['cart_session_id'], $product_id]);
                    }
                } else {
                    unset($_SESSION['cart'][$product_id]);
                    
                    // Remove from database
                    if (isset($_SESSION['cart_session_id'])) {
                        $stmt = $pdo->prepare("
                            DELETE FROM cart 
                            WHERE session_id = ? AND product_id = ?
                        ");
                        $stmt->execute([$_SESSION['cart_session_id'], $product_id]);
                    }
                }
            }
        }
        
        header("Location: shopping_cart.php");
        exit();
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred while updating your cart.");
    }
}

// Get cart contents
$cart_items = [];
$total = 0;

try {
    // Sync cart from database if session is new
    if (empty($_SESSION['cart']) && isset($_SESSION['cart_session_id'])) {
        $stmt = $pdo->prepare("SELECT product_id, quantity FROM cart WHERE session_id = ?");
        $stmt->execute([$_SESSION['cart_session_id']]);
        
        while ($row = $stmt->fetch()) {
            $_SESSION['cart'][$row['product_id']] = $row['quantity'];
        }
    }
    
    // Get product details
    if (!empty($_SESSION['cart'])) {
        $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
        $stmt = $pdo->prepare("
            SELECT p.*, c.category_name, c.category_color 
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            WHERE p.product_id IN ($placeholders)
        ");
        $stmt->execute(array_keys($_SESSION['cart']));
        $cart_items = $stmt->fetchAll();
        
        // Calculate total
        foreach ($cart_items as $item) {
            $quantity = isset($_SESSION['cart'][$item['product_id']]) 
    ? (is_array($_SESSION['cart'][$item['product_id']]) 
        ? (int)$_SESSION['cart'][$item['product_id']]['quantity'] 
        : (int)$_SESSION['cart'][$item['product_id']]) 
    : 0;
            $total += $item['price'] * $quantity;
        }
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred while loading your cart.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Clothing Store</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="cart.css">
    
</head>
<body>
    <div class="container">
        <header class="cart-header">
            <h1>Shopping Cart</h1>
            <a href="products.php" class="continue-shopping">← Continue Shopping</a>
        </header>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="products.php" class="btn">Browse Products</a>
            </div>
        <?php else: ?>
            <form action="shopping_cart.php" method="post">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                <span class="category" style="background-color: <?php echo htmlspecialchars($item['category_color']); ?>">
                                    <?php echo htmlspecialchars($item['category_name']); ?>
                                </span>
                                <p><?php echo htmlspecialchars($item['short_description']); ?></p>
                            </div>
                            <div class="item-price">
                                $<?php echo number_format($item['price'], 2); ?>
                            </div>
                            <div class="item-quantity">
                                <input type="number" name="quantities[<?php echo $item['product_id']; ?>]" 
                                       value="<?php echo isset($_SESSION['cart'][$item['product_id']]) ? $_SESSION['cart'][$item['product_id']] : 1; ?>" 
                                       min="1" max="<?php echo $item['quantity']; ?>">
                                <a href="shopping_cart.php?remove=<?php echo $item['product_id']; ?>" class="remove-btn">Remove</a>
                            </div>
                           <div class="item-subtotal">
    $<?php 
    $quantity = 0;
    if (isset($_SESSION['cart'][$item['product_id']])) {
        $quantity = is_array($_SESSION['cart'][$item['product_id']]) 
            ? (int)$_SESSION['cart'][$item['product_id']]['quantity'] 
            : (int)$_SESSION['cart'][$item['product_id']];
    } else {
        $quantity = 1;
    }
    echo number_format($item['price'] * $quantity, 2); 
    ?>
</div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-details">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Estimated Shipping</span>
                            <span>$5.00</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>$<?php echo number_format($total + 5, 2); ?></span>
                        </div>
                        <button type="submit" name="update_quantity" class="update-btn">Update Cart</button>
                        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>