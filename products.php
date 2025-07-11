<?php
require_once 'database_connection.php';
session_start();

function getCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories");
    return $stmt->fetchAll();
}

$products = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['product_name']) || isset($_GET['max_price']) || isset($_GET['category_id']))) {
    $product_name = $_GET['product_name'] ?? '';
    $max_price = $_GET['max_price'] ?? '';
    $category_id = $_GET['category_id'] ?? '';

    $sql = "SELECT p.*, c.category_name, c.category_color 
            FROM products p 
            JOIN categories c ON p.category_id = c.category_id 
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($product_name)) {
        $sql .= " AND p.product_name LIKE :product_name";
        $params[':product_name'] = "%$product_name%";
    }
    
    if (!empty($max_price) && is_numeric($max_price)) {
        $sql .= " AND p.price <= :max_price";
        $params[':max_price'] = $max_price;
    }
    
    if (!empty($category_id) && is_numeric($category_id)) {
        $sql .= " AND p.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    
    $sql .= " LIMIT 10"; // عرض 10 منتجات فقط لكل صفحة
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
}

$categories = getCategories($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clothing Store - Product Search</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <!-- filter and search -->
        <aside class="search-panel">
            <h2>Search Filters</h2>
            <form method="GET" action="products.php">
                <div class="form-group">
                    <label for="product_name">Product Name:</label>
                    <input type="text" id="product_name" name="product_name" 
                           placeholder="Enter product name" 
                           value="<?= htmlspecialchars($_GET['product_name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="max_price">Max Price:</label>
                    <input type="number" id="max_price" name="max_price" 
                           placeholder="Max price" min="0" step="0.01"
                           value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category:</label>
                    <select id="category_id" name="category_id">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>" 
                                <?= (isset($_GET['category_id']) && $_GET['category_id'] == $category['category_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="filter-btn">Filter</button>
                <a href="products.php" class="clear-btn">Clear Filters</a>
            </form>
        </aside>
        
        <!-- view products -->
        <main class="product-grid">
            <?php if (!empty($products)): ?>
                <div class="products-container">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                            </div>
                            
                            <div class="product-details">
                                <h3 class="product-id">Product #<?= $product['product_id'] ?></h3>
                                
                                <div class="product-name-container">
                                    <a href="view.php?id=<?= $product['product_id'] ?>" 
                                       class="product-name" 
                                       tabindex="0">
                                        <?= htmlspecialchars($product['product_name']) ?>
                                    </a>
                                    <div class="tooltip">
                                        <h2>Quantity: <span class="<?= ($product['quantity'] <= 5) ? 'low-stock' : 'in-stock' ?>">
                                            <?= $product['quantity'] ?>
                                        </span></h2>
                                        <p><?= htmlspecialchars($product['short_description']) ?></p>
                                    </div>
                                </div>
                                
                                <span class="category-badge" style="background-color: <?= $product['category_color'] ?>">
                                    <?= htmlspecialchars($product['category_name']) ?>
                                </span>
                                
                                <div class="product-price">Price: $<?= number_format($product['price'], 2) ?></div>
                                
                                <nav class="product-actions">
                                    <a href="view.php?id=<?= $product['product_id'] ?>" class="view-btn">View</a>
                                   <form action="add_to_cart.php" method="POST" style="display:inline;">
    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
    <input type="hidden" name="price" value="<?= $product['price'] ?>">
    <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>
</form>


                                </nav>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <nav class="pagination">
                    <a href="#" class="page-btn">Previous</a>
                    <a href="#" class="page-btn">Next</a>
                </nav>
            <?php else: ?>
                <p class="no-results">No products found. Try adjusting your search filters.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>