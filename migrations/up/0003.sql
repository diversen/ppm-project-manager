DROP TABLE IF EXISTS `log`;

CREATE TABLE `log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `message` TEXT,
  `date` DATETIME DEFAULT NOW(),
  `remote_ip` VARCHAR(255) DEFAULT 'no_remote_ip',
  `request_uri` VARCHAR(1024) DEFAULT 'no_request_uri',
  `type`  VARCHAR(255) DEFAULT 'no_type',
  `section` VARCHAR(255) DEFAULT 'no_section',
  `code` INT(11) DEFAULT 0,
  `auth_id` int(11) unsigned DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_type_key` (`type`),
  KEY `idx_type_section` (`section`),
  KEY `idx_type_code` (`code`),
  KEY `idx_type_auth_id` (`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;