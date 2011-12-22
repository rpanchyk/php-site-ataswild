-- phpMyAdmin SQL Dump
-- version 3.3.0
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 23, 2011 at 12:24 AM
-- Server version: 5.1.35
-- PHP Version: 5.3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `portfolio_ataswild`
--

-- --------------------------------------------------------

--
-- Table structure for table `tcomments`
--

CREATE TABLE IF NOT EXISTS `tcomments` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `_parent_id` int(11) NOT NULL,
  `name` varchar(128) CHARACTER SET utf8 NOT NULL,
  `email` varchar(64) CHARACTER SET utf8 NOT NULL,
  `info` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `content` text CHARACTER SET utf8 NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `_date_create` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `_date_modify` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tcommentssettings`
--

CREATE TABLE IF NOT EXISTS `tcommentssettings` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `_parent_id` int(11) NOT NULL,
  `smtp_server_host` varchar(128) CHARACTER SET utf8 NOT NULL,
  `smtp_server_port` int(3) NOT NULL DEFAULT '25',
  `email` varchar(128) CHARACTER SET utf8 NOT NULL,
  `email_subject` varchar(512) CHARACTER SET utf8 NOT NULL,
  `is_use_moderation` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tcontainer`
--

CREATE TABLE IF NOT EXISTS `tcontainer` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `markup` text,
  `is_section` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Sections for site',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Default section (main page)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`_id`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tstatic`
--

CREATE TABLE IF NOT EXISTS `tstatic` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(64) NOT NULL,
  `name` varchar(512) NOT NULL,
  `template` varchar(64) NOT NULL DEFAULT 'empty',
  `content` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tuser`
--

CREATE TABLE IF NOT EXISTS `tuser` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `name` varchar(128) NOT NULL,
  `sid` varchar(128) NOT NULL,
  `group_id` int(11) NOT NULL,
  `status` varchar(32) NOT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tusergroup`
--

CREATE TABLE IF NOT EXISTS `tusergroup` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
