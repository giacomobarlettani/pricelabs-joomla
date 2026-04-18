CREATE TABLE IF NOT EXISTS `#__e4jconnect_pricelabs_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `signature` varchar(64) NOT NULL,
  `event` varchar(32) DEFAULT NULL,
  `createdon` datetime NOT NULL,
  `jobs_count` int(5) unsigned DEFAULT 0,
  `jobs_processed` int(5) unsigned DEFAULT 0,
  `aborted` tinyint(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__e4jconnect_pricelabs_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_queue` int(10) DEFAULT NULL,
  `createdon` datetime NOT NULL,
  `command` mediumblob DEFAULT NULL COMMENT 'The serialized object for the requests to execute',
  `results` mediumblob DEFAULT NULL COMMENT 'The execution results',
  `executedon` datetime DEFAULT NULL,
  `completedon` datetime DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;