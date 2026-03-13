-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2026 at 01:16 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `happy-herbivore`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `NAME`, `description`) VALUES
(1, 'Breakfast', 'Morning bowls, oats, and light breakfast'),
(2, 'Lunch & Dinner', 'Warm bowls and hearty meals'),
(3, 'Handhelds', 'Wraps and sandwiches'),
(4, 'Sides & Small Plates', 'Small bites and sides'),
(5, 'Signature Dips', 'House-made dips'),
(6, 'Drinks', 'Smoothies and beverages');

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `image_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `filename_transparent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`image_id`, `filename`, `description`, `filename_transparent`) VALUES
(1, 'breakfast1.png', 'Morning Boost Açaí Bowl', 'breakfast1transparent.png'),
(2, 'breakfast2.png', 'Garden Breakfast Wrap', 'breakfast2transparent.png'),
(3, 'breakfast3.png', 'Peanut Butter Toast', 'breakfast3transparent.png'),
(4, 'breakfast4.png', 'Overnight Oats', 'breakfast4transparent.png'),
(5, 'lunch1.png', 'Tofu Power Tahini Bowl', 'lunch1transparent.png'),
(6, 'lunch2.png', 'Supergreen Harvest', 'lunch2transparent.png'),
(7, 'lunch3.png', 'Falafel Bowl', 'lunch3transparent.png'),
(8, 'lunch4.png', 'Teriyaki Tempeh Bowl', 'lunch4transparent.png'),
(9, 'handheld1.png', 'Chickpea Hummus Wrap', 'handheld1transparent.png'),
(10, 'handheld2.png', 'Halloumi Toastie', 'handheld2transparent.png'),
(11, 'handheld3.png', 'BBQ Jackfruit Slider', 'handheld3transparent.png'),
(12, 'side1.png', 'Sweet Potato Wedges', 'side1transparent.png'),
(13, 'side2.png', 'Zucchini Fries', 'side2transparent.png'),
(14, 'side3.png', 'Falafel Bites', 'side3transparent.png'),
(15, 'side4.png', 'Veggie Platter', 'side4transparent.png'),
(16, 'dip1.png', 'Classic Hummus', 'dip1transparent.png'),
(17, 'dip2.png', 'Avocado Lime Crema', 'dip2transparent.png'),
(18, 'dip3.png', 'Greek Yogurt Ranch', 'dip3transparent.png'),
(19, 'dip4.png', 'Sriracha Mayo', 'dip4transparent.png'),
(20, 'dip5.png', 'Peanut Satay', 'dip5transparent.png'),
(21, 'drink1.png', 'Green Glow Smoothie', 'drink1transparent.png'),
(22, 'drink2.png', 'Iced Matcha Latte', 'drink2transparent.png'),
(23, 'drink3.png', 'Fruit Infused Water', 'drink3transparent.png'),
(24, 'drink4.png', 'Berry Blast Smoothie', 'drink4transparent.png'),
(25, 'drink5.png', 'Citrus Cooler', 'drink5transparent.png');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_status_id` int(11) NOT NULL,
  `pickup_number` tinyint(3) UNSIGNED NOT NULL,
  `price_total` decimal(10,2) NOT NULL,
  `DATETIME` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_status_id`, `pickup_number`, `price_total`, `DATETIME`) VALUES
(1, 2, 1, 19.00, '2026-03-13 01:15:12');

-- --------------------------------------------------------

--
-- Table structure for table `order_product`
--

