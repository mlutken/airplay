-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb1
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 03. 04 2013 kl. 21:19:58
-- Serverversion: 5.5.29
-- PHP-version: 5.4.6-1ubuntu1.2

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
-- Struktur-dump for tabellen `artist_text`
--

CREATE TABLE IF NOT EXISTS `artist_text` (
  `artist_text_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_code` char(2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `artist_id` int(11) NOT NULL,
  `artist_article` text NOT NULL,
  `artist_text_reliability` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`artist_text_id`),
  KEY `is_default` (`language_code`,`artist_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Data dump for tabellen `artist_text`
--

INSERT INTO `artist_text` (`artist_text_id`, `language_code`, `created_date`, `artist_id`, `artist_article`, `artist_text_reliability`) VALUES
(1, 'da', '2013-03-27 20:50:43', 0, '<h2>Guide til billigste priser og største udvalg af <b>{artist_name}</b> albums og sange.</h2>Vælg blandt alle musikformater - Mp3, WMA, FLAC, streaming...CD, SACD , vinyl, DVD... <br/>Og sammenlign udvalg, tilbud, priser blandt mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore...\r\nsamt musik-streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio...', 100),
(2, 'en', '2013-03-27 20:50:43', 0, '<h2>Guide til billigste priser og største udvalg af <b>{artist_name}</b> albums og sange.</h2>Vælg blandt alle musikformater - Mp3, WMA, FLAC, streaming...CD, SACD , vinyl, DVD... <br/>Og sammenlign udvalg, tilbud, priser blandt mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore...\r\nsamt musik-streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio...', 100);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `item_base_text`
--

CREATE TABLE IF NOT EXISTS `item_base_text` (
  `item_base_text_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_code` char(2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `item_base_id` int(11) NOT NULL,
  `item_base_article` text NOT NULL,
  `item_base_text_reliability` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_base_text_id`),
  KEY `is_default` (`language_code`,`item_base_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Data dump for tabellen `item_base_text`
--

INSERT INTO `item_base_text` (`item_base_text_id`, `language_code`, `created_date`, `item_base_id`, `item_base_article`, `item_base_text_reliability`) VALUES
(1, 'da', '2013-03-27 20:53:31', 0, '<h2>Guide til den billigste pris på  {item_base_name} af kunstneren {artist_name}</h2>Guide til den billigste pris på <a href="{item_base_url}">{item_base_name}</a> af kunstneren <a href="{artist_url}">{artist_name}</a>. Vælg blandt alle musikformater - MP3, WMA, FLAC, streaming, CD, SACD, vinyl, DVD.<br/>Musiksøgemaskinen har sammenlignet mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore - samt musik-/streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio.', 0),
(2, 'en', '2013-03-27 20:53:31', 0, '<h2>Guide til den billigste pris på  {item_base_name} af kunstneren {artist_name}</h2>Guide til den billigste pris på <a href="{item_base_url}">{item_base_name}</a> af kunstneren <a href="{artist_url}">{artist_name}</a>. Vælg blandt alle musikformater - MP3, WMA, FLAC, streaming, CD, SACD, vinyl, DVD.<br/>Musiksøgemaskinen har sammenlignet mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore - samt musik-/streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio.', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
