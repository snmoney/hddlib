SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `hddlib`
--
CREATE DATABASE IF NOT EXISTS `hddlib` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `hddlib`;

-- --------------------------------------------------------

--
-- 表 `disk_name`
--

CREATE TABLE IF NOT EXISTS `disk_name` (
  `id` int(10) unsigned NOT NULL,
  `dname` varchar(40) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dsn` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 表 `disk_tree`
--

CREATE TABLE IF NOT EXISTS `disk_tree` (
  `id` int(10) unsigned NOT NULL,
  `diskid` int(10) unsigned NOT NULL,
  `path` varchar(600) NOT NULL,
  `filename` varchar(600) NOT NULL,
  `filesize` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes
--
ALTER TABLE `disk_name`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Index_2` (`dname`),
  ADD UNIQUE KEY `Index_3` (`dsn`);

ALTER TABLE `disk_tree`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Index_diskid` (`diskid`),
  ADD KEY `Index_3` (`path`),
  ADD KEY `Index_4` (`filename`);

ALTER TABLE `disk_name`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `disk_tree`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
  
