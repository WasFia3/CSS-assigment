<?php
session_start();
require_once 'database_connection.php';

if (!isset($pdo)) {
    die("wrong connection");
}

if (isset($_POST['add_to_cart'])) {
    if (!isset($_POST['product_id']) || !isset($_POST['price'])) {
        $_SESSION['error'] = "missing data bro!";
        header("Location: products.php");
        exit();
    }

    $product_id = (int)$_POST['product_id'];
    $price = (float)$_POST['price'];

   
    if (!isset($_SESSION['cart_session_id'])) {
        $_SESSION['cart_session_id'] = bin2hex(random_bytes(16));
    }

    try {
        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            $_SESSION['error'] = "prodcut doesn't exist !";
            header("Location: products.php");
            exit();
        }

        if ($product['quantity'] <= 0) {
            $_SESSION['error'] = "doesn't exist in store!";
            header("Location: products.php");
            exit();
        }

        // update session's cart
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;

       
        $stmt = $pdo->prepare("
            INSERT INTO cart (session_id, product_id, quantity)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE quantity = quantity + 1
        ");
        $stmt->execute([$_SESSION['cart_session_id'], $product_id]);

        $_SESSION['success'] = "product added :) !";
        header("Location: shopping_cart.php");
        exit();

    } catch (PDOException $e) {
        error_log("DB ERROR: " . $e->getMessage());
        $_SESSION['error'] = "adding product failed.";
        header("Location: products.php");
        exit();
    }
} else {
    header("Location: products.php");
    exit();
}
