-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 21, 2026 at 03:30 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aynek`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` int NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `PhoneNo` varchar(15) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`AdminID`, `FirstName`, `LastName`, `Username`, `Email`, `PhoneNo`, `Password`) VALUES
(2, 'Admin', 'Aynek', 'admin', 'admin@aynek.sa', '0500000000', '$2y$10$JyR9cxuroH/B/d.OurmRhuEhdx5Bm7Mo/qE7P1vGR6iYhnmCJOqMq');

-- --------------------------------------------------------

--
-- Table structure for table `citizen`
--

CREATE TABLE `citizen` (
  `CitizenID` int NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `PhoneNo` varchar(15) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `BirthDate` date NOT NULL,
  `Nationality` varchar(50) NOT NULL,
  `IDNo` varchar(20) NOT NULL,
  `TotalRewardPoints` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `citizen`
--

INSERT INTO `citizen` (`CitizenID`, `FirstName`, `LastName`, `Username`, `Email`, `PhoneNo`, `Password`, `BirthDate`, `Nationality`, `IDNo`, `TotalRewardPoints`) VALUES
(1, 'سارة', 'العمري', 'sara_2026', 'sara@email.com', '0501234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1998-03-15', 'sa', '1098765432', 40),
(2, 'محمد', 'الحربي', 'mohammed_h', 'mohammed@email.com', '0509876543', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1995-07-22', 'sa', '1087654321', 20),
(3, 'نورة', 'القحطاني', 'noura_q', 'noura@email.com', '0551122334', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2000-11-05', 'sa', '1076543210', 30);

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `LocationID` int NOT NULL,
  `ReportID` varchar(20) NOT NULL,
  `Latitude` decimal(10,7) NOT NULL,
  `Longitude` decimal(10,7) NOT NULL,
  `Address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`LocationID`, `ReportID`, `Latitude`, `Longitude`, `Address`) VALUES
(9, 'RPT-AA1111', 24.7741000, 46.6922000, 'حي النزهة، الرياض'),
(10, 'RPT-BB2222', 24.6877000, 46.6860000, 'حي العليا، الرياض'),
(11, 'RPT-CC3333', 24.6972000, 46.7219000, 'حي الملز، الرياض'),
(12, 'RPT-DD4444', 24.6890000, 46.6800000, 'حي السليمانية، الرياض'),
(13, 'RPT-EE5555', 24.7200000, 46.6900000, 'حي الروضة، الرياض'),
(14, 'RPT-FF6666', 24.7050000, 46.7100000, 'حي الورود، الرياض');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `ReportID` varchar(20) NOT NULL,
  `CitizenID` int NOT NULL,
  `AdminID` int DEFAULT NULL,
  `Title` varchar(100) NOT NULL,
  `ViolationType` varchar(50) NOT NULL,
  `Description` text NOT NULL,
  `ImagePath` varchar(255) DEFAULT NULL,
  `SubmittedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `Status` enum('Pending','Under Review','Resolved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`ReportID`, `CitizenID`, `AdminID`, `Title`, `ViolationType`, `Description`, `ImagePath`, `SubmittedAt`, `Status`) VALUES
('RPT-AA1111', 1, NULL, 'وقوف مخالف أمام مدخل', 'وقوف مخالف', 'سيارة واقفة تسد مدخل العمارة منذ الصباح', NULL, '2025-01-12 09:30:00', 'Pending'),
('RPT-BB2222', 1, NULL, 'إشغال رصيف المشاة', 'إشغال رصيف المشاة', 'بضاعة محل تسد الرصيف بالكامل', NULL, '2025-01-15 14:00:00', 'Resolved'),
('RPT-CC3333', 2, NULL, 'سد مخرج طوارئ', 'سد مخرج طوارئ', 'سيارة تقف أمام بوابة الطوارئ في المستشفى', NULL, '2025-01-18 11:15:00', 'Under Review'),
('RPT-DD4444', 2, NULL, 'احتلال موقف ذوي الاحتياجات', 'مواقف ذوي الاحتياجات', 'سيارة بدون تصريح تقف في موقف ذوي الاحتياجات', NULL, '2025-01-20 16:45:00', 'Rejected'),
('RPT-EE5555', 3, NULL, 'إعاقة حركة المرور', 'إعاقة حركة المرور', 'شاحنة متوقفة في منتصف الشارع تعطل السير', NULL, '2025-01-22 08:00:00', 'Pending'),
('RPT-FF6666', 3, NULL, 'وقوف في منطقة ممنوعة', 'وقوف مخالف', 'سيارة واقفة على الخط الأصفر أمام المدرسة', NULL, '2025-01-25 13:30:00', 'Resolved');

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `RewardID` int NOT NULL,
  `CitizenID` int NOT NULL,
  `ReportID` varchar(20) NOT NULL,
  `Points` int DEFAULT '10',
  `Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`RewardID`, `CitizenID`, `ReportID`, `Points`, `Date`) VALUES
(4, 1, 'RPT-AA1111', 10, '2025-01-12'),
(5, 1, 'RPT-BB2222', 10, '2025-01-15'),
(6, 2, 'RPT-CC3333', 10, '2025-01-18'),
(7, 2, 'RPT-DD4444', 10, '2025-01-20'),
(8, 3, 'RPT-EE5555', 10, '2025-01-22'),
(9, 3, 'RPT-FF6666', 10, '2025-01-25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `citizen`
--
ALTER TABLE `citizen`
  ADD PRIMARY KEY (`CitizenID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `IDNo` (`IDNo`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`LocationID`),
  ADD UNIQUE KEY `ReportID` (`ReportID`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`ReportID`),
  ADD KEY `CitizenID` (`CitizenID`),
  ADD KEY `AdminID` (`AdminID`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`RewardID`),
  ADD KEY `CitizenID` (`CitizenID`),
  ADD KEY `ReportID` (`ReportID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `AdminID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `citizen`
--
ALTER TABLE `citizen`
  MODIFY `CitizenID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `LocationID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `RewardID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `location`
--
ALTER TABLE `location`
  ADD CONSTRAINT `location_ibfk_1` FOREIGN KEY (`ReportID`) REFERENCES `report` (`ReportID`) ON DELETE CASCADE;

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`CitizenID`) REFERENCES `citizen` (`CitizenID`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_ibfk_2` FOREIGN KEY (`AdminID`) REFERENCES `admin` (`AdminID`) ON DELETE SET NULL;

--
-- Constraints for table `rewards`
--
ALTER TABLE `rewards`
  ADD CONSTRAINT `rewards_ibfk_1` FOREIGN KEY (`CitizenID`) REFERENCES `citizen` (`CitizenID`) ON DELETE CASCADE,
  ADD CONSTRAINT `rewards_ibfk_2` FOREIGN KEY (`ReportID`) REFERENCES `report` (`ReportID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
