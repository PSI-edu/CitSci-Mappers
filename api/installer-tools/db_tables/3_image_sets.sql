CREATE TABLE `image_sets` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `application_id` int(10) unsigned NOT NULL,
      `priority` double(8,2) NOT NULL DEFAULT '1.00',
      `sun_angle` double(8,2) DEFAULT NULL,
      `minimum_latitude` double(8,2) DEFAULT NULL,
      `maximum_latitude` double(8,2) DEFAULT NULL,
      `minimum_longitude` double(8,2) DEFAULT NULL,
      `maximum_longitude` double(8,2) DEFAULT NULL,
      `pixel_resolution` double(8,2) DEFAULT NULL,
      `description` text COLLATE utf8_unicode_ci,
      `details` text COLLATE utf8_unicode_ci,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
UNIQUE KEY `image_sets_name_unique` (`name`),
      KEY `image_sets_application_id_foreign` (`application_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;