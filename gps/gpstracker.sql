-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 29, 2016 at 09:08 AM
-- Server version: 5.6.12-log
-- PHP Version: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `gpstracker`
--
CREATE DATABASE IF NOT EXISTS `gpstracker` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `gpstracker`;

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `prcDeleteRoute`(
_sessionID VARCHAR(50))
BEGIN
  DELETE FROM gpslocations
  WHERE sessionID = _sessionID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `prcGetAllRoutesForMap`()
BEGIN
SELECT sessionId, gpsTime, CONCAT('{ "latitude":"', CAST(latitude AS CHAR),'", "longitude":"', CAST(longitude AS CHAR), '", "speed":"', CAST(speed AS CHAR), '", "direction":"', CAST(direction AS CHAR), '", "distance":"', CAST(distance AS CHAR), '", "locationMethod":"', locationMethod, '", "gpsTime":"', DATE_FORMAT(gpsTime, '%b %e %Y %h:%i%p'), '", "userName":"', userName, '", "phoneNumber":"', phoneNumber, '", "sessionID":"', CAST(sessionID AS CHAR), '", "accuracy":"', CAST(accuracy AS CHAR), '", "extraInfo":"', extraInfo, '" }') json
FROM (SELECT MAX(GPSLocationID) ID
      FROM gpslocations
      WHERE sessionID != '0' && CHAR_LENGTH(sessionID) != 0 && gpstime != '0000-00-00 00:00:00'
      GROUP BY sessionID) AS MaxID
JOIN gpslocations ON gpslocations.GPSLocationID = MaxID.ID
ORDER BY gpsTime;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `prcGetRouteForMap`(
_sessionID VARCHAR(50))
BEGIN
  SELECT CONCAT('{ "latitude":"', CAST(latitude AS CHAR),'", "longitude":"', CAST(longitude AS CHAR), '", "speed":"', CAST(speed AS CHAR), '", "direction":"', CAST(direction AS CHAR), '", "distance":"', CAST(distance AS CHAR), '", "locationMethod":"', locationMethod, '", "gpsTime":"', DATE_FORMAT(gpsTime, '%b %e %Y %h:%i%p'), '", "userName":"', userName, '", "phoneNumber":"', phoneNumber, '", "sessionID":"', CAST(sessionID AS CHAR), '", "accuracy":"', CAST(accuracy AS CHAR), '", "extraInfo":"', extraInfo, '" }') json
  FROM gpslocations
  WHERE sessionID = _sessionID
  ORDER BY lastupdate;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `prcGetRoutes`()
BEGIN
  CREATE TEMPORARY TABLE tempRoutes (
    sessionID VARCHAR(50),
    userName VARCHAR(50),
    startTime DATETIME,
    endTime DATETIME)
  ENGINE = MEMORY;

  INSERT INTO tempRoutes (sessionID, userName)
  SELECT DISTINCT sessionID, userName
  FROM gpslocations;

  UPDATE tempRoutes tr
  SET startTime = (SELECT MIN(gpsTime) FROM gpslocations gl
  WHERE gl.sessionID = tr.sessionID
  AND gl.userName = tr.userName);

  UPDATE tempRoutes tr
  SET endTime = (SELECT MAX(gpsTime) FROM gpslocations gl
  WHERE gl.sessionID = tr.sessionID
  AND gl.userName = tr.userName);

  SELECT

  CONCAT('{ "sessionID": "', CAST(sessionID AS CHAR),  '", "userName": "', userName, '", "times": "(', DATE_FORMAT(startTime, '%b %e %Y %h:%i%p'), ' - ', DATE_FORMAT(endTime, '%b %e %Y %h:%i%p'), ')" }') json
  FROM tempRoutes
  ORDER BY startTime DESC;

  DROP TABLE tempRoutes;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `prcSaveGPSLocation`(
_latitude DECIMAL(10,7),
_longitude DECIMAL(10,7),
_speed INT(10),
_direction INT(10),
_distance DECIMAL(10,1),
_date TIMESTAMP,
_locationMethod VARCHAR(50),
_userName VARCHAR(50),
_phoneNumber VARCHAR(50),
_sessionID VARCHAR(50),
_accuracy INT(10),
_extraInfo VARCHAR(255),
_eventType VARCHAR(50)
)
BEGIN
   INSERT INTO gpslocations (latitude, longitude, speed, direction, distance, gpsTime, locationMethod, userName, phoneNumber,  sessionID, accuracy, extraInfo, eventType)
   VALUES (_latitude, _longitude, _speed, _direction, _distance, _date, _locationMethod, _userName, _phoneNumber, _sessionID, _accuracy, _extraInfo, _eventType);
   SELECT NOW();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `prcSavePTPosition`(IN `_lat` DOUBLE, IN `_lon` DOUBLE, IN `_speed_km` DOUBLE, IN `_datetime` TIMESTAMP, IN `_datetime_received` TIMESTAMP, IN `_unit_id` INT(11), IN `_raw_input` VARCHAR(255), IN `_alt` DOUBLE, IN `_deg` DOUBLE, IN `_speed_kn` DOUBLE, IN `_sattotal` INT(11), IN `_fixtype` INT(1), IN `_hash` CHAR(32))
