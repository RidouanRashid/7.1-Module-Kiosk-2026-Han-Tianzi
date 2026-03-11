-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Gegenereerd op: 09 mrt 2026 om 09:38
-- Serverversie: 10.4.32-MariaDB
-- PHP-versie: 8.2.12

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
-- Tabelstructuur voor tabel `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `categories`
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
-- Tabelstructuur voor tabel `images`
--

CREATE TABLE `images` (
  `image_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `filename_transparent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `images`
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
-- Tabelstructuur voor tabel `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_status_id` int(11) NOT NULL,
  `pickup_number` tinyint(3) UNSIGNED NOT NULL,
  `price_total` decimal(10,2) NOT NULL,
  `DATETIME` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `orders`
--

INSERT INTO `orders` (`order_id`, `order_status_id`, `pickup_number`, `price_total`, `DATETIME`) VALUES
(1, 1, 1, 10.50, '2026-03-04 14:05:34'),
(2, 2, 2, 44.00, '2026-03-04 14:06:10'),
(3, 2, 3, 1.00, '2026-03-06 11:09:52'),
(4, 2, 4, 1.00, '2026-03-06 11:10:56'),
(5, 2, 5, 38.00, '2026-03-06 11:13:24'),
(6, 2, 6, 54.00, '2026-03-06 11:26:39'),
(7, 2, 7, 17.00, '2026-03-06 13:05:59'),
(8, 2, 8, 8.50, '2026-03-06 13:39:09'),
(9, 2, 9, 44.00, '2026-03-09 00:33:26'),
(10, 2, 10, 8.50, '2026-03-09 00:48:38'),
(11, 1, 11, 8.50, '2026-03-09 00:53:23'),
(12, 1, 12, 4.00, '2026-03-09 00:56:49'),
(13, 2, 13, 12.00, '2026-03-09 01:01:48'),
(14, 2, 14, 4.00, '2026-03-09 01:04:44'),
(15, 2, 15, 4.50, '2026-03-09 01:06:08'),
(16, 2, 16, 6.00, '2026-03-09 01:08:06'),
(17, 1, 17, 8.50, '2026-03-09 01:11:01'),
(18, 2, 18, 34.00, '2026-03-09 01:11:14'),
(19, 1, 19, 4.00, '2026-03-09 01:16:32'),
(20, 2, 20, 12.30, '2026-03-09 08:55:23'),
(21, 2, 21, 10.50, '2026-03-09 08:56:41'),
(22, 1, 22, 4.00, '2026-03-09 08:58:37'),
(23, 2, 23, 20.00, '2026-03-09 09:07:35');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `order_product`
--

CREATE TABLE `order_product` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `order_product`
--

INSERT INTO `order_product` (`order_id`, `product_id`, `price`) VALUES
(1, 5, 10.50),
(2, 8, 44.00),
(3, 19, 1.00),
(4, 19, 1.00),
(5, 6, 38.00),
(6, 10, 54.00),
(7, 9, 17.00),
(8, 9, 8.50),
(9, 8, 44.00),
(10, 9, 8.50),
(11, 9, 8.50),
(12, 15, 4.00),
(13, 15, 12.00),
(14, 15, 4.00),
(15, 12, 4.50),
(16, 25, 6.00),
(17, 9, 8.50),
(18, 9, 34.00),
(19, 15, 4.00),
(20, 13, 4.50),
(20, 15, 4.00),
(20, 24, 3.80),
(21, 9, 8.50),
(21, 20, 2.00),
(22, 15, 4.00),
(23, 9, 17.00),
(23, 22, 3.00);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `order_status`
--

CREATE TABLE `order_status` (
  `order_status_id` int(11) NOT NULL,
  `description` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `order_status`
--

INSERT INTO `order_status` (`order_status_id`, `description`) VALUES
(1, 'Pending'),
(2, 'Paid'),
(3, 'Preparing'),
(4, 'Ready'),
(5, 'Completed');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `products`
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
  `V-VG` varchar(20) GENERATED ALWAYS AS (case when `NAME` like '%(VG)%' then 'Vegetarian' when `NAME` like '%(V)%' then 'Vegan' else NULL end) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `image_id`, `NAME`, `description`, `price`, `kcal`, `available`) VALUES
(1, 1, 1, 'Morning Boost Açaí Bowl', 'A chilled blend of açaí and banana topped with crunchy granola, chia seeds, and coconut.', 7.50, 320, 1),
(2, 1, 2, 'The Garden Breakfast Wrap', 'Whole-grain wrap with fluffy scrambled eggs, baby spinach, and a light yogurt-herb sauce.', 6.50, 280, 1),
(3, 1, 3, 'Peanut Butter & Cacao Toast', 'Sourdough toast with 100% natural peanut butter, banana, and a sprinkle of cacao nibs.', 5.00, 240, 1),
(4, 1, 4, 'Overnight Oats: Apple Pie', 'Apple, cinnamon, walnuts, almond milk', 5.50, 290, 1),
(5, 2, 5, 'Tofu Power Tahini Bowl', 'Tri-color quinoa, maple-glazed tofu, roasted sweet potatoes, and kale with tahini dressing.', 10.50, 480, 1),
(6, 2, 6, 'The Supergreen Harvest', 'Massaged kale, edamame, avocado, cucumber, and toasted pumpkin seeds with lemon-olive oil.', 9.50, 310, 1),
(7, 2, 7, 'Mediterranean Falafel Bowl', 'Baked falafel, hummus, pickled red onions, cherry tomatoes, and cucumber on a bed of greens.', 10.00, 440, 1),
(8, 2, 8, 'Warm Teriyaki Tempeh Bowl', 'Steamed brown rice, seared tempeh, broccoli, and shredded carrots with a ginger-soy glaze.', 11.00, 500, 1),
(9, 3, 9, 'Zesty Chickpea Hummus Wrap', 'Spiced chickpeas, shredded carrots, crisp lettuce, and signature hummus in a whole-wheat wrap.', 8.50, 410, 1),
(10, 3, 10, 'Avocado & Halloumi Toastie', 'Grilled halloumi cheese, smashed avocado, and chili flakes on thick-cut multi-grain bread.', 9.00, 460, 1),
(11, 3, 11, 'Smoky BBQ Jackfruit Slider', 'Pulled jackfruit in BBQ sauce with a crunchy purple slaw on a vegan brioche bun.', 7.50, 350, 1),
(12, 4, 12, 'Sweet Potato Wedges', 'Smoked paprika seasoning', 4.50, 260, 1),
(13, 4, 13, 'Zucchini Fries', 'Crispy breaded zucchini sticks. Best with Greek yogurt ranch.', 4.50, 190, 1),
(14, 4, 14, 'Baked Falafel Bites', '5 pieces baked falafel', 5.00, 230, 1),
(15, 4, 15, 'Mini Veggie Platter & Hummus', 'Fresh celery, carrots, and cucumber served with hummus.', 4.00, 160, 1),
(16, 5, 16, 'Classic Hummus', 'Creamy chickpea hummus with tahini and olive oil.', 1.00, 120, 1),
(17, 5, 17, 'Avocado Lime Crema', 'Smooth avocado dip with lime and herbs.', 1.00, 110, 1),
(18, 5, 18, 'Greek Yogurt Ranch', 'Light Greek yogurt ranch with herbs.', 1.00, 90, 1),
(19, 5, 19, 'Spicy Sriracha Mayo', 'Vegan spicy sriracha mayonnaise.', 1.00, 180, 1),
(20, 5, 20, 'Peanut Satay Sauce', 'Rich peanut satay dipping sauce.', 1.00, 200, 1),
(21, 6, 21, 'Green Glow Smoothie', 'Spinach, pineapple, cucumber, and coconut water.', 3.50, 120, 1),
(22, 6, 22, 'Iced Matcha Latte', 'Lightly sweetened matcha green tea with almond milk.', 3.00, 90, 1),
(23, 6, 23, 'Fruit-Infused Water', 'Freshly infused water with lemon-mint, strawberry-basil, or cucumber-lime.', 1.50, 0, 1),
(24, 6, 24, 'Berry Blast Smoothie', 'Blend of strawberries, blueberries, and raspberries with almond milk.', 3.80, 140, 1),
(25, 6, 25, 'Citrus Cooler', 'Orange juice with sparkling water and lime.', 3.00, 90, 1);

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexen voor tabel `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`);

--
-- Indexen voor tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `order_status_id` (`order_status_id`);

--
-- Indexen voor tabel `order_product`
--
ALTER TABLE `order_product`
  ADD PRIMARY KEY (`order_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexen voor tabel `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`order_status_id`);

--
-- Indexen voor tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `image_id` (`image_id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT voor een tabel `images`
--
ALTER TABLE `images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT voor een tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT voor een tabel `order_status`
--
ALTER TABLE `order_status`
  MODIFY `order_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT voor een tabel `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`order_status_id`) REFERENCES `order_status` (`order_status_id`);

--
-- Beperkingen voor tabel `order_product`
--
ALTER TABLE `order_product`
  ADD CONSTRAINT `order_product_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_product_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Beperkingen voor tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `images` (`image_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
