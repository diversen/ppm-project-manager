DROP TABLE IF EXISTS `two_factor`;

CREATE TABLE `two_factor` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `secret` varchar(255) NOT NULL,
  `auth_id` int(10) NOT NULL,
  `verified` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_auth_id` (`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;