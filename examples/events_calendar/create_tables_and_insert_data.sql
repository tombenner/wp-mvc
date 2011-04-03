--
-- Run this script to create the tables and insert some example data for the
-- events_calendar example application.
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL auto_increment,
  `venue_id` int(9) default NULL,
  `date` date default NULL,
  `time` time default NULL,
  `description` text,
  `is_public` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `venue_id` (`venue_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `venue_id`, `date`, `time`, `description`, `is_public`) VALUES
(1, 2, '2011-06-17', '18:00:00', '', 1),
(2, 2, '2011-11-10', '15:43:00', '', 1),
(3, 1, '2011-08-14', '18:00:00', 'Description about this event...', 1);

-- --------------------------------------------------------

--
-- Table structure for table `events_speakers`
--

CREATE TABLE IF NOT EXISTS `events_speakers` (
  `id` int(7) NOT NULL auto_increment,
  `event_id` int(11) default NULL,
  `speaker_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`),
  KEY `speaker_id` (`speaker_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=122 ;

--
-- Dumping data for table `events_speakers`
--

INSERT INTO `events_speakers` (`id`, `event_id`, `speaker_id`) VALUES
(121, 1, 5),
(120, 1, 4),
(109, 2, 6),
(108, 2, 3),
(107, 2, 2),
(115, 3, 5),
(114, 3, 6),
(113, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `speakers`
--

CREATE TABLE IF NOT EXISTS `speakers` (
  `id` int(8) NOT NULL auto_increment,
  `first_name` varchar(255) default NULL,
  `last_name` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `description` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `speakers`
--

INSERT INTO `speakers` (`id`, `first_name`, `last_name`, `url`, `description`) VALUES
(1, 'Maurice', 'Deebank', 'http://maurice.com', 'Maurice''s bio...'),
(3, 'Martin', 'Duffy', 'http://duffy.com', 'Martin''s bio...'),
(4, 'Marco', 'Thomas', 'http://marco.com', 'Marco''s bio...'),
(5, 'Nick', 'Gilbert', 'http://nick.com', 'Nick''s bio...'),
(6, 'Mick', 'Lloyd', 'http://mick.com', 'Mick''s bio...'),
(2, 'Gary', 'Ainge', 'http://gary.com', 'Gary''s bio...');

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE IF NOT EXISTS `venues` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `sort_name` varchar(255) NOT NULL,
  `url` varchar(255) default NULL,
  `description` text,
  `address1` tinytext,
  `address2` tinytext,
  `city` varchar(100) default NULL,
  `state` varchar(100) default NULL,
  `zip` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`, `sort_name`, `url`, `description`, `address1`, `address2`, `city`, `state`, `zip`) VALUES
(1, 'Cabell Auditorium', '', 'http://cabellauditorium.com', '', '10 E 15th St', '', 'New York', 'NY', '10003'),
(2, 'Farveson Hall', '', 'http://farvesonhall.org', '', '216 W 21st St', '', 'New York', 'NY', '10011');
