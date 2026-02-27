-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Gegenereerd op: 27 feb 2026 om 12:05
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
(1, 1, 1, 'Morning Boost Açaí Bowl', 'Açaí, banana, granola, chia, coconut', 7.50, 320, 1),
(2, 1, 2, 'The Garden Breakfast Wrap', 'Eggs, spinach, yogurt-herb sauce', 6.50, 280, 1),
(3, 1, 3, 'Peanut Butter & Cacao Toast', 'Peanut butter, banana, cacao nibs', 5.00, 240, 1),
(4, 1, 4, 'Overnight Oats: Apple Pie', 'Apple, cinnamon, walnuts, almond milk', 5.50, 290, 1),
(5, 2, 5, 'Tofu Power Tahini Bowl', 'Quinoa, tofu, sweet potato, kale', 10.50, 480, 1),
(6, 2, 6, 'The Supergreen Harvest', 'Kale, edamame, avocado, seeds', 9.50, 310, 1),
(7, 2, 7, 'Mediterranean Falafel Bowl', 'Falafel, hummus, veggies', 10.00, 440, 1),
(8, 2, 8, 'Warm Teriyaki Tempeh Bowl', 'Rice, tempeh, broccoli, glaze', 11.00, 500, 1),
(9, 3, 9, 'Zesty Chickpea Hummus Wrap', 'Chickpeas, carrots, hummus', 8.50, 410, 1),
(10, 3, 10, 'Avocado & Halloumi Toastie', 'Halloumi, avocado, chili', 9.00, 460, 1),
(11, 3, 11, 'Smoky BBQ Jackfruit Slider', 'BBQ jackfruit, slaw', 7.50, 350, 1),
(12, 4, 12, 'Sweet Potato Wedges', 'Smoked paprika seasoning', 4.50, 260, 1),
(13, 4, 13, 'Zucchini Fries', 'Breaded zucchini sticks', 4.50, 190, 1),
(14, 4, 14, 'Baked Falafel Bites', '5 pieces baked falafel', 5.00, 230, 1),
(15, 4, 15, 'Mini Veggie Platter & Hummus', 'Celery, carrots, cucumber', 4.00, 160, 1),
(16, 5, 16, 'Classic Hummus', 'Creamy chickpea dip', 1.00, 120, 1),
(17, 5, 17, 'Avocado Lime Crema', 'Avocado with lime', 1.00, 110, 1),
(18, 5, 18, 'Greek Yogurt Ranch', 'Herbed yogurt dip', 1.00, 90, 1),
(19, 5, 19, 'Spicy Sriracha Mayo', 'Chili mayo sauce', 1.00, 180, 1),
(20, 5, 20, 'Peanut Satay Sauce', 'Rich peanut sauce', 1.00, 200, 1),
(21, 6, 21, 'Green Glow Smoothie', 'Spinach, pineapple, coconut water', 3.50, 120, 1),
(22, 6, 22, 'Iced Matcha Latte', 'Matcha with almond milk', 3.00, 90, 1),
(23, 6, 23, 'Fruit-Infused Water', 'Fresh fruit infused water', 1.50, 0, 1),
(24, 6, 24, 'Berry Blast Smoothie', 'Mixed berries, almond milk', 3.80, 140, 1),
(25, 6, 25, 'Citrus Cooler', 'Orange juice & sparkling water', 3.00, 90, 1);

--
-- Indexen voor geëxporteerde tabellen
--

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
-- AUTO_INCREMENT voor een tabel `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Beperkingen voor geëxporteerde tabellen
--

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
