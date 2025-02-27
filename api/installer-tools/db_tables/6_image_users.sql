CREATE TABLE `image_users` (
       `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
       `user_id` int(10) unsigned NOT NULL,
       `image_id` int(10) unsigned NOT NULL,
       `application_id` int(10) unsigned NOT NULL,
       `submit_time` int(10) unsigned DEFAULT NULL,
       `score` int(10) unsigned DEFAULT NULL,
       `premarked` tinyint(1) NOT NULL DEFAULT '0',
       `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
       `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       `details` text COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)',
       `validated_center` tinyint(1) DEFAULT NULL,
       PRIMARY KEY (`id`),
       KEY `image_users_user_id_foreign` (`user_id`),
       KEY `image_users_image_id_foreign` (`image_id`),
       KEY `image_users_application_id_foreign` (`application_id`),
       CONSTRAINT `image_users_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
       CONSTRAINT `image_users_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE,
       CONSTRAINT `image_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;