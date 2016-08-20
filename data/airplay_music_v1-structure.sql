-- phpMyAdmin SQL Dump
-- version 4.0.0-rc1
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 23. 08 2013 kl. 23:08:05
-- Serverversion: 5.5.31-0ubuntu0.13.04.1-log
-- PHP-version: 5.4.9-4ubuntu2

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
-- Struktur-dump for tabellen `all_artists`
--

DROP TABLE IF EXISTS `all_artists`;
CREATE TABLE IF NOT EXISTS `all_artists` (
  `all_artists_id` int(11) NOT NULL AUTO_INCREMENT,
  `all_artists_name` varchar(256) NOT NULL,
  PRIMARY KEY (`all_artists_id`),
  KEY `all_artists_name` (`all_artists_name`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `all_item_bases`
--

DROP TABLE IF EXISTS `all_item_bases`;
CREATE TABLE IF NOT EXISTS `all_item_bases` (
  `all_item_bases_id` int(11) NOT NULL AUTO_INCREMENT,
  `all_item_bases_name` varchar(256) NOT NULL,
  `item_type` int(11) DEFAULT NULL,
  `artist_name` varchar(256) NOT NULL,
  PRIMARY KEY (`all_item_bases_id`),
  KEY `all_item_bases_name` (`all_item_bases_name`(255)),
  KEY `item_type` (`item_type`),
  KEY `artist_name` (`artist_name`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `artist`
--

DROP TABLE IF EXISTS `artist`;
CREATE TABLE IF NOT EXISTS `artist` (
  `artist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `artist_name` varchar(256) NOT NULL,
  `artist_url` varchar(256) NOT NULL,
  `genre_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `subgenre_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `country_id` smallint(6) DEFAULT '0',
  `artist_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `item_price_count` INT NOT NULL DEFAULT '0', 
  `artist_reliability` int(2) NOT NULL DEFAULT '0',
  `artist_soundex` VARCHAR( 256 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`artist_id`),
  KEY `artist_name` (`artist_name`(255)),
  KEY `genre_id` (`genre_id`),
  KEY `artist_reliability` (`artist_reliability`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `artist_alias`
--

DROP TABLE IF EXISTS `artist_alias`;
CREATE TABLE IF NOT EXISTS `artist_alias` (
  `artist_alias_id` int(11) NOT NULL AUTO_INCREMENT,
  `artist_alias_name` varchar(256) NOT NULL,
  `artist_name` varchar(256) NOT NULL,
  PRIMARY KEY (`artist_alias_id`),
  KEY `artist_alias_name` (`artist_alias_name`(255)),
  KEY `artist_name` (`artist_name`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `artist_lookup`
--

DROP TABLE IF EXISTS `artist_lookup`;
CREATE TABLE IF NOT EXISTS `artist_lookup` (
  `artist_lookup_id` int(11) NOT NULL AUTO_INCREMENT,
  `artist_id` int(11) NOT NULL,
  `artist_name` varchar(256) NOT NULL,
  `artist_name_alternative_spelling` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`artist_lookup_id`),
  KEY `artist_id` (`artist_id`),
  FULLTEXT KEY `artist_lookup` (`artist_name`,`artist_name_alternative_spelling`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `artist_synonym`
--

DROP TABLE IF EXISTS `artist_synonym`;
CREATE TABLE IF NOT EXISTS `artist_synonym` (
  `artist_synonym_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `artist_synonym_name` varchar(256) NOT NULL DEFAULT '',
  `artist_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`artist_synonym_id`),
  KEY `artist_synonym_name` (`artist_synonym_name`(255)),
  KEY `artist_id` (`artist_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `artist_text`
--

DROP TABLE IF EXISTS `artist_text`;
CREATE TABLE IF NOT EXISTS `artist_text` (
  `artist_text_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_code` char(2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `artist_id` int(11) NOT NULL,
  `artist_article` text NOT NULL,
  `artist_text_reliability` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`artist_text_id`),
  KEY `is_default` (`language_code`,`artist_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `artist_various`
--

DROP TABLE IF EXISTS `artist_various`;
CREATE TABLE IF NOT EXISTS `artist_various` (
  `artist_various_id` int(10) NOT NULL AUTO_INCREMENT,
  `artist_various_name` varchar(128) NOT NULL,
  PRIMARY KEY (`artist_various_id`),
  KEY `artist_various_name` (`artist_various_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `country`
--

DROP TABLE IF EXISTS `country`;
CREATE TABLE IF NOT EXISTS `country` (
  `country_id` smallint(3) NOT NULL,
  `country_name` varchar(256) NOT NULL,
  PRIMARY KEY (`country_id`),
  KEY `country_name` (`country_name`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `currency`
--

DROP TABLE IF EXISTS `currency`;
CREATE TABLE IF NOT EXISTS `currency` (
  `currency_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `currency_name` char(4) NOT NULL,
  `from_euro` double DEFAULT NULL,
  `to_euro` double DEFAULT NULL,
  PRIMARY KEY (`currency_id`),
  KEY `currency_name` (`currency_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `currency_to_euro`
--

DROP TABLE IF EXISTS `currency_to_euro`;
CREATE TABLE IF NOT EXISTS `currency_to_euro` (
  `currency_to_euro_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `currency_to_euro_name` char(4) NOT NULL DEFAULT '',
  `currency_id` tinyint(3) unsigned NOT NULL,
  `currency_name` char(4) NOT NULL,
  `to_euro` double DEFAULT NULL,
  PRIMARY KEY (`currency_to_euro_id`),
  KEY `currency_id` (`currency_id`),
  KEY `currency_name` (`currency_id`,`currency_name`,`to_euro`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `genre`
--

DROP TABLE IF EXISTS `genre`;
CREATE TABLE IF NOT EXISTS `genre` (
  `genre_id` tinyint(3) unsigned NOT NULL,
  `genre_name` varchar(40) NOT NULL,
  PRIMARY KEY (`genre_id`),
  KEY `genre_name` (`genre_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `info_artist`
--

DROP TABLE IF EXISTS `info_artist`;
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
  `url_facebook` varchar(192) NOT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `intro_text_album`
--

DROP TABLE IF EXISTS `intro_text_album`;
CREATE TABLE IF NOT EXISTS `intro_text_album` (
  `intro_text_album_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `language_code` char(2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `album_simple_id` int(11) NOT NULL,
  `intro_text` text NOT NULL,
  PRIMARY KEY (`intro_text_album_id`),
  KEY `is_default` (`is_default`,`language_code`,`album_simple_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `intro_text_artist`
--

DROP TABLE IF EXISTS `intro_text_artist`;
CREATE TABLE IF NOT EXISTS `intro_text_artist` (
  `intro_text_artist_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `language_code` char(2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `artist_id` int(11) NOT NULL,
  `intro_text` text NOT NULL,
  PRIMARY KEY (`intro_text_artist_id`),
  KEY `is_default` (`is_default`,`language_code`,`artist_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `intro_text_song`
--

DROP TABLE IF EXISTS `intro_text_song`;
CREATE TABLE IF NOT EXISTS `intro_text_song` (
  `intro_text_song_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `language_code` char(2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `song_simple_id` int(11) NOT NULL,
  `intro_text` text NOT NULL,
  PRIMARY KEY (`intro_text_song_id`),
  KEY `is_default` (`is_default`,`language_code`,`song_simple_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `item_base`
--

DROP TABLE IF EXISTS `item_base`;
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
  `undertoner_review_rating` decimal(10,1) NOT NULL,
  `undertoner_review_url` varchar(2048) NOT NULL,
  `undertoner_review_text` mediumtext NOT NULL,
  `gaffa_review_rating` decimal(10,1) NOT NULL,
  `gaffa_review_url` varchar(2048) NOT NULL,
  `gaffa_review_text` mediumtext NOT NULL,
  `soundofaarhus_review_rating` DECIMAL( 10, 1 ) NOT NULL,
  `soundofaarhus_review_url` VARCHAR( 2048 ) NOT NULL,
  `soundofaarhus_review_text` MEDIUMTEXT NOT NULL,
  `item_base_reliability` int(2) NOT NULL DEFAULT '0',
  `item_base_soundex` VARCHAR( 256 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL ,
  `image_width` INT NOT NULL AFTER `image_url` ,
  `image_height` INT NOT NULL AFTER `image_width` ,
  `image_processed` BOOLEAN NOT NULL DEFAULT '0' AFTER `image_height` ,
  `image_from_record_store_id` INT NOT NULL AFTER `image_processed` ,
  PRIMARY KEY (`item_base_id`),
  KEY `item_type` (`item_type`),
  KEY `item_name` (`item_base_name`(255)),
  KEY `item_base_reliability` (`item_base_reliability`),
  KEY `artist_id` (`artist_id`),
  KEY `parent_item` (`parent_item`),
  KEY `release_date` (`release_date`),
  KEY ( `image_from_record_store_id` ) ,
  KEY ( `image_processed` ) 
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `item_base_alias`
--

DROP TABLE IF EXISTS `item_base_alias`;
CREATE TABLE IF NOT EXISTS `item_base_alias` (
  `item_base_alias_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_base_alias_name` varchar(256) NOT NULL,
  `artist_name` varchar(256) NOT NULL,
  `item_base_name` varchar(256) NOT NULL,
  PRIMARY KEY (`item_base_alias_id`),
  KEY `item_base_alias_name` (`item_base_alias_name`(255)),
  KEY `artist_name` (`artist_name`(255)),
  KEY `item_base_name` (`item_base_name`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `item_base_correction`
--

DROP TABLE IF EXISTS `item_base_correction`;
CREATE TABLE IF NOT EXISTS `item_base_correction` (
  `item_base_correction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `artist_id` int(10) unsigned NOT NULL,
  `item_base_correction_name` varchar(256) NOT NULL,
  `item_base_name` varchar(256) NOT NULL,
  PRIMARY KEY (`item_base_correction_id`),
  KEY `artist_id` (`artist_id`),
  KEY `item_name` (`item_base_name`(255)),
  KEY `item_base_correction_name` (`item_base_correction_name`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `item_base_lookup`
--

DROP TABLE IF EXISTS `item_base_lookup`;
CREATE TABLE IF NOT EXISTS `item_base_lookup` (
  `item_base_lookup_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_base_id` int(11) NOT NULL,
  `item_type` int(11) NOT NULL,
  `item_base_name` varchar(256) NOT NULL,
  `item_base_name_alternative_spelling` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`item_base_lookup_id`),
  KEY `item_base_id` (`item_base_id`),
  KEY `item_type` (`item_type`),
  FULLTEXT KEY `item_base_lookup` (`item_base_name`,`item_base_name_alternative_spelling`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `item_base_text`
--

DROP TABLE IF EXISTS `item_base_text`;
CREATE TABLE IF NOT EXISTS `item_base_text` (
  `item_base_text_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_code` char(2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `item_base_id` int(11) NOT NULL,
  `item_base_article` text NOT NULL,
  `item_base_text_reliability` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_base_text_id`),
  KEY `is_default` (`language_code`,`item_base_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `item_price`
--

DROP TABLE IF EXISTS `item_price`;
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
  `item_date_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `item_year` smallint(4) unsigned NOT NULL DEFAULT '0',
  `item_time` int(11) NOT NULL DEFAULT '0',
  `track_number` int(11) NOT NULL DEFAULT '0',
  `item_used` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `parent_item` int(11) NOT NULL DEFAULT '0',
  `child_items` varchar(512) NOT NULL,
  `item_grading_cover` varchar(3) NOT NULL,
  `item_grading` varchar(3) NOT NULL,
  PRIMARY KEY (`item_price_id`),
  KEY `media_format_id` (`media_format_id`),
  KEY `record_store_id` (`record_store_id`),
  KEY `item_price_name` (`item_price_name`(255)),
  KEY `item_type` (`item_type`),
  KEY `item_used` (`item_used`),
  KEY `item_base_id` (`item_base_id`),
  KEY `artist_id` (`artist_id`),
  KEY `timestamp_updated` (`timestamp_updated`),
  KEY `item_grading` (`item_grading`,`item_grading_cover`),
  KEY `item_date_time` (`item_date_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `jac`
--

DROP TABLE IF EXISTS `jac`;
CREATE TABLE IF NOT EXISTS `jac` (
  `item_base_id` int(11) NOT NULL,
  PRIMARY KEY (`item_base_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `job`
--

DROP TABLE IF EXISTS `job`;
CREATE TABLE IF NOT EXISTS `job` (
  `job_id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_danish_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_run` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `record_store_id` int(11) NOT NULL,
  `job_run_interval_minutes` int(11) NOT NULL DEFAULT '1440',
  `job_force_restart` int(11) NOT NULL DEFAULT '0',
  `job_status_id` int(11) NOT NULL,
  `estimated_runtime` int(11) NOT NULL,
  `script_path` varchar(255) CHARACTER SET latin1 NOT NULL,
  `parameters` varchar(255) CHARACTER SET latin1 COLLATE latin1_danish_ci NOT NULL,
  `job_priority` int(11) NOT NULL,
  `items_mined` int(11) NOT NULL DEFAULT '0',
  `nav_current_state_index` varchar(255) CHARACTER SET latin1 COLLATE latin1_danish_ci NOT NULL,
  `nav_last_state_index` varchar(255) CHARACTER SET latin1 COLLATE latin1_danish_ci NOT NULL,
  `host_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_danish_ci NOT NULL,
  `job_approved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`job_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `job_status`
--

DROP TABLE IF EXISTS `job_status`;
CREATE TABLE IF NOT EXISTS `job_status` (
  `job_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  PRIMARY KEY (`job_status_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `job_status_log`
--

DROP TABLE IF EXISTS `job_status_log`;
CREATE TABLE IF NOT EXISTS `job_status_log` (
  `jobs_status_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(255) NOT NULL,
  `items_mined` int(11) NOT NULL DEFAULT '0',
  `total_pages_loaded` int(11) NOT NULL DEFAULT '0',
  `host_name` varchar(250) NOT NULL,
  PRIMARY KEY (`jobs_status_log_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `live_search_cache`
--

DROP TABLE IF EXISTS `live_search_cache`;
CREATE TABLE IF NOT EXISTS `live_search_cache` (
  `live_search_cache_id` int(11) NOT NULL AUTO_INCREMENT,
  `site` varchar(2) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `item_base_id` int(11) NOT NULL DEFAULT '0',
  `item_type` int(11) NOT NULL,
  `search_for_type_id` int(11) NOT NULL,
  `timestamp_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cache_tries` int(1) NOT NULL DEFAULT '1',
  `record_store_id` int(11) NOT NULL,
  `json_response` longtext NOT NULL,
  `json_response_collapsed` longtext NOT NULL,
  `item_count` int(11) NOT NULL DEFAULT '0',
  `item_prices_count_total` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`live_search_cache_id`),
  KEY `artist_id` ( `artist_id` , `item_type` , `search_for_type_id` )
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `media_format`
--

DROP TABLE IF EXISTS `media_format`;
CREATE TABLE IF NOT EXISTS `media_format` (
  `media_format_id` tinyint(3) unsigned NOT NULL,
  `media_format_name` varchar(30) NOT NULL,
  PRIMARY KEY (`media_format_id`),
  KEY `media_format_name` (`media_format_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `quiz`
--

DROP TABLE IF EXISTS `quiz`;
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

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `record_store`
--

DROP TABLE IF EXISTS `record_store`;
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=55 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `unknown_genre`
--

DROP TABLE IF EXISTS `unknown_genre`;
CREATE TABLE IF NOT EXISTS `unknown_genre` (
  `unknown_genre_id` int(11) NOT NULL AUTO_INCREMENT,
  `unknown_genre_name` varchar(128) NOT NULL DEFAULT '',
  `record_store_id` int(11) DEFAULT NULL,
  `buy_at_url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`unknown_genre_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `unknown_media_format`
--

DROP TABLE IF EXISTS `unknown_media_format`;
CREATE TABLE IF NOT EXISTS `unknown_media_format` (
  `unknown_media_format_id` int(11) NOT NULL AUTO_INCREMENT,
  `unknown_media_format_name` varchar(64) NOT NULL DEFAULT '',
  `record_store_id` int(11) DEFAULT NULL,
  `buy_at_url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`unknown_media_format_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `unknown_media_type`
--

DROP TABLE IF EXISTS `unknown_media_type`;
CREATE TABLE IF NOT EXISTS `unknown_media_type` (
  `unknown_media_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `unknown_media_type_name` varchar(64) NOT NULL DEFAULT '',
  `record_store_id` int(11) DEFAULT NULL,
  `buy_at_url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`unknown_media_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `word`
--

DROP TABLE IF EXISTS `word`;
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Table structure for table `facebook_app_v1`
--

CREATE TABLE IF NOT EXISTS `facebook_app_v1` (
  `facebook_app_v1_id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `artist_id` int(11) NOT NULL,
  `item_type` int(11) NOT NULL,
  `media_format_id` int(11) NOT NULL,
  `json` text NOT NULL,
  PRIMARY KEY (`facebook_app_v1_id`),
  KEY `idx_artist_item_format` (`artist_id`,`item_type`,`media_format_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
