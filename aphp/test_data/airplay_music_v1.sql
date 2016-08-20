-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 20. 03 2013 kl. 14:05:55
-- Serverversion: 5.5.29
-- PHP-version: 5.3.10-1ubuntu3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `airplay_music_v1`
--

-- --------------------------------------------------------


--
-- Table structure for table `word`
--

CREATE TABLE IF NOT EXISTS `word` (
  `word_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `word_name` varchar(128) NOT NULL,
  `artist_word_count` int(11) unsigned NOT NULL DEFAULT '0',
  `album_word_count` int(11) unsigned NOT NULL DEFAULT '0',
  `song_word_count` int(11) unsigned NOT NULL DEFAULT '0',
  `word_count` int(10) unsigned NOT NULL DEFAULT '0',
  `word_length` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`word_id`),
  KEY `word_length` (`word_length`),
  KEY `word_name` (`word_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


--
-- Struktur-dump for tabellen `artist`
--

CREATE TABLE IF NOT EXISTS `artist` (
  `artist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `artist_name` varchar(256) NOT NULL,
  `artist_url` varchar(256) NOT NULL,
  `genre_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `subgenre_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `country_id` smallint(6) DEFAULT '0',
  `artist_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `artist_reliability` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`artist_id`),
  KEY `artist_name` (`artist_name`(255)),
  KEY `genre_id` (`genre_id`),
  KEY `artist_reliability` (`artist_reliability`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `artist_synonym`
--

CREATE TABLE IF NOT EXISTS `artist_synonym` (
  `artist_synonym_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `artist_synonym_name` varchar(256) NOT NULL DEFAULT '',
  `artist_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`artist_synonym_id`),
  KEY `artist_synonym_name` (`artist_synonym_name`(255)),
  KEY `artist_id` (`artist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `country`
--

CREATE TABLE IF NOT EXISTS `country` (
  `country_id` smallint(3) NOT NULL,
  `country_name` varchar(256) NOT NULL,
  PRIMARY KEY (`country_id`),
  KEY `country_name` (`country_name`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Data dump for tabellen `country`
--

INSERT INTO `country` (`country_id`, `country_name`) VALUES
(0, 'Unknown'),
(1, 'USA'),
(33, 'France'),
(44, 'UK'),
(45, 'Denmark'),
(46, 'Sweden'),
(47, 'Norway'),
(49, 'Germany'),
(358, 'Finland');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `currency`
--

CREATE TABLE IF NOT EXISTS `currency` (
  `currency_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `currency_name` char(4) NOT NULL,
  `from_euro` double DEFAULT NULL,
  `to_euro` double DEFAULT NULL,
  PRIMARY KEY (`currency_id`),
  KEY `currency_name` (`currency_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Data dump for tabellen `currency`
--

INSERT INTO `currency` (`currency_id`, `currency_name`, `from_euro`, `to_euro`) VALUES
(1, 'EUR', 1, 1),
(2, 'DKK', 7.46, 0.13404825737265),
(3, 'SEK', 8.5435, 0.1170480482238),
(4, 'GBP', 0.8093, 1.2356357345854),
(5, 'USD', 1.3104, 0.76312576312576),
(6, 'NOK', 7.2974, 0.13703510839477);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `currency_to_euro`
--

CREATE TABLE IF NOT EXISTS `currency_to_euro` (
  `currency_to_euro_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `currency_id` tinyint(3) unsigned NOT NULL,
  `currency_name` char(4) NOT NULL,
  `to_euro` double DEFAULT NULL,
  PRIMARY KEY (`currency_to_euro_id`),
  KEY `currency_id` (`currency_id`),
  KEY `currency_name` (`currency_id`,`currency_name`,`to_euro`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Data dump for tabellen `currency_to_euro`
--

INSERT INTO `currency_to_euro` (`currency_to_euro_id`, `currency_id`, `currency_name`, `to_euro`) VALUES
(1, 1, 'EUR', 1),
(2, 2, 'DKK', 0.13404825737265),
(3, 3, 'SEK', 0.1170480482238),
(4, 4, 'GBP', 1.2356357345854),
(5, 5, 'USD', 0.76312576312576),
(6, 6, 'NOK', 0.13703510839477);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `genre`
--

CREATE TABLE IF NOT EXISTS `genre` (
  `genre_id` tinyint(3) unsigned NOT NULL,
  `genre_name` varchar(40) NOT NULL,
  PRIMARY KEY (`genre_id`),
  KEY `genre_name` (`genre_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Data dump for tabellen `genre`
--

INSERT INTO `genre` (`genre_id`, `genre_name`) VALUES
(8, 'Classical'),
(6, 'Country/Folk'),
(3, 'Dance/Electronic'),
(9, 'Entertainment'),
(4, 'HipHop/Rap'),
(7, 'Jazz/Blues'),
(10, 'Kids'),
(5, 'Metal/Hard Rock'),
(12, 'New age'),
(11, 'Other'),
(1, 'Pop/Rock'),
(2, 'Soul/R&B'),
(14, 'Soundtrack'),
(0, 'Unknown'),
(13, 'World/Reggae');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `info_artist`
--

CREATE TABLE IF NOT EXISTS `info_artist` (
  `info_artist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `artist_id` int(10) unsigned NOT NULL,
  `gender` char(1) NOT NULL,
  `artist_real_name` varchar(64) NOT NULL,
  `url_artist_official` varchar(256) NOT NULL,
  `url_fanpage` varchar(192) NOT NULL,
  `url_wikipedia` varchar(192) NOT NULL,
  `url_allmusic` varchar(192) NOT NULL,
  `url_musicbrainz` varchar(192) NOT NULL,
  `url_discogs` varchar(192) NOT NULL,
  `artist_type` char(1) NOT NULL,
  `country_id` int(10) unsigned NOT NULL,
  `year_born` smallint(6) NOT NULL,
  `year_died` smallint(6) NOT NULL,
  `year_start` smallint(6) NOT NULL,
  `year_end` smallint(6) NOT NULL,
  `google_score` int(10) unsigned NOT NULL DEFAULT '0',
  `bing_score` int(10) unsigned NOT NULL DEFAULT '0',
  `info_artist_reliability` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`info_artist_id`),
  KEY `artist_id` (`artist_id`),
  KEY `info_artist_reliability` (`info_artist_reliability`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `intro_text_album`
--

CREATE TABLE IF NOT EXISTS `intro_text_album` (
  `intro_text_album_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `language_code` char(2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `album_simple_id` int(11) NOT NULL,
  `intro_text` text NOT NULL,
  PRIMARY KEY (`intro_text_album_id`),
  KEY `is_default` (`is_default`,`language_code`,`album_simple_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Data dump for tabellen `intro_text_album`
--

INSERT INTO `intro_text_album` (`intro_text_album_id`, `is_default`, `language_code`, `created_date`, `album_simple_id`, `intro_text`) VALUES
(1, 1, 'da', '2013-01-23 22:09:07', 0, '<h2>Guide til den billigste pris på albummet {album_name} af kunstneren {artist_name}</h2>Guide til den billigste pris på albummet <a href="{album_url}">{album_name}</a> af kunstneren <a href="{artist_url}">{artist_name}</a>. Vælg blandt alle musikformater - MP3, WMA, FLAC, streaming, CD, SACD, vinyl, DVD.<br/>Musiksøgemaskinen har sammenlignet mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore - samt musik-/streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio.'),
(2, 1, 'en', '2013-01-23 22:09:07', 0, 'Guide til den billigste pris på albummet <a href="{album_url}">{album_name}</a> af kunstneren <a href="{artist_url}">{artist_name}</a>. Vælg blandt alle musikformater - MP3, WMA, FLAC, streaming, CD, SACD, vinyl, DVD.<br/>Musiksøgemaskinen har sammenlignet mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore - samt musik-/streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio.');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `intro_text_artist`
--

CREATE TABLE IF NOT EXISTS `intro_text_artist` (
  `intro_text_artist_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `language_code` char(2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `artist_id` int(11) NOT NULL,
  `intro_text` text NOT NULL,
  PRIMARY KEY (`intro_text_artist_id`),
  KEY `is_default` (`is_default`,`language_code`,`artist_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Data dump for tabellen `intro_text_artist`
--

INSERT INTO `intro_text_artist` (`intro_text_artist_id`, `is_default`, `language_code`, `created_date`, `artist_id`, `intro_text`) VALUES
(1, 1, 'da', '2013-01-21 21:05:32', 0, '<h2>Guide til billigste priser og største udvalg af <b>{artist_name}</b> albums og sange.</h2>Vælg blandt alle musikformater - Mp3, WMA, FLAC, streaming...CD, SACD , vinyl, DVD... <br/>Og sammenlign udvalg, tilbud, priser blandt mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore...\r\nsamt musik-streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio...'),
(2, 1, 'en', '2013-01-21 21:05:32', 0, 'Guide til billigste priser og største udvalg af <b>{artist_name}</b> albums og sange <br/>Vælg blandt alle musikformater - Mp3, WMA, FLAC, streaming...CD, SACD , vinyl, DVD... <br/>Og sammenlign udvalg, tilbud, priser blandt mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore...\r\nsamt musik-streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio...');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `intro_text_song`
--

CREATE TABLE IF NOT EXISTS `intro_text_song` (
  `intro_text_song_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `language_code` char(2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `song_simple_id` int(11) NOT NULL,
  `intro_text` text NOT NULL,
  PRIMARY KEY (`intro_text_song_id`),
  KEY `is_default` (`is_default`,`language_code`,`song_simple_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Data dump for tabellen `intro_text_song`
--

INSERT INTO `intro_text_song` (`intro_text_song_id`, `is_default`, `language_code`, `created_date`, `song_simple_id`, `intro_text`) VALUES
(1, 1, 'da', '2013-01-24 19:04:41', 0, '<h2>Find den billigste pris på musik, download, streaming, CD, vinyl - for sangen {song_name} af kunstneren {artist_name}</h2>Find den billigste pris på musik, download, streaming, CD, vinyl - for sangen <a href="{song_url}">{song_name}</a> af kunstneren <a href="{artist_url}">{artist_name}</a>. Vælg blandt alle musikformater - MP3, WMA, FLAC, streaming, CD, SACD, vinyl og DVD.<br/>Musiksøgemaskinen har sammenlignet mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore, samt musik-/streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio.'),
(2, 1, 'en', '2013-01-24 19:04:41', 0, 'Find den billigste pris på musik, download, streaming, CD, vinyl - for sangen <a href="{song_url}">{song_name}</a> af kunstneren <a href="{artist_url}">{artist_name}</a>. Vælg blandt alle musikformater - MP3, WMA, FLAC, streaming, CD, SACD, vinyl og DVD.<br/>Musiksøgemaskinen har sammenlignet mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore, samt musik-/streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio.');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `item_base`
--

CREATE TABLE IF NOT EXISTS `item_base` (
  `item_base_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_type` tinyint(1) NOT NULL,
  `artist_id` int(10) unsigned NOT NULL,
  `item_base_name` varchar(256) NOT NULL,
  `record_label_id` int(6) unsigned NOT NULL DEFAULT '0',
  `item_genre_id` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `item_subgenre_ids` varchar(32) NOT NULL DEFAULT '',
  `item_year` smallint(4) unsigned NOT NULL DEFAULT '0',
  `release_date` date NOT NULL DEFAULT '0000-00-00',
  `parent_item` int(11) NOT NULL DEFAULT '0',
  `item_time` int(11) NOT NULL DEFAULT '0',
  `track_number` int(11) NOT NULL DEFAULT '0',
  `child_items` varchar(512) NOT NULL DEFAULT '',
  `item_base_reliability` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_base_id`),
  KEY `artist_id` (`artist_id`),
  KEY `parent_item` (`parent_item`),
  KEY `item_type` (`item_type`),
  KEY `item_base_reliability` (`item_base_reliability`),
  KEY `item_name` (`item_base_name`(255)),
  KEY `release_date` (`release_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `item_base_correction`
--

CREATE TABLE IF NOT EXISTS `item_base_correction` (
  `item_base_correction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `artist_id` int(10) unsigned NOT NULL,
  `item_base_correction_name` varchar(256) NOT NULL,
  `item_base_name` varchar(256) NOT NULL,
  PRIMARY KEY (`item_base_correction_id`),
  KEY `artist_id` (`artist_id`),
  KEY `item_name` (`item_base_name`(255)),
  KEY `item_base_correction_name` (`item_base_correction_name`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `item_price`
--

CREATE TABLE IF NOT EXISTS `item_price` (
  `item_price_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_base_id` int(10) unsigned NOT NULL,
  `item_type` int(11) NOT NULL DEFAULT '0',
  `item_price_name` varchar(256) NOT NULL,
  `record_store_id` int(11) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `media_format_id` int(11) NOT NULL DEFAULT '0',
  `media_type_id` int(11) NOT NULL,
  `item_genre_id` int(11) NOT NULL DEFAULT '0',
  `price_local` decimal(10,2) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `buy_at_url` varchar(2048) NOT NULL,
  `cover_image_url` varchar(2048) NOT NULL DEFAULT '',
  `release_date` date NOT NULL DEFAULT '0000-00-00',
  `item_year` smallint(4) unsigned NOT NULL DEFAULT '0',
  `item_time` int(11) NOT NULL DEFAULT '0',
  `track_number` int(11) NOT NULL DEFAULT '0',
  `item_used` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `parent_item` int(11) NOT NULL DEFAULT '0',
  `child_items` varchar(512) NOT NULL,
  PRIMARY KEY (`item_price_id`),
  KEY `item_base_id` (`item_base_id`),
  KEY `artist_id` (`artist_id`),
  KEY `media_format_id` (`media_format_id`),
  KEY `record_store_id` (`record_store_id`),
  KEY `item_price_name` (`item_price_name`(255)),
  KEY `item_type` (`item_type`),
  KEY `item_used` (`item_used`),
  KEY `timestamp_updated` (`timestamp_updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `media_format`
--

CREATE TABLE IF NOT EXISTS `media_format` (
  `media_format_id` tinyint(3) unsigned NOT NULL,
  `media_format_name` varchar(30) NOT NULL,
  PRIMARY KEY (`media_format_id`),
  KEY `media_format_name` (`media_format_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Data dump for tabellen `media_format`
--

INSERT INTO `media_format` (`media_format_id`, `media_format_name`) VALUES
(2, 'ACC'),
(10, 'Blu-ray'),
(5, 'CD'),
(8, 'DVD'),
(14, 'DVDA'),
(15, 'MC'),
(9, 'Mobile'),
(3, 'MP3'),
(12, 'SACD'),
(13, 'SACDH'),
(6, 'Single'),
(4, 'Stream'),
(0, 'Unknown'),
(7, 'Vinyl'),
(1, 'WMA'),
(11, '_RESERVED_');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `record_store`
--

CREATE TABLE IF NOT EXISTS `record_store` (
  `record_store_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `record_store_name` varchar(128) NOT NULL,
  `record_store_url` varchar(80) NOT NULL,
  `country_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `use_affiliate` smallint(6) NOT NULL,
  `affiliate_link` varchar(255) NOT NULL,
  `affiliate_encode_times` int(11) NOT NULL,
  `record_store_reliability` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`record_store_id`),
  KEY `record_store_name` (`record_store_name`),
  KEY `record_store_reliability` (`record_store_reliability`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `unknown_genre`
--

CREATE TABLE IF NOT EXISTS `unknown_genre` (
  `unknown_genre_id` int(11) NOT NULL AUTO_INCREMENT,
  `genre_name` varchar(64) DEFAULT NULL,
  `record_store_id` int(11) DEFAULT NULL,
  `buy_at_url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`unknown_genre_id`),
  UNIQUE KEY `genre_name` (`genre_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `unknown_media_format`
--

CREATE TABLE IF NOT EXISTS `unknown_media_format` (
  `unknown_media_format_id` int(11) NOT NULL AUTO_INCREMENT,
  `media_format_name` varchar(64) DEFAULT NULL,
  `record_store_id` int(11) DEFAULT NULL,
  `buy_at_url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`unknown_media_format_id`),
  UNIQUE KEY `media_format_name` (`media_format_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `unknown_media_type`
--

CREATE TABLE IF NOT EXISTS `unknown_media_type` (
  `unknown_media_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `media_type_name` varchar(64) DEFAULT NULL,
  `record_store_id` int(11) DEFAULT NULL,
  `buy_at_url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`unknown_media_type_id`),
  UNIQUE KEY `media_type_name` (`media_type_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `wiki_text_artist`
--

CREATE TABLE IF NOT EXISTS `wiki_text_artist` (
  `wiki_text_artist_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_code` char(2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `artist_id` int(11) NOT NULL,
  `wiki_text` text NOT NULL,
  PRIMARY KEY (`wiki_text_artist_id`),
  KEY `is_default` (`language_code`,`artist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `artist_various`
--

CREATE TABLE IF NOT EXISTS `artist_various` (
  `artist_various_id` int(10) NOT NULL AUTO_INCREMENT,
  `artist_various_name` varchar(128) NOT NULL,
  PRIMARY KEY (`artist_various_id`),
  KEY `artist_various_name` (`artist_various_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;


--
-- Table structure for table `artist_lookup`
--

CREATE TABLE IF NOT EXISTS `artist_lookup` (
  `artist_lookup_id` int(11) NOT NULL AUTO_INCREMENT,
  `artist_id` int(11) NOT NULL,
  `artist_name` varchar(256) NOT NULL,
  `artist_name_alternative_spelling` varchar(256) NULL,
  PRIMARY KEY (`artist_lookup_id`),
  KEY `artist_id` (`artist_id`),
  FULLTEXT KEY `artist_lookup` (`artist_name`,`artist_name_alternative_spelling`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `item_base_lookup`
--

CREATE TABLE IF NOT EXISTS `item_base_lookup` (
  `item_base_lookup_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_base_id` int(11) NOT NULL,
  `item_type` int(11) NOT NULL,
  `item_base_name` varchar(256) NOT NULL,
  `item_base_name_alternative_spelling` varchar(256) NULL,
  PRIMARY KEY (`item_base_lookup_id`),
  KEY `item_base_id` (`item_base_id`),
  KEY `item_type` (`item_type`),
  FULLTEXT KEY `item_base_lookup` (`item_base_name`,`item_base_name_alternative_spelling`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;




CREATE TABLE `job_status` (
  `job_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  PRIMARY KEY (`job_status_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


--
-- Dumping data for table `job_status`
--
INSERT INTO `job_status` VALUES (1,'Ready for Queuing'),(2,'Running'),(3,'Disabled');


--
-- Table structure for table `job`
--

CREATE TABLE `job` (
  `job_id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_run` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `record_store_id` int(11) NOT NULL,
  `job_run_interval_minutes` int(11) NOT NULL DEFAULT '1440',
  `job_force_restart` int(11) NOT NULL DEFAULT '0',
  `job_status_id` int(11) NOT NULL,
  `estimated_runtime` int(11) NOT NULL,
  `script_path` varchar(255) CHARACTER SET latin1 NOT NULL,
  `parameters` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `job_priority` int(11) NOT NULL,
  `items_mined` int(11) NOT NULL DEFAULT '0',
  `nav_current_state_index` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `nav_last_state_index` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `host_name` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `job_approved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`job_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


--
-- Dumping data for table `job`
--

INSERT INTO `job` VALUES (3,'MovieMusic (US)','2012-06-10 17:44:11','2013-05-06 11:10:51',3,43200,0,1,1,'scripts/airplay/MoviemusicCom/MoviemusicCom.php','',2,2021,'29','29','robots.airplaymusic.dk',1),(4,'Øresneglen (DK)','2012-06-22 16:58:44','2013-05-06 11:05:44',2,43200,0,1,1,'scripts/airplay/SneglenDk/SneglenDk2.php','',2,685,'4','4','robots.airplaymusic.dk',1),(10,'AmazonCoUk A','2012-06-22 18:18:58','2013-04-10 09:50:11',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=a end=a',2,0,'','','',0),(6,'VoxHallCom','2012-06-22 17:00:20','2013-03-03 21:10:26',17,86400,0,3,1,'scripts/airplay/VoxHallCom/VoxHallCom3.php','',2,0,'','','',0),(7,'Bilka (DK)','2012-06-22 17:29:16','2013-05-06 11:05:07',1,43200,0,1,1,'scripts/airplay/BilkaDk/BilkaDk.php','',2,3010,'21','21','robots.airplaymusic.dk',1),(9,'CDJAZZ.COM (DK)','2012-06-22 18:00:52','2013-04-26 22:20:07',25,43200,0,1,1,'scripts/airplay/CdJazzCom/CdJazzCom.php','',2,0,'','','',1),(11,'DkCdwowCom','2012-06-23 12:46:11','2013-04-10 13:35:17',11,43200,0,3,1,'scripts/airplay/DkCdwowCom/DkCdwowCom.php','',2,0,'','','',0),(12,'DkCdwowCom New Releases','2012-06-23 12:46:32','2013-04-17 13:36:25',11,10080,0,3,1,'scripts/airplay/DkCdwowCom/DkCdwowComNewReleases.php','',2,0,'','','',0),(14,'AmazonCoUk B','2012-06-24 20:26:09','2013-04-10 12:10:20',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=b end=b',2,0,'','','',0),(15,'AmazonCoUk C','2012-06-24 20:27:01','2013-04-10 12:10:11',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=c end=c',2,0,'','','',0),(16,'AmazonCoUk D','2012-06-24 20:27:37','2013-04-12 19:30:07',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=d end=d',2,0,'','','',0),(17,'AmazonCoUk E','2012-06-24 20:27:44','2013-04-12 19:30:14',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=e end=e',2,0,'','','',0),(18,'AmazonCoUk F','2012-06-24 20:27:50','2013-04-12 20:00:13',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=f end=f',2,0,'','','',0),(19,'AmazonCoUk G','2012-06-24 20:27:57','2013-04-12 20:00:20',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=g end=g',2,0,'','','',0),(20,'AmazonCoUk H','2012-06-24 20:28:02','2013-04-12 20:00:28',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=h end=h',2,0,'','','',0),(21,'AmazonCoUk I','2012-06-24 20:28:10','2013-04-12 20:00:36',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=i end=i',2,0,'','','',0),(22,'AmazonCoUk J','2012-06-24 20:28:18','2013-04-12 22:25:20',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=j end=j',2,0,'','','',0),(23,'AmazonCoUk K','2012-06-24 20:29:53','2013-04-12 20:40:10',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=k end=k',2,0,'','','',0),(24,'AmazonCoUk L','2012-06-24 20:29:59','2013-04-13 10:45:59',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=l end=l',2,0,'','','',0),(25,'AmazonCoUk M','2012-06-24 20:30:06','2013-04-13 10:45:53',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=m end=m',2,0,'','','',0),(26,'AmazonCoUk N','2012-06-24 20:30:11','2013-04-13 14:10:08',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=n end=n',2,0,'','','',0),(27,'AmazonCoUk O','2012-06-24 20:30:25','2013-04-13 10:45:21',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=o end=o',2,0,'','','',0),(28,'AmazonCoUk P','2012-06-24 20:30:31','2013-04-13 10:45:27',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=p end=p',2,0,'','','',0),(29,'AmazonCoUk Q','2012-06-24 20:34:43','2013-04-13 10:45:34',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=q end=q',2,19421,'1340','1340','robots.airplaymusic.dk',0),(30,'AmazonCoUk R','2012-06-24 20:34:48','2013-04-13 10:45:15',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=r end=r',2,215915,'16659','16659','robots.airplaymusic.dk',0),(31,'AmazonCoUk S','2012-06-24 20:34:54','2013-04-13 12:40:13',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=s end=s',2,385312,'41604','41604','robots.airplaymusic.dk',0),(32,'AmazonCoUk T','2012-06-24 20:35:01','2013-04-13 10:46:06',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=t end=t',2,189877,'18734','18734','robots.airplaymusic.dk',0),(33,'AmazonCoUk Y','2012-06-24 20:35:12','2013-04-13 15:20:15',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=y end=y',2,18337,'3289','3289','robots.airplaymusic.dk',0),(34,'AmazonCoUk U','2012-06-24 20:35:19','2013-04-13 11:30:13',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=u end=u',2,17419,'3184','3184','robots.airplaymusic.dk',0),(35,'AmazonCoUk Z','2012-06-24 20:35:28','2013-04-13 16:30:15',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=z end=z',2,16433,'4730','4730','robots.airplaymusic.dk',0),(36,'AmazonCoUk X','2012-06-24 20:35:35','2013-04-13 15:00:15',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=x end=x',2,1880,'1006','1006','robots.airplaymusic.dk',0),(39,'AmazonCoUk #','2012-06-24 20:36:03','2013-04-13 10:45:08',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=# end=#',2,0,'','','',0),(40,'Gucca (DK)','2012-07-03 20:08:27','2013-04-24 09:30:07',6,43200,0,1,1,'scripts/airplay/GuccaDk/GuccaDk.php','',2,11119,'29','57','robots.airplaymusic.dk',1),(41,'musikkonline.no (NO)','2012-07-03 21:20:55','2013-05-05 07:41:17',7,43200,0,1,1,'scripts/airplay/MusikkonlineNo/MusikkonlineNo.php','',2,179446,'34','34','robots.airplaymusic.dk',1),(43,'UndergroundHipHop','2012-07-08 12:42:54','2012-11-06 09:15:01',28,10080,0,3,1,'scripts/airplay/UndergroundHipHop/UndergroundHipHop.php','',2,1159,'0','-1','mlpc',1),(44,'DSBDk','2012-07-08 12:52:45','2013-02-15 09:35:08',18,43200,0,3,1,'scripts/airplay/DSBDk/DSBDk.php','',2,0,'','','',0),(45,'Imusic (DK)','2012-07-08 15:23:45','2013-04-21 13:45:11',4,43200,0,3,1,'scripts/airplay/ImusicDk/ImusicDk.php','',2,0,'','','',0),(46,'Megastore (SE)','2012-07-08 19:00:17','2013-04-24 10:55:08',20,43200,0,1,1,'scripts/airplay/MegastoreSe/MegastoreSe.php','',2,301209,'21','21','robots.airplaymusic.dk',1),(67,'CdonDk D','2012-08-12 22:57:36','2013-04-10 07:41:14',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=d end=d',2,175311,'21366','21366','robots.airplaymusic.dk',1),(50,'Brasilitino (DK)','2012-07-17 12:56:18','2013-05-06 11:05:56',30,10080,0,2,1,'scripts/airplay/BrasilitinoDK/BrasilitinoDK.php','',2,0,'','','',1),(48,'Gaffa (DK)','2012-07-09 20:10:59','2013-05-06 11:05:50',27,43200,0,1,1,'scripts/airplay/GaffaDk/GaffaDk.php','',2,7130,'51','76','robots.airplaymusic.dk',1),(49,'Highwaymusic (DK)','2012-07-09 20:14:07','2013-04-24 11:50:16',10,43200,0,1,1,'scripts/airplay/HighwaymusicDk/HighwaymusicDk.php','',2,0,'0','-1','robots.airplaymusic.dk',1),(56,'CdMarket blues klassisk','2012-07-26 12:15:42','2013-04-20 13:25:22',31,43200,0,3,1,'scripts/airplay/CdMarket/CdMarket.php','start=Blues end=klassisk',2,0,'','','',0),(51,'Stereo Studio (DK)','2012-07-21 12:33:23','2013-05-04 13:16:14',12,10080,0,2,1,'scripts/airplay/StereostudioDk/StereostudioDk.php','start=cd1 end=BR9',2,0,'22','22','robots.airplaymusic.dk',1),(52,'Stereo Studio (DK) NewReleases CD','2012-07-21 12:48:58','2013-05-07 13:31:21',12,10080,0,1,1,'scripts/airplay/StereostudioDk/StereostudioDk_NewReleases.php','',2,118,'0','-1','robots.airplaymusic.dk',1),(53,'Gaffa (DK) NewReleases','2012-07-21 13:18:41','2013-05-06 11:05:19',27,10080,0,1,1,'scripts/airplay/GaffaDk/GaffaDkNewReleases.php','',2,516,'1','1','robots.airplaymusic.dk',1),(54,'PlusPolitikenDk','2012-07-22 18:09:49','2012-10-17 20:45:00',9,43200,0,3,1,'scripts/airplay/PlusPolitikenDk/PlusPolitikenDk.php','',2,0,'','','',1),(55,'Øresneglen (DK) NewReleases','2012-07-23 18:43:10','2013-05-06 11:05:13',2,10080,0,2,1,'scripts/airplay/SneglenDk/SneglenDkNewReleases.php','',2,0,'','','',1),(57,'CdMarket (EE) country elektronisk','2012-07-26 12:16:01','2013-04-20 13:40:22',31,43200,0,3,1,'scripts/airplay/CdMarket/CdMarket.php','start=country end=elektronisk-musik',2,0,'','','',0),(58,'CdMarket (EE) estisk hip hop','2012-07-26 12:16:38','2013-04-20 13:30:15',31,43200,0,3,1,'scripts/airplay/CdMarket/CdMarket.php','start=estisk-musik end=hip-hop-rap',2,0,'','','',0),(59,'CdMarket (EE) helligdags karaoke','2012-07-26 12:17:01','2013-04-20 13:35:22',31,43200,0,3,1,'scripts/airplay/CdMarket/CdMarket.php','start=helligdags-musik end=karaoke',2,0,'','','',0),(60,'CdMarket (EE) latin metal','2012-07-26 12:17:19','2013-04-20 13:35:14',31,43200,0,3,1,'scripts/airplay/CdMarket/CdMarket.php','start=latin-musik end=metal',2,0,'','','',0),(61,'CdMarket (EE) r-b Rock','2012-07-26 12:17:51','2013-04-20 13:25:14',31,43200,0,3,1,'scripts/airplay/CdMarket/CdMarket.php','start=r-b end=rock',2,0,'','','',0),(62,'CdMarket (EE) reggae religios-musik','2012-07-26 12:18:11','2013-04-20 13:40:14',31,43200,0,3,1,'scripts/airplay/CdMarket/CdMarket.php','start=reggae end=religios-musik',2,0,'','','',0),(63,'CdMarket (EE) filmmusik verdensmusik','2012-07-26 12:18:28','2013-04-20 13:30:24',31,43200,0,3,1,'scripts/airplay/CdMarket/CdMarket.php','start=filmmusik end=verdensmusik',2,0,'','','',0),(66,'CdonDk C','2012-08-12 22:57:17','2013-04-10 07:41:08',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=c end=c',2,94013,'24061','24061','robots.airplaymusic.dk',1),(64,'CdonDk A','2012-08-12 21:40:37','2013-04-10 07:40:38',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=a end=a',2,1217366,'22066','22066','robots.airplaymusic.dk',1),(65,'CdonDk B','2012-08-12 22:56:56','2013-04-10 07:40:44',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=b end=b',2,28550,'14020','27446','robots.airplaymusic.dk',1),(68,'CdonDk E','2012-08-12 22:57:46','2013-04-10 07:45:08',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=e end=e',2,107010,'10860','10860','robots.airplaymusic.dk',1),(69,'CdonDk F','2012-08-12 22:57:58','2013-04-10 07:45:14',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=f end=f',2,52453,'13602','13602','robots.airplaymusic.dk',1),(70,'CdonDk G','2012-08-12 22:58:07','2013-04-11 07:10:14',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=g end=g',2,54574,'16500','16500','robots.airplaymusic.dk',1),(71,'CdonDk H','2012-08-12 22:58:19','2013-04-11 07:10:08',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=h end=h',2,55920,'15149','15149','robots.airplaymusic.dk',1),(72,'CdonDk I','2012-08-12 22:58:30','2013-04-11 07:20:11',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=i end=i',2,15711,'6591','6591','robots.airplaymusic.dk',1),(73,'CdonDk J','2012-08-12 22:58:41','2013-04-11 07:20:17',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=j end=j',2,0,'','','',0),(74,'CdonDk K','2012-08-12 22:58:57','2013-04-11 07:20:23',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=k end=k',2,38730,'19338','19338','robots.airplaymusic.dk',1),(75,'CdonDk L','2012-08-12 22:59:11','2013-04-11 07:20:31',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=l end=l',2,473194,'15641','15641','robots.airplaymusic.dk',1),(76,'CdonDk M','2012-08-12 22:59:22','2013-04-11 07:20:37',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=m end=m',2,27605,'26101','30228','robots.airplaymusic.dk',1),(77,'CdonDk N','2012-08-12 22:59:33','2013-04-11 07:20:44',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=n end=n',2,29644,'11045','11045','robots.airplaymusic.dk',1),(78,'CdonDk O','2012-08-12 22:59:42','2013-04-10 07:05:07',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=o end=o',2,1371439,'7300','7300','robots.airplaymusic.dk',1),(79,'CdonDk P','2012-08-12 22:59:52','2013-04-11 07:40:20',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=p end=p',2,28290,'7491','20439','robots.airplaymusic.dk',1),(80,'CdonDk Q','2012-08-12 23:00:02','2013-04-12 10:35:56',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=q end=q',2,102235,'1340','1340','robots.airplaymusic.dk',1),(81,'CdonDk R','2012-08-12 23:00:11','2013-04-12 10:35:20',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=r end=r',2,66161,'16659','16659','robots.airplaymusic.dk',1),(82,'CdonDk S','2012-08-12 23:00:20','2013-04-12 10:35:49',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=s end=s',2,127966,'41604','41604','robots.airplaymusic.dk',1),(83,'CdonDk T','2012-08-12 23:00:30','2013-04-12 10:35:27',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=t end=t',2,27470,'14695','18734','robots.airplaymusic.dk',1),(84,'CdonDk U','2012-08-12 23:00:39','2013-04-14 12:20:07',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=u end=u',2,7346,'3184','3184','robots.airplaymusic.dk',1),(85,'CdonDk V','2012-08-12 23:00:51','2013-04-12 10:35:08',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=v end=v',2,27230,'1879','9003','robots.airplaymusic.dk',1),(86,'CdonDk W','2012-08-12 23:00:59','2013-04-12 10:35:43',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=w end=w',2,39052,'8249','8249','robots.airplaymusic.dk',1),(87,'CdonDk X','2012-08-12 23:01:57','2013-04-12 10:35:36',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=x end=x',2,884,'1006','1006','robots.airplaymusic.dk',1),(88,'CdonDk Z','2012-08-12 23:02:16','2013-04-12 13:35:14',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=z end=z',2,5810,'4730','4730','robots.airplaymusic.dk',1),(89,'CdonDk Y','2012-08-12 23:02:36','2013-04-12 12:50:16',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=y end=y',2,7713,'3289','3289','robots.airplaymusic.dk',1),(90,'CdonDk #','2012-08-12 23:03:00','2013-04-10 07:40:32',13,43200,0,3,1,'scripts/airplay/CdonDk/CdonDk.php','start=# end=#',2,67280,'8400','8400','robots.airplaymusic.dk',1),(91,'AmazonCoUk V','2012-08-12 23:04:59','2013-04-13 10:45:41',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=v end=v',2,100453,'9003','9003','robots.airplaymusic.dk',0),(92,'AmazonCoUk W','2012-08-12 23:05:09','2013-04-13 14:20:11',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUk.php','start=w end=w',2,118257,'8249','8249','robots.airplaymusic.dk',0),(97,'ArtistInfoMusicBrainz D','2012-09-09 12:20:46','2012-09-11 19:50:22',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=d end=d',2,0,'','','',1),(94,'ArtistInfoMusicBrainz A','2012-09-09 12:20:23','2012-09-10 17:00:09',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=a end=a',2,25,'33795','143194','mlpc',1),(96,'ArtistInfoMusicBrainz C','2012-09-09 12:20:38','2012-09-10 17:25:51',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=c end=c',2,7,'3449','137882','ubuntu1204',1),(93,'ArtistInfoMusicBrainz  #','2012-09-06 17:51:10','2013-01-15 22:20:11',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=# end=#',2,0,'','','',1),(95,'ArtistInfoMusicBrainz B','2012-09-09 12:20:29','2012-09-12 13:15:13',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=b end=b',2,3370,'75470','142715','ubuntu1204',1),(98,'ArtistInfoMusicBrainz E','2012-09-09 12:20:53','2012-09-11 19:50:15',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=e end=e',2,48,'70042','70042','mlpc',1),(99,'ArtistInfoMusicBrainz F','2012-09-09 12:21:00','2012-09-12 14:40:16',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=f end=f',2,0,'','','',1),(100,'ArtistInfoMusicBrainz G','2012-09-09 12:21:10','2012-09-10 19:15:32',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=g end=g',2,4937,'83815','83815','ubuntu1204',1),(101,'ArtistInfoMusicBrainz H','2012-09-09 12:21:18','2012-09-12 14:35:07',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=h end=h',2,21,'630','71527','ip-10-227-145-134',1),(102,'ArtistInfoMusicBrainz I','2012-09-09 12:21:26','2012-09-09 15:05:31',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=i end=i',2,64,'32274','32274','mlpc',1),(103,'ArtistInfoMusicBrainz J','2012-09-09 12:21:36','2012-09-09 15:05:19',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=j end=j',2,1568,'79660','119157','mlpc',1),(104,'ArtistInfoMusicBrainz K','2012-09-09 12:21:46','2012-09-09 15:05:41',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=k end=k',2,1756,'71401','71401','mlpc',1),(105,'ArtistInfoMusicBrainz L','2012-09-09 12:21:55','2012-09-12 13:20:13',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=l end=l',2,0,'','','',1),(106,'ArtistInfoMusicBrainz M','2012-09-09 12:22:14','2012-09-12 14:55:11',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=m end=m',2,0,'','','',1),(107,'ArtistInfoMusicBrainz N','2012-09-09 12:22:20','2012-09-10 20:35:12',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=n end=n',2,5939,'50691','50691','ubuntu1204',1),(108,'ArtistInfoMusicBrainz O','2012-09-09 12:22:29','2012-09-11 19:50:31',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=o end=o',2,21,'38475','38475','mlpc',1),(109,'ArtistInfoMusicBrainz P','2012-09-09 12:22:37','2012-09-11 05:55:09',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=p end=p',2,2,'8459','91507','mlpc',1),(110,'ArtistInfoMusicBrainz Q','2012-09-09 12:22:48','2012-09-13 04:20:11',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=q end=q',2,7,'4422','4422','ubuntu1204',1),(111,'ArtistInfoBing B','2012-09-09 12:22:58','2013-04-22 19:40:11',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=b end=b',2,3,'3','112608','robots.airplaymusic.dk',1),(112,'ArtistInfoMusicBrainz S','2012-09-09 12:23:21','2012-09-09 12:50:19',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=s end=s',2,623,'51854','172987','ubuntu1204',1),(113,'ArtistInfoMusicBrainz T','2012-09-09 12:23:30','2012-09-09 12:50:11',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=t end=t',2,896,'47904','144275','ubuntu1204',1),(114,'ArtistInfoMusicBrainz U','2012-09-09 12:23:39','2012-09-09 12:45:17',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=u end=u',2,24,'13929','13929','ubuntu1204',1),(115,'ArtistInfoMusicBrainz V','2012-09-09 12:23:50','2012-09-09 12:45:10',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=v end=v',2,114,'41563','45500','ubuntu1204',1),(116,'ArtistInfoMusicBrainz W','2012-09-09 12:23:59','2012-09-12 13:10:13',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=w end=w',2,2,'8','8','ubuntu1204',1),(117,'ArtistInfoMusicBrainz X','2012-09-09 12:24:08','2012-09-12 13:05:12',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=x end=x',2,0,'0','0','ubuntu1204',1),(118,'ArtistInfoMusicBrainz Y','2012-09-09 12:24:18','2012-09-12 13:00:13',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=y end=y',2,0,'0','0','ubuntu1204',1),(119,'ArtistInfoMusicBrainz Z','2012-09-09 12:24:28','2012-09-12 12:55:12',0,43200,0,3,1,'scripts/airplay/ArtistInfoMusicBrainz/ArtistInfoMusicBrainz.php','start=z end=z',2,0,'1','1','ubuntu1204',1),(120,'ArtistInfoBing #','2012-09-09 14:43:36','2013-04-22 19:30:31',0,30240,0,3,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=# end=#',2,3,'3','19566','robots.airplaymusic.dk',1),(121,'ArtistInfoBing A','2012-09-09 14:43:46','2013-04-22 19:30:21',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=a end=a',2,13,'13','121277','robots.airplaymusic.dk',1),(123,'ArtistInfoBing C','2012-09-09 14:53:05','2013-04-22 19:45:12',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=c end=c',2,29,'29','111143','robots.airplaymusic.dk',1),(124,'ArtistInfoBing D','2012-09-09 14:55:19','2013-04-22 19:40:21',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=D end=D',2,17,'17','115495','robots.airplaymusic.dk',1),(125,'ArtistInfoBing E','2012-09-09 14:55:25','2013-04-27 19:55:15',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=e end=e',2,58,'58','64464','robots.airplaymusic.dk',1),(126,'ArtistInfoBing F','2012-09-09 14:55:48','2013-05-07 14:16:08',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=f end=f',2,12,'12','64711','robots.airplaymusic.dk',1),(127,'ArtistInfoBing G','2012-09-09 14:55:58','2013-04-22 20:05:11',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=g end=g',2,13,'13','71182','robots.airplaymusic.dk',1),(128,'ArtistInfoBing H','2012-09-09 14:56:07','2013-04-22 19:45:22',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=h end=h',2,119,'119','59108','robots.airplaymusic.dk',1),(129,'ArtistInfoBing I','2012-09-09 14:56:15','2013-04-27 19:55:21',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=i end=i',2,132,'132','31145','robots.airplaymusic.dk',1),(130,'ArtistInfoBing J','2012-09-09 14:58:56','2013-04-22 19:55:16',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=j end=j',2,89,'89','103018','robots.airplaymusic.dk',1),(131,'ArtistInfoBing K','2012-09-09 14:59:05','2013-04-22 19:50:11',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=k end=k',2,102,'102','60347','robots.airplaymusic.dk',1),(132,'ArtistInfoBing L','2012-09-09 14:59:15','2013-04-27 19:55:27',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=l end=l',2,2,'2','95010','robots.airplaymusic.dk',1),(133,'ArtistInfoBing M','2012-09-09 14:59:55','2013-04-22 20:00:11',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=m end=m',2,29,'29','144795','robots.airplaymusic.dk',1),(134,'ArtistInfoBing N','2012-09-09 15:00:17','2013-04-23 21:00:09',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=n end=n',2,8,'8','44354','robots.airplaymusic.dk',1),(135,'ArtistInfoBing O','2012-09-09 15:00:24','2013-05-06 14:31:08',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=o end=o',2,1,'1','36886','robots.airplaymusic.dk',1),(136,'ArtistInfoBing P','2012-09-09 15:00:31','2013-05-06 14:11:08',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=p end=p',2,45,'45','87396','robots.airplaymusic.dk',1),(137,'ArtistInfoBing Q','2012-09-09 15:02:27','2013-05-06 14:50:12',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=q end=q',2,92,'92','4621','robots.airplaymusic.dk',1),(138,'ArtistInfoBing R','2012-09-09 15:02:34','2013-05-06 14:26:08',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=r end=r',2,39,'39','96296','robots.airplaymusic.dk',1),(139,'ArtistInfoBing S','2012-09-09 15:02:52','2013-05-06 14:45:08',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=s end=s',2,273,'273','170264','robots.airplaymusic.dk',1),(140,'ArtistInfoBing T','2012-09-09 15:03:00','2013-05-06 14:21:10',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=t end=t',2,36,'36','143680','robots.airplaymusic.dk',1),(141,'ArtistInfoBing U','2012-09-09 15:03:56','2013-05-06 14:46:08',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=u end=u',2,21,'21','14047','robots.airplaymusic.dk',1),(142,'ArtistInfoBing V','2012-09-09 15:04:04','2013-05-06 14:16:09',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=v end=v',2,127,'127','41675','robots.airplaymusic.dk',1),(143,'ArtistInfoBing W','2012-09-09 15:04:12','2013-05-06 14:45:14',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=w end=w',2,0,'0','40800','robots.airplaymusic.dk',1),(144,'ArtistInfoBing X','2012-09-09 15:04:21','2013-05-06 14:35:07',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=x end=x',2,1,'1','3016','robots.airplaymusic.dk',1),(147,'ArtistInfoBing Y','2012-11-04 00:08:40','2013-05-06 14:36:08',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=y end=y',2,87,'87','13446','robots.airplaymusic.dk',1),(148,'ArtistInfoBing Z','2012-11-04 00:08:48','2013-05-06 14:40:07',0,30240,0,1,1,'scripts/airplay/ArtistInfoBing/ArtistInfoBing.php','start=z end=z',2,61,'61','10989','robots.airplaymusic.dk',1),(149,'vinylmusix.com (DK)','2013-01-04 14:02:48','2013-05-06 11:05:31',0,10080,0,1,1,'scripts/airplay/VinylmusixCom/VinylmusixCom.php','start=ForeignA end=MaxiZ',2,2195,'105','105','robots.airplaymusic.dk',1),(154,'Clemens Antikvariat (DK)','2013-04-03 07:52:33','2013-05-06 11:01:21',0,10080,0,3,1,'scripts/airplay/ClemensAntikvariatDk/ClemensAntikvariatDk.php','start=Start end=End',2,2489,'0','-1','robots.airplaymusic.dk',1),(150,'Run For Cover (DK)','2013-02-25 18:37:36','2013-05-06 11:06:02',0,10080,0,1,1,'scripts/airplay/RunForCoverDk/RunForCoverDk.php','',2,737,'0','-1','robots.airplaymusic.dk',1),(151,'Vinylpladen (DK)','2013-02-25 21:14:20','2013-05-06 11:05:25',0,10080,0,1,1,'scripts/airplay/VinylPladenDk/VinylPladenDk.php','',2,815,'0','-1','robots.airplaymusic.dk',1),(152,'Stereo Studio (DK) NewReleases Vinyl','2013-03-05 14:04:21','2013-05-07 14:26:21',0,10080,0,1,1,'scripts/airplay/StereostudioDk/StereostudioDk_NewReleasesVinyl.php','',2,58,'0','-1','robots.airplaymusic.dk',1),(157,'AmazonCoUk B MP3','2013-04-17 16:58:51','2013-04-18 13:20:13',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=b end=b',2,0,'','','',0),(155,'Vinyl Collector (DK)','2013-04-05 12:14:42','2013-05-06 11:05:38',0,10080,0,1,1,'scripts/airplay/VinylcollectorDk/VinylcollectorDk.php','start=Blues end=Samlinger',2,2552,'20','20','robots.airplaymusic.dk',1),(153,'shop2download (DK)','2013-03-25 12:35:42','2013-04-05 10:10:17',0,43200,0,3,1,'scripts/airplay/Shop2DownloadCom/Shop2DownloadCom.php','start=Alternative end=World',2,0,'','','',0),(156,'AmazonCoUk A MP3','2013-04-16 19:15:29','2013-04-18 13:20:07',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=a end=a',2,0,'','','',0),(158,'AmazonCoUk C MP3','2013-04-17 16:58:59','2013-04-18 13:20:19',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=c end=c',2,0,'','','',0),(159,'AmazonCoUk F MP3','2013-04-17 16:59:06','2013-04-18 14:20:53',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=f end=f',2,0,'','','',0),(160,'AmazonCoUk E MP3','2013-04-17 16:59:13','2013-04-18 14:20:47',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=e end=e',2,0,'','','',0),(188,'CdonDk D MP3','2013-04-28 19:30:29','2013-04-28 19:35:07',0,43200,0,3,1,'scripts/airplay/CdonDk/CdonDkMP3.php','start=d end=d',2,0,'','','',1),(161,'AmazonCoUk D MP3','2013-04-18 08:14:54','2013-04-18 13:20:25',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=d end=d',2,0,'','','',0),(162,'AmazonCoUk G MP3','2013-04-18 08:15:04','2013-04-18 14:20:59',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=g end=g',2,0,'','','',0),(163,'AmazonCoUk H MP3','2013-04-18 08:15:13','2013-04-18 14:25:12',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=h end=h',2,0,'','','',0),(164,'AmazonCoUk I MP3','2013-04-18 08:15:25','2013-04-18 14:25:18',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=i end=i',2,0,'','','',0),(165,'AmazonCoUk J MP3','2013-04-18 08:15:36','2013-04-18 15:25:14',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=j end=j',2,0,'','','',0),(166,'AmazonCoUk K MP3','2013-04-18 08:15:46','2013-04-18 15:25:42',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=k end=k',2,0,'','','',0),(167,'AmazonCoUk L MP3','2013-04-18 08:15:55','2013-04-18 15:25:32',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=l end=l',2,0,'','','',0),(168,'AmazonCoUk M MP3','2013-04-18 08:16:14','2013-04-18 15:25:26',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=m end=m',2,0,'','','',0),(169,'AmazonCoUk N MP3','2013-04-18 08:16:23','2013-04-20 19:25:13',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=n end=n',2,0,'','','',0),(189,'CdonDk E MP3','2013-04-28 19:30:36','2013-04-28 19:35:13',0,43200,0,3,1,'scripts/airplay/CdonDk/CdonDkMP3.php','start=e end=e',2,0,'','','',1),(170,'CdonDk A MP3','2013-04-18 19:32:05','2013-04-19 15:05:12',0,43200,0,3,1,'scripts/airplay/CdonDk/CdonDkMP3.php','start=a end=a',2,0,'','','',1),(173,'AmazonCoUk O MP3','2013-04-20 19:27:27','2013-04-21 15:15:14',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=o end=o',2,0,'','','',0),(174,'AmazonCoUk P MP3','2013-04-20 19:27:38','2013-04-21 15:15:22',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=p end=p',2,0,'','','',0),(175,'AmazonCoUk Q MP3','2013-04-20 19:27:45','2013-04-21 15:15:29',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=q end=q',2,257414,'2083','2083','robots.airplaymusic.dk',0),(176,'AmazonCoUk R MP3','2013-04-20 19:27:56','2013-04-21 15:15:35',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=r end=r',2,0,'','','',0),(177,'AmazonCoUk S MP3','2013-04-20 19:28:03','2013-04-21 17:00:19',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=s end=s',2,0,'','','',0),(178,'AmazonCoUk T MP3','2013-04-20 19:28:12','2013-04-22 03:05:21',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=t end=t',2,0,'','','',0),(179,'AmazonCoUk U MP3','2013-04-20 19:28:24','2013-04-28 09:00:07',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=u end=u',2,0,'','','',0),(180,'AmazonCoUk V MP3','2013-04-20 19:28:34','2013-04-28 09:00:14',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=v end=v',2,0,'','','',0),(181,'AmazonCoUk W MP3','2013-04-20 19:28:41','2013-04-28 15:45:08',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=w end=w',2,0,'','','',0),(182,'AmazonCoUk X MP3','2013-04-20 19:28:51','2013-04-28 20:45:15',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=x end=x',2,0,'','','',0),(183,'AmazonCoUk Y MP3','2013-04-20 19:28:59','0000-00-00 00:00:00',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=y end=y',2,0,'','','',0),(184,'AmazonCoUk Z MP3','2013-04-20 19:29:10','0000-00-00 00:00:00',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=z end=z',2,0,'','','',0),(185,'AmazonCoUk # MP3','2013-04-20 19:29:23','2013-04-28 20:45:21',24,43200,0,3,1,'scripts/airplay/AmazonCoUk/AmazonCoUkMP3.php','start=# end=#',2,0,'','','',0),(187,'CdonDk C MP3','2013-04-28 19:30:20','2013-04-28 19:35:19',0,43200,0,3,1,'scripts/airplay/CdonDk/CdonDkMP3.php','start=c end=c',2,0,'','','',1),(186,'CdonDk B MP3','2013-04-28 19:30:13','2013-04-28 19:35:25',0,43200,0,3,1,'scripts/airplay/CdonDk/CdonDkMP3.php','start=b end=b',2,0,'','','',1),(190,'CdonDk F MP3','2013-04-28 19:30:42','2013-04-28 19:35:31',0,43200,0,3,1,'scripts/airplay/CdonDk/CdonDkMP3.php','start=f end=f',2,0,'','','',1),(191,'CdonDk G MP3','2013-04-28 19:31:01','2013-04-28 19:35:38',0,43200,0,3,1,'scripts/airplay/CdonDk/CdonDkMP3.php','start=g end=g',2,0,'','','',1),(192,'CdonDk H MP3','2013-04-28 19:31:09','2013-04-28 20:35:50',0,43200,0,3,1,'scripts/airplay/CdonDk/CdonDkMP3.php','start=h end=h',2,0,'','','',1),(193,'CdonDk I MP3','2013-04-28 19:33:31','2013-04-28 20:40:14',0,43200,0,3,1,'scripts/airplay/CdonDk/CdonDkMP3.php','start=i end=i',2,0,'','','',1),(194,'CdonDk J MP3','2013-04-28 19:33:51','0000-00-00 00:00:00',0,43200,0,3,1,'scripts/airplay/CdonDk/CdonDkMP3.php','start=j end=j',2,0,'','','',1);


--
-- Table structure for table `job_status_log`
--

CREATE TABLE `job_status_log` (
  `jobs_status_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(255) NOT NULL,
  `items_mined` int(11) NOT NULL DEFAULT '0',
  `total_pages_loaded` int(11) NOT NULL DEFAULT '0',
  `host_name` varchar(250) NOT NULL,
  PRIMARY KEY (`jobs_status_log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

