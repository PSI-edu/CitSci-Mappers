CREATE TABLE `users` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `username` varchar(255) UNIQUE,
              `first_name` varchar(100),
              `last_name` varchar(100),
              `public_name` tinyint(1) NOT NULL DEFAULT '0',
              `email` varchar(255) UNIQUE NOT NULL,
              `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `users_name_unique` (`username`),
              KEY `emails_on_users` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
