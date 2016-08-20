-- phpMyAdmin SQL Dump
-- version 4.0.0-rc1
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 09. 07 2013 kl. 09:17:26
-- Serverversion: 5.5.31-0ubuntu0.12.10.1
-- PHP-version: 5.4.6-1ubuntu1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
-- Struktur-dump for tabellen `quiz`
--

CREATE TABLE IF NOT EXISTS `quiz` (
  `quiz_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_name` varchar(128) NOT NULL,
  `quiz_keywords` text NOT NULL COMMENT 'Quiz keywords for lookup. Artist name(s) etc.',
  `quiz_json` text NOT NULL COMMENT 'Actual quiz json string',
  `author_user_id` int(12) unsigned NOT NULL DEFAULT '0' COMMENT 'Quiz authors airplay user ID',
  `author_email` varchar(128) NOT NULL DEFAULT '' COMMENT 'Quiz authors email',
  `author_fb_id` int(12) unsigned NOT NULL DEFAULT '0' COMMENT 'Quiz authors Facebook ID',
  PRIMARY KEY (`quiz_id`),
  KEY `quiz_name` (`quiz_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Data dump for tabellen `quiz`
--

INSERT INTO `quiz` (`quiz_id`, `quiz_name`, `quiz_keywords`, `quiz_json`, `author_user_id`, `author_email`, `author_fb_id`) VALUES
(1, 'test 1', 'Kim larsen test', '{\n	  "quiz_name": "Kim Larsen quiz 1"\n	, "intro_text": "This is a simple text quiz featuring Kim Larsen questions"\n	, "intro_image": "/images/k/i/m/kim_larsen_1.png"\n	, "author_email": "ml@airplaymusic.dk"\n	, "author_name": "Martin Lütken"\n	, "type": "text" \n	, "num_questions": 5\n	, "num_choices": 3\n	, "image_mode" : "none" \n	, "questions" : [\n		  { "question" : "What year was Kim Larsen born?", "c0" : "1944", "c1" : "1945", "c2" : "1946", "answer" : "c1" }\n 		, { "question" : "Name of first album?", "c0" : "Sådan", "c1" : "Ja tak", "c2" : "Værsgo", "answer" : "c2" }\n 		, { "question" : "Song about hospital ship in Korea?", "c0" : "Sealandia", "c1" : "Jutlandia", "c2" : "1949", "answer" : "c1" }\n 		, { "question" : "Best selling album ever?", "c0" : "Midt om natten", "c1" : "Forklædt som voksen", "c2" : "Yummi Yummi", "answer" : "c0" }\n 		, { "question" : "Kim Larsen''s education?", "c0" : "School teacher", "c1" : "No education", "c2" : "Electrician", "answer" : "c0" }\n	]\n}\n', 0, '', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
