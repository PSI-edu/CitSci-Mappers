CREATE TABLE `tutorials`(
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `application_id` int(10) unsigned NOT NULL,
    `user_id` int(10) unsigned NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
    KEY `tutorials_application_id_foreign` (`application_id`),
    KEY `tutorials_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;