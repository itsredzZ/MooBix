-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2025 at 06:41 AM
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
-- Database: `db_moobix_1`
--

-- --------------------------------------------------------

--
-- Table structure for table `booked_seats`
--

CREATE TABLE `booked_seats` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `show_date` date NOT NULL,
  `show_time` varchar(10) NOT NULL,
  `seat_number` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booked_seats`
--

INSERT INTO `booked_seats` (`id`, `transaction_id`, `movie_id`, `show_date`, `show_time`, `seat_number`) VALUES
(22, 20, 7, '2025-12-17', '19:30', 'A1'),
(23, 20, 7, '2025-12-17', '19:30', 'A2'),
(24, 20, 7, '2025-12-17', '19:30', 'B1'),
(25, 20, 7, '2025-12-17', '19:30', 'B2'),
(26, 21, 6, '2025-12-17', '19:30', 'A1'),
(27, 22, 1, '2025-12-20', '14:00', 'B3'),
(28, 22, 1, '2025-12-20', '14:00', 'B4'),
(29, 23, 5, '2025-12-18', '14:00', 'A3'),
(30, 23, 5, '2025-12-18', '14:00', 'A4'),
(31, 24, 7, '2025-12-18', '14:00', 'B1'),
(32, 24, 7, '2025-12-18', '14:00', 'B3'),
(33, 24, 7, '2025-12-18', '14:00', 'B4'),
(34, 24, 7, '2025-12-18', '14:00', 'C2'),
(35, 25, 8, '2025-12-18', '14:00', 'C2'),
(36, 25, 8, '2025-12-18', '14:00', 'C3'),
(37, 25, 8, '2025-12-18', '14:00', 'D2'),
(38, 26, 8, '2025-12-18', '14:00', 'A2'),
(39, 26, 8, '2025-12-18', '14:00', 'A3'),
(40, 27, 8, '2025-12-19', '19:30', 'D3'),
(41, 27, 8, '2025-12-19', '19:30', 'D5'),
(42, 28, 6, '2025-12-19', '10:00', 'D1'),
(43, 28, 6, '2025-12-19', '10:00', 'D2'),
(44, 28, 6, '2025-12-19', '10:00', 'D3'),
(45, 28, 6, '2025-12-19', '10:00', 'D4'),
(46, 29, 7, '2025-12-20', '10:00', 'D2'),
(47, 29, 7, '2025-12-20', '10:00', 'D4'),
(48, 30, 7, '2025-12-20', '10:00', 'D5'),
(49, 30, 7, '2025-12-20', '10:00', 'D6'),
(50, 31, 14, '2025-12-20', '10:00', 'A1'),
(51, 32, 14, '2025-12-20', '10:00', 'A2'),
(61, 41, 13, '2025-12-20', '14:00', 'A1');

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `poster` varchar(255) NOT NULL,
  `genre` varchar(50) NOT NULL,
  `synopsis` text DEFAULT NULL,
  `duration` varchar(20) DEFAULT '2h 0m',
  `rating` decimal(3,2) DEFAULT 4.50,
  `status` enum('showing','coming_soon','archived') DEFAULT 'showing',
  `price` int(11) NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `title`, `poster`, `genre`, `synopsis`, `duration`, `rating`, `status`, `price`, `is_featured`) VALUES
(1, 'Spider-Man: Across the Spider-Verse', 'spiderman.jpg', 'Action/Sci-fi', 'After reuniting with Gwen Stacy, Brooklyn’s full-time, friendly neighborhood Spider-Man is catapulted across the Multiverse, where he encounters the Spider Society, a team of Spider-People charged with protecting the Multiverse’s very existence. But when the heroes clash on how to handle a new threat, Miles finds himself pitted against the other Spiders and must set out on his own to save those he loves most.', '2h 20m', 4.15, 'showing', 50000, 0),
(2, 'Doraemon: Stand by Me', 'doraemon.jpg', 'Family/Adventure', 'Sewashi and Doraemon find themselves way back in time and meet Nobita. It is up to Doraemon to take care of Nobita or else he will not return to the present.', '1h 34m', 3.65, 'showing', 50000, 0),
(3, 'Mission Impossible: Fallout', 'mifallout.jpg', 'Action/Adventure', 'When an IMF mission ends badly, the world is faced with dire consequences. As Ethan Hunt takes it upon himself to fulfill his original briefing, the CIA begin to question his loyalty and his motives. The IMF team find themselves in a race against time, hunted by assassins while trying to prevent a global catastrophe.', '2h 15m', 4.50, 'showing', 50000, 0),
(4, 'Detective Conan Movie: One-Eyed Flashback', 'dconeeyed.jpg', 'Animation ', 'As police inspector Yamato Kansuke pursues a certain man in the snowy mountains of Nagano, a shadow suddenly appears in his field of vision. While he\'s distracted, a rifle bullet fired by someone grazes his left eye and causes an avalanche accompanied by a roar. Ten months later, Kansuke, having miraculously survived the avalanche, receives a report that a researcher at the Nobeyama National Astronomical Observatory has been attacked.', '1h 49m', 3.75, 'showing', 50000, 0),
(5, 'The Shadow\'s Edge', 'TheShadowsEdgePoster.jpg', 'Action/Crime', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Film seru yang wajib ditonton tahun ini.', '2h 15m', 4.50, 'showing', 50000, 0),
(6, 'Avengers: Endgame', 'endgame.jpg', 'Action/Sci-fi', 'After the devastating events of Avengers: Infinity War, the universe is in ruins due to the efforts of the Mad Titan, Thanos. With the help of remaining allies, the Avengers must assemble once more in order to undo Thanos\' actions and restore order to the universe once and for all, no matter what consequences may be in store.', '2h 15m', 4.50, 'showing', 50000, 0),
(7, 'A Whisker Away', 'neko.jpg', 'Animation/Drama', 'A peculiar girl transforms into a cat to catch her crush\'s attention. But before she realizes it, the line between human and animal starts to blur.', '1h 44m', 3.95, 'showing', 50000, 0),
(8, 'I Want to Eat Your Pancreas', 'suizoutabetai.png', 'Animation/Drama', 'After his classmate and crush is diagnosed with a pancreatic disease, an average high schooler sets out to make the most of her final days.', '1h 48m', 4.10, 'showing', 50000, 0),
(9, 'Demon Slayer: Kimetsu no Yaiba – The Movie: Mugen Train', '1766077439_mugentrain.png', 'Animation', 'Tanjiro Kamado, joined with Inosuke Hashibira, a boy raised by boars who wears a boar\'s head, and Zenitsu Agatsuma, a scared boy who reveals his true power when he sleeps, boards the Infinity Train on a new mission with the Fire Hashira, Kyojuro Rengoku, to defeat a demon who has been tormenting the people and killing the demon slayers who oppose it!', '1h 57m', 4.50, 'showing', 50000, 0),
(13, 'Zootopia 2', '1766077609_zootopia2.png', 'Animation', 'After cracking the biggest case in Zootopia\'s history, rookie cops Judy Hopps and Nick Wilde find themselves on the twisting trail of a great mystery when Gary De’Snake arrives and turns the animal metropolis upside down. To crack the case, Judy and Nick must go undercover to unexpected new parts of town, where their growing partnership is tested like never before.', '1h 47m', 4.50, 'showing', 50000, 0),
(14, 'Demon Slayer: Kimetsu no Yaiba Infinity Castle', '1766113308_infinitycastle.png', 'Animation', 'The Demon Slayer Corps are drawn into the Infinity Castle, where Tanjiro, Nezuko, and the Hashira face terrifying Upper Rank demons in a desperate fight as the final battle against Muzan Kibutsuji begins.', '2h 36m', 4.50, 'coming_soon', 50000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `booking_code` varchar(20) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `show_date` date NOT NULL,
  `show_time` varchar(10) NOT NULL,
  `total_price` int(11) NOT NULL,
  `payment_status` enum('pending','paid','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `movie_id`, `booking_code`, `transaction_date`, `show_date`, `show_time`, `total_price`, `payment_status`) VALUES
(20, 9, 7, 'BKG-45A94340', '2025-12-17 16:01:22', '2025-12-17', '19:30', 200000, 'paid'),
(21, 9, 6, 'BKG-99AAEB68', '2025-12-17 16:01:57', '2025-12-17', '19:30', 50000, 'paid'),
(22, 9, 1, 'BKG-41D0DD50', '2025-12-17 16:11:27', '2025-12-20', '14:00', 100000, 'paid'),
(23, 9, 5, 'BKG-CCCB134E', '2025-12-18 10:33:49', '2025-12-18', '14:00', 100000, 'paid'),
(24, 9, 7, 'BKG-A96C9237', '2025-12-18 10:35:03', '2025-12-18', '14:00', 200000, 'paid'),
(25, 9, 8, 'BKG-799115B4', '2025-12-18 11:48:02', '2025-12-18', '14:00', 150000, 'paid'),
(26, 9, 8, 'BKG-0AC5538F', '2025-12-18 12:09:14', '2025-12-18', '14:00', 100000, 'paid'),
(27, 9, 8, 'BKG-6B8052A6', '2025-12-18 23:25:09', '2025-12-19', '19:30', 100000, 'paid'),
(28, 9, 6, 'BKG-9B84539D', '2025-12-18 23:25:50', '2025-12-19', '10:00', 200000, 'paid'),
(29, 13, 7, 'BKG-847606FB', '2025-12-19 21:23:42', '2025-12-20', '10:00', 100000, 'paid'),
(30, 13, 7, 'BKG-9DF36B67', '2025-12-19 22:23:56', '2025-12-20', '10:00', 100000, 'paid'),
(31, 13, 14, 'BKG-1D1BBF47', '2025-12-19 22:25:13', '2025-12-20', '10:00', 50000, 'paid'),
(32, 13, 14, 'BKG-76547544', '2025-12-19 22:29:07', '2025-12-20', '10:00', 50000, 'paid'),
(41, 13, 13, 'BKG-9BE609EA', '2025-12-20 11:53:15', '2025-12-20', '14:00', 50000, 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('active','banned') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `last_login`, `status`) VALUES
(9, 'Valencia', 'valen@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-18 10:30:47', '2025-12-18 03:30:47', 'active'),
(10, 'Admin', 'admin@moobix.com', '$2y$10$f4o/C0yWz2.5x1d9WW9Sju0cejPJ/9wRJpixomJ.701tGlOoKIfNm', 'admin', '2025-12-18 10:30:47', '2025-12-18 03:30:47', 'active'),
(12, 'Queen', 'queen@gmail.com', '$2y$10$GdV/JZBi4XMpC6kLeXvhoOxDejSQHXBUpJNjwuXm57yaUL5NV.KTq', 'user', '2025-12-19 20:02:16', '2025-12-19 13:02:16', 'active'),
(13, 'dummy', 'dummy@gmail.com', '$2y$10$0a/Rp2Xc8jprII0VT7zGVOxlO9crtmmMYBZAHGxYF84nx4KMe1nXq', 'user', '2025-12-19 21:18:46', '2025-12-19 14:18:46', 'active'),
(14, 'Budi', 'budi@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(15, 'Sarah', 'sarah@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(16, 'Andi', 'andi@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(17, 'Dewi', 'dewi@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(18, 'Eko', 'eko@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(19, 'Fajar', 'fajar@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(20, 'Gita', 'gita@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(21, 'Hendra', 'hendra@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(22, 'Indah', 'indah@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(23, 'Joko', 'joko@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(24, 'Kartika', 'kartika@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(25, 'Lutfi', 'lutfi@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(26, 'Maya', 'maya@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(27, 'Nanda', 'nanda@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active'),
(28, 'Oscar', 'oscar@gmail.com', '$2y$10$IOe6XLf6iwqfq7pwLf9W1uPy9Mvm/ICbRffA6LINnk/E5GlleSgdS', 'user', '2025-12-19 21:32:25', '2025-12-19 14:32:25', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booked_seats`
--
ALTER TABLE `booked_seats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `fk_seats_movie` (`movie_id`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booked_seats`
--
ALTER TABLE `booked_seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booked_seats`
--
ALTER TABLE `booked_seats`
  ADD CONSTRAINT `fk_seats_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_seats_trx` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_trx_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_trx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
