CREATE TABLE `marks` (
         `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
         `user_id` int(10) unsigned NOT NULL,
         `image_id` int(10) unsigned NOT NULL,
         `application_id` int(10) unsigned NOT NULL,
         `image_user_id` int(10) unsigned NOT NULL,
         `machine_mark_id` int(10) unsigned DEFAULT NULL,
         `shared_mark_id` int(10) unsigned DEFAULT NULL,
         `x` double(8,2) NOT NULL,
         `y` double(8,2) NOT NULL,
         `diameter` double(8,2) DEFAULT NULL,
         `submit_time` int(10) unsigned DEFAULT NULL,
         `confirmed` tinyint(1) DEFAULT NULL,
         `score` double(8,2) DEFAULT NULL,
         `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
         `sub_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
         `details` blob,
         `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
         `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
         KEY `marks_application_id_foreign` (`application_id`),
         KEY `marks_image_user_id_foreign` (`image_user_id`),
         KEY `marks_machine_mark_id_foreign` (`machine_mark_id`),
         KEY `marks_shared_mark_id_foreign` (`shared_mark_id`),
         KEY `marks_user_id_foreign` (`user_id`),
         KEY `marks_image_id_foreign` (`image_id`),
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;