BEGIN
   INSERT INTO pt_position (lat, lon, speed_km, unit_id,raw_input,datetime,datetime_received,alt,deg,speed_kn,sattotal,fixtype,hash)
   VALUES (_lat, _lon, _speed_km, _unit_id, _raw_input, _datetime, _datetime_received,_alt,_deg,_speed_kn,_sattotal,_fixtype,_hash);
   SELECT NOW();
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `gpslocations`
--

CREATE TABLE IF NOT EXISTS `gpslocations` (
  `GPSLocationID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `latitude` decimal(10,7) NOT NULL DEFAULT '0.0000000',
  `longitude` decimal(10,7) NOT NULL DEFAULT '0.0000000',
  `phoneNumber` varchar(50) NOT NULL DEFAULT '',
  `userName` varchar(50) NOT NULL DEFAULT '',
  `sessionID` varchar(50) NOT NULL DEFAULT '',
  `speed` int(10) unsigned NOT NULL DEFAULT '0',
  `direction` int(10) unsigned NOT NULL DEFAULT '0',
  `distance` decimal(10,1) NOT NULL DEFAULT '0.0',
  `gpsTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locationMethod` varchar(50) NOT NULL DEFAULT '',
  `accuracy` int(10) unsigned NOT NULL DEFAULT '0',
  `extraInfo` varchar(255) NOT NULL DEFAULT '',
  `eventType` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`GPSLocationID`),
  KEY `sessionIDIndex` (`sessionID`),
  KEY `phoneNumberIndex` (`phoneNumber`),
  KEY `userNameIndex` (`userName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pt_position`
--

CREATE TABLE IF NOT EXISTS `pt_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `datetime_received` datetime NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `alt` double NOT NULL,
  `deg` double NOT NULL,
  `speed_km` double NOT NULL,
  `speed_kn` double NOT NULL,
  `sattotal` int(11) NOT NULL,
  `fixtype` int(1) NOT NULL,
  `raw_input` tinytext NOT NULL,
  `hash` char(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`),
  KEY `unit_id` (`unit_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pt_unit`
--

CREATE TABLE IF NOT EXISTS `pt_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` tinytext NOT NULL,
  `imei` tinytext NOT NULL,
  `password` char(32) NOT NULL,
  `icon` int(11) NOT NULL DEFAULT '0',
  `linecol` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pt_user`
--

CREATE TABLE IF NOT EXISTS `pt_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  `username` tinytext NOT NULL,
  `password` varchar(32) NOT NULL DEFAULT '',
  `timezone` tinytext NOT NULL,
  `pcacredit` int(11) NOT NULL DEFAULT '0',
  `registered` datetime NOT NULL,
  `lastlogin` datetime NOT NULL,
  `usergroup` int(1) NOT NULL DEFAULT '4',
  `nonactive` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `pt_user`
--
-- Password for admin@jantv.in is csl@jantv

INSERT INTO `pt_user` (`id`, `name`, `username`, `password`, `timezone`, `pcacredit`, `registered`, `lastlogin`, `usergroup`, `nonactive`) VALUES
(1, 'Admin', 'admin@jantv.in', '4b8a7b98cc5dd40ca4287a1ccbe3f45f', 'Asia/Kolkata', 0, '2007-07-31 20:26:14', '2016-07-29 09:01:43', 1, 0);

CREATE TABLE IF NOT EXISTS `pt_position_unreguser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_entry` char(25) NOT NULL,
  `datetime` datetime NOT NULL,
  `datetime_received` datetime NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `alt` double NOT NULL,
  `deg` double NOT NULL,
  `speed_km` double NOT NULL,
  `speed_kn` double NOT NULL,
  `sattotal` int(11) NOT NULL,
  `fixtype` int(1) NOT NULL,
  `raw_input` tinytext NOT NULL,
  `hash` char(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`),
  KEY `user_entry` (`user_entry`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pt_position`
--
ALTER TABLE `pt_position`
  ADD CONSTRAINT `pt_position_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `pt_unit` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pt_unit`
--
ALTER TABLE `pt_unit`
  ADD CONSTRAINT `pt_unit_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `pt_user` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
