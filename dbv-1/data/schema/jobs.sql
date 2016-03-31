CREATE TABLE `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` longtext COLLATE utf8_unicode_ci,
  `payload` longtext COLLATE utf8_unicode_ci,
  `attempts` int(11) DEFAULT NULL,
  `reserved` int(11) DEFAULT NULL,
  `reserved_at` int(11) DEFAULT NULL,
  `available_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci