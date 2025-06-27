CREATE TABLE IF NOT EXISTS `#__osmembership_categories` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL DEFAULT '',
	`description` text,
	`published` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`exclusive_plans` tinyint NOT NULL DEFAULT 0,
	`grouping_plans` tinyint NOT NULL DEFAULT 0,
	`access` int UNSIGNED NOT NULL DEFAULT 1,
	`ordering` int NOT NULL DEFAULT 0,
	`parent_id` int NOT NULL DEFAULT 0,
	`level` tinyint NOT NULL DEFAULT 1,
	`alias` varchar(255) NOT NULL DEFAULT '',
	`subscription_form_message` text,
	`user_email_body` text,
	`user_email_body_offline` text,
	`admin_email_body` text,
	`thanks_message` text,
	`thanks_message_offline` text,
	`subscription_renew_form_msg` text,
	`user_renew_email_body` text,
	`user_renew_email_body_offline` text,
	`admin_renew_email_body` text,
	`renew_thanks_message` text,
	`renew_thanks_message_offline` text,
	`subscription_upgrade_form_msg` text,
	`user_upgrade_email_body` text,
	`user_upgrade_email_body_offline` text,
	`admin_upgrade_email_body` text,
	`upgrade_thanks_message` text,
	`upgrade_thanks_message_offline` text,
	`subscription_approved_email_body` text,
	`first_reminder_email_body` text,
	`second_reminder_email_body` text,
	`third_reminder_email_body` text,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `#__osmembership_field_plan` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`field_id` int NOT NULL DEFAULT 0,
	`plan_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_messages` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`message_key` varchar(100) NOT NULL DEFAULT '',
	`message` text,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_states` (
	`id` int NOT NULL AUTO_INCREMENT,
	`country_id` int NOT NULL DEFAULT 1,
	`state_name` varchar(64) NOT NULL DEFAULT '',
	`state_3_code` char(10) NOT NULL DEFAULT '',
	`state_2_code` char(10) NOT NULL DEFAULT '',
	`published` tinyint NOT NULL DEFAULT 1,
	`state_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `state_3_code` (`country_id`,`state_3_code`),
	UNIQUE KEY `state_2_code` (`country_id`,`state_2_code`),
	KEY `idx_country_id` (`country_id`),
	KEY `idx_state_name` (`state_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_k2items` (
	 `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	 `plan_id` int NOT NULL DEFAULT 0,
	 `article_id` int NOT NULL DEFAULT 0,
	 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_emails` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`email_type` varchar(50) NOT NULL DEFAULT '',
	`sent_at` datetime,
	`sent_to` tinyint NOT NULL DEFAULT 0,
	`email` varchar(255) NOT NULL DEFAULT '',
	`subject` varchar(255) NOT NULL DEFAULT '',
	`body` text,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__osmembership_documents` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`ordering` int NOT NULL DEFAULT 0,
	`title` varchar(224) NOT NULL DEFAULT '',
	`attachment` varchar(225) NOT NULL DEFAULT '',
	`update_package` varchar(225) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_plan_documents` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`document_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `idx_plan_id` (`plan_id`),
	KEY `idx_document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_taxes` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`country` varchar(255) NOT NULL DEFAULT '',
	`rate` decimal(10,2) DEFAULT '0.00',
	`vies` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`published` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`state` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_sefurls` (
	 `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	 `md5_key` text,
	 `query` text,
	 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_articles` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`article_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_urls` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`url` text,
	`title` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_schedulecontent` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`article_id` int NOT NULL DEFAULT 0,
	`number_days` int NOT NULL DEFAULT 0,
	`ordering` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_schedule_k2items` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`item_id` int NOT NULL DEFAULT 0,
	`number_days` int NOT NULL DEFAULT 0,
	`ordering` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_coupon_plans` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`coupon_id` int NOT NULL DEFAULT 0,
	`plan_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_renewaldiscounts` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`number_days` int NOT NULL DEFAULT 0,
	`discount_type` tinyint NOT NULL DEFAULT 0,
	`discount_amount` decimal(10,2) DEFAULT '0.00',
	`title` varchar(255) NOT NULL DEFAULT '',
	`published` tinyint NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_downloadids` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` int NOT NULL DEFAULT 0,
	`download_id` varchar(50) NOT NULL DEFAULT '',
	`created_date` datetime,
	`domain` varchar(255) NOT NULL DEFAULT '',
	`published` tinyint UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_downloadlogs` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`download_id` int NOT NULL DEFAULT 0,
	`document_id` int NOT NULL DEFAULT 0,
	`version` varchar(50) NOT NULL DEFAULT '',
	`download_date` datetime,
	`domain` varchar(255) NOT NULL DEFAULT '',
	`server_ip` varchar(50) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_sppagebuilder_pages` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`page_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_schedule_sppagebuilder_pages` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`page_id` int NOT NULL DEFAULT 0,
	`number_days` int NOT NULL DEFAULT 0,
	`ordering` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_scheduledocuments` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`document` varchar(255) NOT NULL DEFAULT '',
	`number_days` int NOT NULL DEFAULT 0,
	`ordering` int UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_checkinlogs` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`subscriber_id` int NOT NULL DEFAULT 0,
	`checkin_date` datetime,
	`success` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_mitems` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL DEFAULT '',
	`title` varchar(255) NOT NULL DEFAULT '',
	`title_en` varchar(400) NOT NULL DEFAULT '',
	`type` varchar(255) NOT NULL DEFAULT '',
	`group` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`translatable` tinyint UNSIGNED NOT NULL DEFAULT 1,
	`featured` tinyint UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_exporttmpls` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL DEFAULT '',
	`fields` text,
	`ordering` int NOT NULL DEFAULT 0,
	`published` tinyint NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_mmtemplates` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL DEFAULT '',
	`message` text,
	`ordering` int NOT NULL DEFAULT 0,
	`published` tinyint NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `#__osmembership_accesstokens` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`token` varchar(400) NOT NULL DEFAULT '',
	`expire_at` bigint UNSIGNED NOT NULL DEFAULT 0,
	`vendor` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;