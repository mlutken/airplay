-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 09. 02 2013 kl. 13:16:14
-- Serverversion: 5.5.29
-- PHP-version: 5.3.10-1ubuntu3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `airplay_music`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `item_base`
--

CREATE TABLE IF NOT EXISTS `item_base` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_type` bit(1) NOT NULL,
  `artist_id` int(10) unsigned NOT NULL,
  `item_name` varchar(256) NOT NULL,
  `record_label_id` int(6) unsigned NOT NULL DEFAULT '0',
  `genre_id` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `subgenre_ids` varchar(32) NOT NULL DEFAULT '',
  `album_year` smallint(4) unsigned NOT NULL DEFAULT '0',
  `release_date` int(11) NOT NULL DEFAULT '0',
  `parent_item` int(11) NOT NULL DEFAULT '0',
  `item_time` int(11) NOT NULL DEFAULT '0',
  `child_items` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`item_id`),
  KEY `artist_id` (`artist_id`,`record_label_id`),
  KEY `item_type` (`item_type`),
  KEY `item_name` (`item_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Data dump for tabellen `item_base`
--

INSERT INTO `item_base` (`item_id`, `item_type`, `artist_id`, `item_name`, `record_label_id`, `genre_id`, `subgenre_ids`, `album_year`, `release_date`, `parent_item`, `item_time`, `child_items`) VALUES
(1, b'0', 1, 'Draining Puddles, Retrieving Treasures', 0, 1, '', 2006, 0, 0, 0, ''),
(2, b'0', 3, 'Dark Words, Gentle Sounds', 0, 1, '', 1998, 0, 0, 0, ''),
(3, b'0', 3, 'Li & Friends', 0, 1, '', 1993, 0, 0, 0, ''),
(4, b'0', 3, '48 K', 0, 1, '', 1994, 0, 0, 0, ''),
(5, b'0', 4, 'In The Shadows', 0, 1, '', 2009, 0, 0, 0, ''),
(6, b'0', 5, 'The Sugar & The Salt', 0, 1, '', 2007, 0, 0, 0, ''),
(7, b'0', 6, 'Ab Und Zu', 0, 1, '', 1989, 0, 0, 0, ''),
(8, b'0', 6, 'Dark Glasses - Remix Ep', 0, 1, '', 1997, 0, 0, 0, ''),
(9, b'0', 6, 'Spark Of Life', 0, 1, '', 2002, 0, 0, 0, ''),
(10, b'0', 6, 'Totally', 0, 1, '', 1996, 0, 0, 0, ''),
(11, b'0', 7, 'Absolotus Och Lasse Och Ludmila', 0, 1, '', 1998, 0, 0, 0, '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
