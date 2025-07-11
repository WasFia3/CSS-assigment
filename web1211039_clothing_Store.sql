-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 15, 2025 at 05:21 PM
-- Server version: 8.0.42
-- PHP Version: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `web1211039_clothing_Store`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `category_color` varchar(20) NOT NULL DEFAULT '#6c757d'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_color`) VALUES
(1, 'Event Start', '#0d6efd'),
(2, 'Sweater', '#6f42c1'),
(3, 'Shirt', '#20c997'),
(4, 'Pants', '#fd7e14'),
(5, 'Accessories', '#ffc107');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `short_description` text,
  `image_path` varchar(255) DEFAULT 'default-product.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category_id`, `price`, `quantity`, `short_description`, `image_path`) VALUES
(101, 'Super Non-Iron Striped Sim-Fit', 1, 50.99, 3, 'Premium non-iron striped shirt with slim fit design', 'default-product.jpg'),
(111, 'Super Non-Iron Striped Sim-Fit', 1, 65.99, 6, 'Premium non-iron striped shirt with slim fit design', 'default-product.jpg'),
(135, 'Super Non-Iron Striped Sim-Fit', 1, 21.00, 0, 'Premium non-iron striped shirt with slim fit design', 'default-product.jpg'),
(201, 'Crow Hotel Long-Sleeve Sweater', 2, 110.99, 8, 'Warm long-sleeve sweater perfect for winter', 'default-product.jpg'),
(230, 'Crow Hotel Long-Sleeve Sweater', 2, 38.59, 7, 'Warm long-sleeve sweater perfect for winter', 'default-product.jpg'),
(255, 'Crow Hotel Long-Sleeve Sweater', 2, 28.89, 4, 'Warm long-sleeve sweater perfect for winter', 'default-product.jpg'),
(275, 'Row Hotel Long-Sleeve Sweater', 2, 53.92, 12, 'Warm long-sleeve sweater perfect for winter', 'default-product.jpg'),
(280, 'Row Hotel Long-Sleeve Sweater', 2, 39.59, 5, 'Warm long-sleeve sweater perfect for winter', 'default-product.jpg'),
(301, 'Super Non-Iron Striped Sim-Fit', 1, 140.00, 10, 'Premium non-iron striped shirt with slim fit design', 'default-product.jpg'),
(305, 'Classic Cotton T-Shirt', 3, 19.99, 15, 'Basic cotton t-shirt for everyday wear', 'default-product.jpg'),
(410, 'Slim Fit Chino Pants', 4, 45.50, 9, 'Comfortable slim-fit chino pants', 'default-product.jpg'),
(415, 'Super Non-Iron Striped Sim-Fit', 1, 39.99, 2, 'Premium non-iron striped shirt with slim fit design', 'default-product.jpg'),
(520, 'Wool Winter Scarf', 5, 29.99, 20, 'Warm wool scarf for cold weather', 'default-product.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=521;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
