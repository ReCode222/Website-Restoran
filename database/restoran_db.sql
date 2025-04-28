-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 23, 2025 at 01:51 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restoran_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Makanan'),
(2, 'Minuman'),
(3, 'Snack');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `category_id`, `name`, `price`, `description`, `image`) VALUES
(1, 1, 'Nasi Goreng Spesial', '25000.00', 'Nasi goreng dengan telur, ayam, dan sayuran', 'nasi-goreng.jpg'),
(2, 1, 'Mie Goreng', '23000.00', 'Mie goreng dengan telur dan sayuran', 'mie-goreng.jpg'),
(3, 1, 'Ayam Bakar', '30000.00', 'Ayam bakar bumbu special dengan nasi', 'ayam-bakar.jpg'),
(4, 1, 'Sate Ayam', '28000.00', 'Sate ayam dengan bumbu kacang', 'sate-ayam.jpg'),
(5, 2, 'Es Teh Manis', '5000.00', 'Teh manis dengan es', 'es-teh.jpg'),
(6, 2, 'Es Jeruk', '7000.00', 'Jeruk peras segar dengan es', 'es-jeruk.jpg'),
(7, 2, 'Jus Alpukat', '12000.00', 'Jus alpukat segar dengan susu', 'jus-alpukat.jpg'),
(8, 2, 'Lemon Tea', '8000.00', 'Teh dengan perasan lemon segar', 'lemon-tea.jpg'),
(9, 3, 'Kentang Goreng', '15000.00', 'Kentang goreng crispy', 'kentang-goreng.jpg'),
(10, 3, 'Pisang Goreng', '10000.00', 'Pisang goreng crispy', 'pisang-goreng.jpg'),
(11, 3, 'Dimsum', '18000.00', 'Dimsum ayam/udang (4 pcs)', 'dimsum.jpg'),
(12, 3, 'Roti Bakar', '13000.00', 'Roti bakar dengan berbagai topping', 'roti-bakar.jpg'),
(16, 1, 'Apa Aja', '20000.00', 'Lorem Ipsum Dolor Sit Amet', 'image-removebg-preview (8).png');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `order_number` int NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `total_price`, `status`, `created_at`) VALUES
(5, 1, '80000.00', 'completed', '2025-01-24 02:09:23'),
(6, 2, '408000.00', 'processing', '2025-01-24 02:22:08'),
(7, 1, '515000.00', 'completed', '2025-01-30 13:48:34'),
(8, 1, '166000.00', 'processing', '2025-03-15 00:58:48'),
(10, 1, '76000.00', 'pending', '2025-04-16 01:30:40'),
(11, 2, '61000.00', 'pending', '2025-04-16 01:38:01'),
(12, 1, '269000.00', 'pending', '2025-04-22 05:53:32'),
(13, 2, '138000.00', 'pending', '2025-04-22 06:00:46'),
(14, 3, '144000.00', 'completed', '2025-04-22 06:10:28'),
(15, 1, '141000.00', 'pending', '2025-04-23 01:17:08'),
(16, 2, '116000.00', 'pending', '2025-04-23 01:22:40'),
(17, 3, '128000.00', 'pending', '2025-04-23 01:29:38'),
(18, 4, '20000.00', 'completed', '2025-04-23 01:32:08'),
(19, 5, '23000.00', 'completed', '2025-04-23 01:42:12');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_id`, `quantity`, `price`) VALUES
(6, 5, 10, 2, '10000.00'),
(7, 5, 5, 2, '5000.00'),
(8, 5, 1, 2, '25000.00'),
(9, 6, 3, 9, '30000.00'),
(10, 6, 2, 6, '23000.00'),
(11, 7, 3, 3, '30000.00'),
(12, 7, 2, 2, '23000.00'),
(13, 7, 4, 8, '28000.00'),
(14, 7, 1, 3, '25000.00'),
(15, 7, 9, 4, '15000.00'),
(16, 7, 5, 4, '5000.00'),
(17, 8, 2, 2, '23000.00'),
(18, 8, 1, 3, '25000.00'),
(19, 8, 7, 1, '12000.00'),
(20, 8, 5, 1, '5000.00'),
(21, 8, 12, 1, '13000.00'),
(22, 8, 9, 1, '15000.00'),
(23, 10, 1, 1, '25000.00'),
(24, 10, 2, 1, '23000.00'),
(25, 10, 4, 1, '28000.00'),
(26, 11, 2, 1, '23000.00'),
(27, 11, 7, 1, '12000.00'),
(28, 11, 12, 2, '13000.00'),
(29, 12, 2, 1, '23000.00'),
(30, 12, 1, 1, '25000.00'),
(31, 12, 8, 1, '8000.00'),
(32, 12, 7, 3, '12000.00'),
(33, 12, 5, 2, '5000.00'),
(34, 12, 12, 3, '13000.00'),
(35, 12, 10, 5, '10000.00'),
(36, 12, 9, 4, '15000.00'),
(37, 12, 11, 1, '18000.00'),
(38, 13, 2, 1, '23000.00'),
(39, 13, 1, 1, '25000.00'),
(40, 13, 4, 1, '28000.00'),
(41, 13, 7, 1, '12000.00'),
(42, 13, 5, 1, '5000.00'),
(43, 13, 6, 1, '7000.00'),
(44, 13, 12, 1, '13000.00'),
(45, 13, 10, 1, '10000.00'),
(46, 13, 9, 1, '15000.00'),
(47, 14, 2, 1, '23000.00'),
(48, 14, 1, 1, '25000.00'),
(49, 14, 4, 1, '28000.00'),
(50, 14, 7, 1, '12000.00'),
(51, 14, 5, 1, '5000.00'),
(52, 14, 8, 1, '8000.00'),
(53, 14, 10, 1, '10000.00'),
(54, 14, 9, 1, '15000.00'),
(55, 14, 11, 1, '18000.00'),
(56, 15, 2, 1, '23000.00'),
(57, 15, 1, 1, '25000.00'),
(58, 15, 4, 1, '28000.00'),
(59, 15, 12, 1, '13000.00'),
(60, 15, 10, 2, '10000.00'),
(61, 15, 9, 1, '15000.00'),
(62, 15, 7, 1, '12000.00'),
(63, 15, 5, 1, '5000.00'),
(64, 16, 2, 2, '23000.00'),
(65, 16, 1, 2, '25000.00'),
(66, 16, 8, 1, '8000.00'),
(67, 16, 7, 1, '12000.00'),
(68, 17, 2, 1, '23000.00'),
(69, 17, 1, 2, '25000.00'),
(70, 17, 7, 2, '12000.00'),
(71, 17, 8, 1, '8000.00'),
(72, 17, 12, 1, '13000.00'),
(73, 17, 10, 1, '10000.00'),
(74, 18, 16, 1, '20000.00'),
(75, 19, 2, 1, '23000.00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir','pelayan') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', 'admin#1234', 'admin'),
(2, 'kasir', 'kasir123', 'kasir'),
(4, 'pelayan', 'pelayan123', 'pelayan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
