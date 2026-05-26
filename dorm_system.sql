-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2026-05-23 13:37:10
-- 服务器版本： 5.7.26
-- PHP 版本： 7.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `dorm_system`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '123456');

-- --------------------------------------------------------

--
-- 表的结构 `dormitory`
--

CREATE TABLE `dormitory` (
  `id` int(11) NOT NULL,
  `building_no` int(11) NOT NULL,
  `floor_no` int(11) NOT NULL,
  `room_no` int(11) NOT NULL,
  `gender` enum('男','女') COLLATE utf8_unicode_ci NOT NULL DEFAULT '男',
  `capacity` int(11) DEFAULT '4',
  `available_beds` int(11) DEFAULT '4'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `dormitory`
--

INSERT INTO `dormitory` (`id`, `building_no`, `floor_no`, `room_no`, `gender`, `capacity`, `available_beds`) VALUES
(1, 1, 1, 1, '男', 4, 3),
(2, 1, 1, 2, '男', 4, 4),
(3, 1, 1, 3, '男', 4, 4),
(4, 1, 1, 4, '男', 4, 4),
(5, 1, 1, 5, '男', 4, 4),
(6, 1, 2, 1, '男', 4, 4),
(7, 1, 2, 2, '男', 4, 4),
(8, 1, 2, 3, '男', 4, 4),
(9, 1, 2, 4, '男', 4, 4),
(10, 1, 2, 5, '男', 4, 4),
(11, 1, 3, 1, '男', 4, 4),
(12, 1, 3, 2, '男', 4, 4),
(13, 1, 3, 3, '男', 4, 4),
(14, 1, 3, 4, '男', 4, 4),
(15, 1, 3, 5, '男', 4, 4),
(16, 2, 1, 1, '男', 4, 4),
(17, 2, 1, 2, '男', 4, 4),
(18, 2, 1, 3, '男', 4, 4),
(19, 2, 1, 4, '男', 4, 4),
(20, 2, 1, 5, '男', 4, 4),
(21, 2, 2, 1, '男', 4, 4),
(22, 2, 2, 2, '男', 4, 4),
(23, 2, 2, 3, '男', 4, 4),
(24, 2, 2, 4, '男', 4, 4),
(25, 2, 2, 5, '男', 4, 4),
(26, 2, 3, 1, '男', 4, 4),
(27, 2, 3, 2, '男', 4, 4),
(28, 2, 3, 3, '男', 4, 4),
(29, 2, 3, 4, '男', 4, 4),
(30, 2, 3, 5, '男', 4, 4),
(31, 3, 1, 1, '女', 4, 4),
(32, 3, 1, 2, '女', 4, 4),
(33, 3, 1, 3, '女', 4, 4),
(34, 3, 1, 4, '女', 4, 4),
(35, 3, 1, 5, '女', 4, 4),
(36, 3, 2, 1, '女', 4, 4),
(37, 3, 2, 2, '女', 4, 4),
(38, 3, 2, 3, '女', 4, 4),
(39, 3, 2, 4, '女', 4, 4),
(40, 3, 2, 5, '女', 4, 4),
(41, 3, 3, 1, '女', 4, 4),
(42, 3, 3, 2, '女', 4, 4),
(43, 3, 3, 3, '女', 4, 4),
(44, 3, 3, 4, '女', 4, 4),
(45, 3, 3, 5, '女', 4, 4);

-- --------------------------------------------------------

--
-- 表的结构 `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `student_no` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `class_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `gender` enum('男','女') COLLATE utf8_unicode_ci NOT NULL,
  `dorm_id` int(11) DEFAULT NULL,
  `bed_no` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `student`
--

INSERT INTO `student` (`id`, `student_no`, `name`, `class_name`, `gender`, `dorm_id`, `bed_no`) VALUES
(9, '11111', '1', '1', '男', 1, 1);

--
-- 转储表的索引
--

--
-- 表的索引 `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 表的索引 `dormitory`
--
ALTER TABLE `dormitory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `building_no` (`building_no`,`floor_no`,`room_no`);

--
-- 表的索引 `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_no` (`student_no`),
  ADD KEY `dorm_id` (`dorm_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `dormitory`
--
ALTER TABLE `dormitory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- 使用表AUTO_INCREMENT `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
