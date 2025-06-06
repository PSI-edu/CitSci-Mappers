CREATE TABLE `achievements`(
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `description` text COLLATE utf8_unicode_ci,
    `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `criteria` text COLLATE utf8_unicode_ci,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
    UNIQUE KEY `achievements_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;