CREATE TABLE `order_product` (
  `order_product_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_ready` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_product`
--

INSERT INTO `order_product` (`order_product_id`, `order_id`, `product_id`, `price`, `is_ready`) VALUES
(1, 1, 6, 9.50, 1),
(2, 1, 25, 3.00, 1),
(3, 1, 4, 0.00, 1),
(4, 1, 17, 0.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_status`
--

CREATE TABLE `order_status` (
  `order_status_id` int(11) NOT NULL,
  `description` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status`
--

INSERT INTO `order_status` (`order_status_id`, `description`) VALUES
(1, 'Pending'),
(2, 'Paid'),
(3, 'Preparing'),
(4, 'Ready'),
(5, 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `image_id` int(11) DEFAULT NULL,
  `NAME` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `kcal` int(11) DEFAULT NULL,
  `available` tinyint(1) NOT NULL DEFAULT 1,
  `V-VG` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `image_id`, `NAME`, `description`, `price`, `kcal`, `available`, `V-VG`) VALUES
(1, 1, 1, 'Morning Boost Açaí Bowl', 'A chilled blend of açaí and banana topped with crunchy granola, chia seeds, and coconut.', 7.50, 320, 1, 'Vegan'),
(2, 1, 2, 'The Garden Breakfast Wrap', 'Whole-grain wrap with fluffy scrambled eggs, baby spinach, and a light yogurt-herb sauce.', 6.50, 280, 1, 'Vegetarian'),
(3, 1, 3, 'Peanut Butter & Cacao Toast', 'Sourdough toast with 100% natural peanut butter, banana, and a sprinkle of cacao nibs.', 5.00, 240, 1, 'Vegan'),
(4, 1, 4, 'Overnight Oats: Apple Pie', 'Apple, cinnamon, walnuts, almond milk', 5.50, 290, 1, 'Vegan'),
(5, 2, 5, 'Tofu Power Tahini Bowl', 'Tri-color quinoa, maple-glazed tofu, roasted sweet potatoes, and kale with tahini dressing.', 10.50, 480, 1, 'Vegan'),
(6, 2, 6, 'The Supergreen Harvest', 'Massaged kale, edamame, avocado, cucumber, and toasted pumpkin seeds with lemon-olive oil.', 9.50, 310, 1, 'Vegan'),
(7, 2, 7, 'Mediterranean Falafel Bowl', 'Baked falafel, hummus, pickled red onions, cherry tomatoes, and cucumber on a bed of greens.', 10.00, 440, 1, 'Vegan'),
(8, 2, 8, 'Warm Teriyaki Tempeh Bowl', 'Steamed brown rice, seared tempeh, broccoli, and shredded carrots with a ginger-soy glaze.', 11.00, 500, 1, 'Vegan'),
(9, 3, 9, 'Zesty Chickpea Hummus Wrap', 'Spiced chickpeas, shredded carrots, crisp lettuce, and signature hummus in a whole-wheat wrap.', 8.50, 410, 1, 'Vegan'),
(10, 3, 10, 'Avocado & Halloumi Toastie', 'Grilled halloumi cheese, smashed avocado, and chili flakes on thick-cut multi-grain bread.', 9.00, 460, 1, 'Vegetarian'),
(11, 3, 11, 'Smoky BBQ Jackfruit Slider', 'Pulled jackfruit in BBQ sauce with a crunchy purple slaw on a vegan brioche bun.', 7.50, 350, 1, 'Vegan'),
(12, 4, 12, 'Sweet Potato Wedges', 'Smoked paprika seasoning', 4.50, 260, 1, 'Vegan'),
(13, 4, 13, 'Zucchini Fries', 'Crispy breaded zucchini sticks. Best with Greek yogurt ranch.', 4.50, 190, 1, 'Vegetarian'),
(14, 4, 14, 'Baked Falafel Bites', '5 pieces baked falafel', 5.00, 230, 1, 'Vegan'),
(15, 4, 15, 'Mini Veggie Platter & Hummus', 'Fresh celery, carrots, and cucumber served with hummus.', 4.00, 160, 1, 'Vegan'),
(16, 5, 16, 'Classic Hummus', 'Creamy chickpea hummus with tahini and olive oil.', 1.00, 120, 1, 'Vegan'),
(17, 5, 17, 'Avocado Lime Crema', 'Smooth avocado dip with lime and herbs.', 1.00, 110, 1, 'Vegan'),
(18, 5, 18, 'Greek Yogurt Ranch', 'Light Greek yogurt ranch with herbs.', 1.00, 90, 1, 'Vegetarian'),
(19, 5, 19, 'Spicy Sriracha Mayo', 'Vegan spicy sriracha mayonnaise.', 1.00, 180, 1, 'Vegan'),
(20, 5, 20, 'Peanut Satay Sauce', 'Rich peanut satay dipping sauce.', 1.00, 200, 1, 'Vegan'),
(21, 6, 21, 'Green Glow Smoothie', 'Spinach, pineapple, cucumber, and coconut water.', 3.50, 120, 1, 'Vegan'),
(22, 6, 22, 'Iced Matcha Latte', 'Lightly sweetened matcha green tea with almond milk.', 3.00, 90, 1, 'Vegan'),
(23, 6, 23, 'Fruit-Infused Water', 'Freshly infused water with lemon-mint, strawberry-basil, or cucumber-lime.', 1.50, 0, 1, 'Vegan'),
(24, 6, 24, 'Berry Blast Smoothie', 'Blend of strawberries, blueberries, and raspberries with almond milk.', 3.80, 140, 1, 'Vegan'),
(25, 6, 25, 'Citrus Cooler', 'Orange juice with sparkling water and lime.', 3.00, 90, 1, 'Vegan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `order_status_id` (`order_status_id`);

--
-- Indexes for table `order_product`
--
ALTER TABLE `order_product`
  ADD PRIMARY KEY (`order_product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`order_status_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `image_id` (`image_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_product`
--
ALTER TABLE `order_product`
  MODIFY `order_product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_status`
--
ALTER TABLE `order_status`
  MODIFY `order_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`order_status_id`) REFERENCES `order_status` (`order_status_id`);

--
-- Constraints for table `order_product`
--
ALTER TABLE `order_product`
  ADD CONSTRAINT `order_product_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_product_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `images` (`image_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
