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
-- Table structure for table `hoopsdynastyteams`
--

CREATE TABLE `hoopsdynastyteams` (
  `id` int NOT NULL,
  `creator` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `team_name` varchar(32) COLLATE utf8mb4_general_ci NOT NULL,
  `access` int NOT NULL DEFAULT '0',
  `center` varchar(32) COLLATE utf8mb4_general_ci NOT NULL,
  `power_forward` varchar(32) COLLATE utf8mb4_general_ci NOT NULL,
  `small_forward` varchar(32) COLLATE utf8mb4_general_ci NOT NULL,
  `point_guard` varchar(32) COLLATE utf8mb4_general_ci NOT NULL,
  `shooting_guard` varchar(32) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoopsdynastyteams`
--

INSERT INTO `hoopsdynastyteams` (`id`, `creator`, `team_name`, `access`, `center`, `power_forward`, `small_forward`, `point_guard`, `shooting_guard`) VALUES
(1, 'dmgg', 'Golden State Warriors', 1, 'Gui Santos', 'Draymond Green', 'Jimmy Butler', 'Stephen Curry', 'Buddy Hield'),
(5, 'moeez', 'New Team', 0, 'Lebron James', 'Draymond Green', 'Markieff Morris', 'Markieff Morris', 'Draymond Green'),
(6, 'moeez1', 'Moeez', 0, 'Lebron James', 'Stephen Curry', 'Stephen Curry', 'Stephen Curry', 'Stephen Curry'),
(7, 'testing', 'My Team', 0, 'Lebron James', 'Lebron James', 'Lebron James', 'Lebron James', 'Lebron James'),
(9, 'test', 'New Team Baby', 0, 'Draymond Green', 'Buddy Hield', 'Markieff Morris', 'Gui Santos', 'Lebron James'),
(11, 'dmgg', 'This guy', 0, 'Markieff Morris', 'Draymond Green', 'Gui Santos', 'Lebron James', 'Buddy Hield'),
(12, 'testingAgain', 'Winner', 0, 'Draymond Green', 'Markieff Morris', 'Jimmy Butler', 'Lebron James', 'Stephen Curry'),
(13, 'Gagan', 'GeekGeekness', 0, 'Buddy Hield', 'Jimmy Butler', 'Markieff Morris', 'Lebron James', 'Stephen Curry'),
(14, 'daryl', 'Lakers', 0, 'Draymond Green', 'Markieff Morris', 'Jimmy Butler', 'Lebron James', 'Stephen Curry'),
(15, 'daryl', 'Miami Heats', 0, 'Jimmy Butler', 'Buddy Hield', 'Draymond Green', 'Stephen Curry', 'Lebron James'),
(16, 'dmgg', 'Team 45', 1, 'Sam Scott', 'Grady Rueffer', 'Gagan Bhattari', 'Abdul Mooez Shaikh', 'Daryl John'),
(17, 'dmgg', 'LA Lakers', 1, 'Markieff Morris', 'Lebron James', 'Alex Len', 'Dorian Finney-Smith', 'Gabe Vincent'),
(18, 'dmgg', 'Toronto Raptors', 1, 'Gradey Dick', 'Jakob Poeltl', 'Chris Boucher', 'RJ Barrett', 'Garrett Temple'),
(19, 'dmgg', 'Boston Celtics', 1, 'Torrey Craig', 'Al Horford', 'Luke Kornet', 'Jrue Holiday', 'Derrick White'),
(20, 'dmgg', 'Miami Heat', 1, 'Alec Burks', 'Kyle Anderson', 'Andrew Wiggins', 'Terry Rozier', 'Kevin Love'),
(21, 'dmgg', 'Chicago Bulls', 1, 'Kevin Huerter', 'Nikola Vucevic', 'Jevon Carter', 'Zach Collins', 'Lonzo Ball'),
(22, 'dmgg', 'Washington Wizards', 1, 'Anthony Gill', 'Richaun Holmes', 'Marcus Smart', 'Malcolm Brogdon', 'Khris Middleton'),
(23, 'dmgg', 'LA Clippers', 1, 'Patty Mills', 'James Harden', 'Nicolas Batum', 'Bogdan Bogdanovic', 'Kawhi Leonard'),
(24, 'dmgg', 'Detroit Pistons', 1, 'Tobias Harris', 'Malik Beasley', 'Lindy Waters III', 'Dennis Schroder', 'Paul Reed'),
(25, 'dmgg', 'Atlanta Hawks', 1, 'Georges Niang', 'Caris LeVert', 'Larry Nance Jr.', 'Clint Capela', 'Terance Mann'),
(26, 'dmgg', 'Sacremento Kings', 1, 'Doug McDermott', 'Jae Crowder', 'Jonas Valanciunas', 'Mason Jones', 'DeMar DeRozan'),
(27, 'dmgg', 'New York Knicks', 1, 'Mikal Bridges', 'Josh Hart', 'Cameron Payne', 'Karl-Anthony Towns', 'Delon Wright'),
(28, 'dmgg', 'Cleveland Cavaliers', 1, 'Donovan Mitchell', 'Jarrett Allen', 'Max Strus', 'Dean Wade', 'Javonte Green'),
(29, 'dmgg', 'Dallas Mavericks', 1, 'Dwight Powell', 'Spencer Dinwiddie', 'Anthony Davis', 'Kyrie Irving', 'Klay Thompson'),
(30, 'test', 'another 1', 0, 'James Harden', 'Jimmy Butler', 'Stephen Curry', 'Kevin Love', 'Lebron James'),
(31, 'Mr. Test', 'Test&#39;s Love', 0, 'Gradey Dick', 'Terance Mann', 'Josh Hart', 'Kevin Love', 'James Harden');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hoopsdynastyteams`
--
ALTER TABLE `hoopsdynastyteams`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hoopsdynastyteams`
--
ALTER TABLE `hoopsdynastyteams`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
