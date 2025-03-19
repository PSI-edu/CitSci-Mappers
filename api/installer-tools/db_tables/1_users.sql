CREATE TABLE `users` (
     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
     `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     `publishable_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     `details` text COLLATE utf8_unicode_ci,
     `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
     `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     `tutorials_completed` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
     `scistarter_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     `scistarter_id` int(11) DEFAULT NULL,
     `scistarter_profile_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     `public_name` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `users_name_unique` (`username`),
     KEY `emails_on_users` (`email`),
     KEY `users_email_index` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
