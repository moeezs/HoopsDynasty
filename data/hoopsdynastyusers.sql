-- phpMyAdmin SQL Dump
-- version 5.2.2-1.el9
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 26, 2025 at 10:33 PM
-- Server version: 9.1.0-commercial
-- PHP Version: 8.2.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ruefferg_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `hoopsdynastyusers`
--

CREATE TABLE `hoopsdynastyusers` (
  `id` int NOT NULL,
  `username` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(86) COLLATE utf8mb4_general_ci NOT NULL,
  `firstname` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `accesslevel` int NOT NULL DEFAULT '0',
  `email` varchar(320) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoopsdynastyusers`
--

INSERT INTO `hoopsdynastyusers` (`id`, `username`, `password`, `firstname`, `lastname`, `accesslevel`, `email`) VALUES
(1, 'dmgg', '$2y$10$KCRrbKTxP9NiQ0GPACWNJ..WH8b.VF4iszQK2UvF54olN4wo8HGou', 'Dynamite', 'DMGG', 1, NULL),
(3, 'Mr. Test', '$2y$10$Op5SiVZgC4Nv3z4JNwuXHOYdY.Pst4VWxomz2HYS6B0azcqm475tW', 'Tester', 'Testington', 0, 'tester@gmail.com'),
(4, 'Plotter', '$2y$10$L7R4PguiQUdpXbON6YzZS.zp/1uvgWMZrjmIGKQBZp2vB/L4d9osO', 'Lmao', 'hacked', 0, 'hacker@doe.com'),
(6, 'qwe', '$2y$10$GGnjn7vhCptMYpHe1stJzeeWMC5dzAWvOWvWA91MfzuMN/8mIcMLe', 'qwe', 'qwe', 0, 'qwe@1.x'),
(7, 'dfdvdfvdfvd', '$2y$10$q2e/SE3Ws57UDPPc2I3GH.xklqe2LZ/VK1/Zw/nnvJDoodprnnwhK', 'dvdf', 'dfvdf', 0, 'vdvd'),
(8, 'moeez', '$2y$10$ZFxafoXkp5NmpwWILUvdcehNYCUkBxZG7REMbl7hHCx5OmId1MEXK', 'Mo', 'Money', 0, 'moeez@gmail.com'),
(9, 'bhattarg', '$2y$10$pElNFSjlwtyhlJoa7MVATuibzwdTwdrpRb8A2M7f1qvdgrb3MqEqa', 'Gagan', 'Bhattarai', 0, 'gagan.bhattarai25@gmail.com'),
(10, 'moeez1', '$2y$10$ca1fT2aRvPTItEUfDGEqqOjo08u1vEBWP.WnEaiffivJhjhV3fyuq', 'Mo', 'Money', 0, 'moee2z@gmail.com'),
(11, 'testing', '$2y$10$MC.Noj/yriWIgOwtoo.EceveFGqlln/oHMblI9YNKFoPuhkPFDS36', 'Test', 'qwe', 0, 'test@gmail.com'),
(12, 'test', '$2y$10$ua5beoGiqMb7RieXGThXQunowElpK6AP4JWyd01LHCMptFsU5eyEC', 'Test Again', '1', 0, 'anothertest@gmail.com'),
(13, 'Grady (dmgg)', '$2y$10$7J/qi0vAPUXTYr3NOlWVwOwAdIbURCYitjNeQS.3ghj3mN8iYHO.a', 'Grady', 'Rueffer', 0, 'wouldn\'t@youliketoknow.weatherboy'),
(14, 'testingAgain', '$2y$10$VFIre9DW9RDORAxYVPuGnuua6/HTA01awwGcGxqYQ2Wys7ttINteq', 'Lmao', 'qwe', 0, 'qwe@1.x'),
(15, 'Gagan', '$2y$10$WgI7pBBCYUhiQA0Bhh0k5.18Lq6t3BcKhTRL4/zE4U4GGVIuFJ/Pe', 'Gagan', 'Gagan', 0, 'Gagan@Gagan.com'),
(16, 'daryl', '$2y$10$JScgRo51uLzTuTUpS69/pu46cJLOFHJlPWqWn141m7fZEsDJCNwXe', 'Daryl', 'John', 0, 'daryl@daryl.com'),
(17, 'Ms. Test', '$2y$10$iHzs.OPHbUxXvQl4sGTUKe4TwOVWZmGFBQISF3mxAAenFjv31rt/a', 'Testess', 'Testington', 0, 'test@test.test');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hoopsdynastyusers`
--
ALTER TABLE `hoopsdynastyusers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hoopsdynastyusers`
--
ALTER TABLE `hoopsdynastyusers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
