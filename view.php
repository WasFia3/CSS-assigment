<?php
require_once 'database_connection.php';

// التحقق من وجود معرف المنتج في الرابط
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];

// جلب بيانات المنتج من قاعدة البيانات
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.category_name, c.category_color 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.product_id = :product_id
    ");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: products.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name']) ?> - Clothing Store</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="product-view.css">
</head>
<body>
    <div class="product-detail-container">
        <a href="products.php" class="back-btn">← Back to Products</a>
        
        <div class="product-detail-card">
            <div class="product-image-large">
                <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
            </div>
            
            <div class="product-info">
                <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>
                
                <div class="product-meta">
                    <span class="category-badge" style="background-color: <?= $product['category_color'] ?>">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </span>
                    <span>Product ID: #<?= $product['product_id'] ?></span>
                </div>
                
                <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                
                <div class="stock-status <?= 
                    $product['quantity'] == 0 ? 'out-of-stock' : 
                    ($product['quantity'] <= 5 ? 'low-stock' : 'in-stock') 
                ?>">
                    <?= 
                        $product['quantity'] == 0 ? 'Out of Stock' : 
                        ($product['quantity'] <= 5 ? 'Low Stock' : 'In Stock') 
                    ?> (<?= $product['quantity'] ?> available)
                </div>
                
                <div class="product-description">
                    <h3>Description</h3>
                    <p><?= htmlspecialchars($product['short_description']) ?></p>
                </div>
                
                <div class="action-buttons">
                    <?php if ($product['quantity'] > 0): ?>
                        <a href="add_to_cart.php?id=<?= $product['product_id'] ?>" class="add-to-cart-btn">Add to Cart</a>
                    <?php endif; ?>
                    <a href="products.php" class="view-btn">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>