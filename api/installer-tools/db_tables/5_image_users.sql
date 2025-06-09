CREATE TABLE `image_users` (
       `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
       `user_id` int(10) unsigned NOT NULL,
       `image_id` int(10) unsigned NOT NULL,
       `application_id` int(10) unsigned NOT NULL,
       `score` int(10) unsigned DEFAULT NULL,
       `premarked` tinyint(1) NOT NULL DEFAULT '0',
       `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
       `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       `details` text COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)',
PRIMARY KEY (`id`),
       KEY `image_users_user_id_foreign` (`user_id`),
       KEY `image_users_image_id_foreign` (`image_id`),
       KEY `image_users_application_id_foreign` (`application_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;