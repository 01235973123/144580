CREATE TABLE IF NOT EXISTS `#__osmembership_activecampaign` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`contact_id` int UNSIGNED NOT NULL DEFAULT 0,
	`tag_id` int UNSIGNED NOT NULL DEFAULT 0,
	`contact_tag_id` int UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__osmembership_articles` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`article_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE IF NOT EXISTS `#__osmembership_checkinlogs` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`subscriber_id` int NOT NULL DEFAULT 0,
	`checkin_date` datetime,
	`success` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_configs` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`config_key` varchar(100) NOT NULL DEFAULT '',
	`config_value` text,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_countries` (
	`id` int NOT NULL AUTO_INCREMENT,
	`zone_id` int NOT NULL DEFAULT 1,
	`name` varchar(64) NOT NULL DEFAULT '',
	`country_3_code` char(3) NOT NULL DEFAULT '',
	`country_2_code` char(2) NOT NULL DEFAULT '',
	`published` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`country_id` int NOT NULL DEFAULT 0,
	`ordering` int UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `idx_country_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_coupons` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`code` varchar(20) NOT NULL DEFAULT '',
	`coupon_type` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`discount` decimal(10,2) NOT NULL DEFAULT 0.00,
	`plan_id` int NOT NULL DEFAULT 0,
	`times` int NOT NULL DEFAULT 0,
	`used` int NOT NULL DEFAULT 0,
	`valid_from` datetime,
	`valid_to` datetime,
	`note` varchar(255) NOT NULL DEFAULT '',
	`published` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`subscription_type` varchar(50) NOT NULL DEFAULT '',
	`user_id` int UNSIGNED NOT NULL DEFAULT 0,
	`max_usage_per_user` int NOT NULL DEFAULT 0,
	`apply_for` tinyint NOT NULL DEFAULT 0,
	`access` int UNSIGNED NOT NULL DEFAULT 1,
	`assignment` tinyint NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_coupon_plans` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`coupon_id` int NOT NULL DEFAULT 0,
	`plan_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_documents` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`ordering` int NOT NULL DEFAULT 0,
	`title` varchar(224) NOT NULL DEFAULT '',
	`attachment` varchar(225) NOT NULL DEFAULT '',
	`update_package` varchar(225) NOT NULL DEFAULT '',
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

CREATE TABLE IF NOT EXISTS `#__osmembership_exporttmpls` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL DEFAULT '',
	`fields` text,
	`ordering` int NOT NULL DEFAULT 0,
	`published` tinyint NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_fields` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`name` varchar(255) NOT NULL DEFAULT '',
	`title` varchar(255) NOT NULL DEFAULT '',
	`description` mediumtext,
	`field_type` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`payment_method` varchar(255) NOT NULL DEFAULT '',
	`required` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`values` text,
	`default_values` text,
	`rows` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`cols` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`size` int NOT NULL DEFAULT 0,
	`css_class` varchar(50) NOT NULL DEFAULT '',
	`input_mask` varchar(255) NOT NULL DEFAULT '',
	`extra` varchar(255) NOT NULL DEFAULT '',
	`ordering` int NOT NULL DEFAULT 0,
	`published` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`datatype_validation` tinyint NOT NULL DEFAULT 0,
	`field_mapping` varchar(255) NOT NULL DEFAULT '',
	`is_core` tinyint NOT NULL DEFAULT 0,
	`filter` varchar(100) NOT NULL DEFAULT '',
	`container_class` varchar(255) NOT NULL DEFAULT '',
	`container_size` varchar(50) NOT NULL DEFAULT '',
	`input_size` varchar(50) NOT NULL DEFAULT '',
	`assignment` tinyint NOT NULL DEFAULT 0,
	`allowed_file_types` varchar(400) NOT NULL DEFAULT '',
	`show_on_subscription_payment` tinyint NOT NULL DEFAULT 0,
	`taxable` tinyint NOT NULL DEFAULT 1,
	`newsletter_field_mapping` varchar(255) NOT NULL DEFAULT '',
	`populate_from_previous_subscription` tinyint NOT NULL DEFAULT 1,
	`prompt_text` varchar(255) NOT NULL DEFAULT '',
	`filterable` tinyint NOT NULL DEFAULT 0,
	`pattern` varchar(255) NOT NULL DEFAULT '',
	`min` decimal(10,2) DEFAULT NULL,
	`max` decimal(10,2) DEFAULT NUll,
	`step` decimal(10,2) DEFAULT 0.00,
	`show_on_subscription_form` tinyint NOT NULL DEFAULT 1,
	`show_on_subscriptions` tinyint NOT NULL DEFAULT 0,
	`hide_on_membership_renewal` tinyint NOT NULL DEFAULT 0,
	`hide_on_email` tinyint NOT NULL DEFAULT 0,
	`hide_on_export` tinyint NOT NULL DEFAULT 0,
	`show_on_members_list` tinyint NOT NULL DEFAULT 0,
	`show_on_group_member_form` tinyint NOT NULL DEFAULT 1,
	`is_searchable` tinyint NOT NULL DEFAULT 0,
	`show_on_profile` tinyint NOT NULL DEFAULT 0,
	`show_on_user_profile` tinyint NOT NULL DEFAULT 1,
	`fee_field` tinyint NOT NULL DEFAULT 0,
	`fee_usage` tinyint NOT NULL DEFAULT 2,
	`fee_values` text,
	`fee_formula` varchar(255) NOT NULL DEFAULT '',
	`profile_field_mapping` varchar(255) NOT NULL DEFAULT '',
	`depend_on_field_id` int NOT NULL DEFAULT 0,
	`depend_on_options` text,
	`joomla_group_ids` text,
	`max_length` int NOT NULL DEFAULT 0,
	`place_holder` varchar(255) NOT NULL DEFAULT '',
	`multiple` tinyint NOT NULL DEFAULT 0,
	`validation_rules` varchar(255) NOT NULL DEFAULT '',
	`server_validation_rules` varchar(255) NOT NULL DEFAULT '',
	`validation_error_message` varchar(255) NOT NULL DEFAULT '',
	`modify_subscription_duration` text,
	`can_edit_on_profile` tinyint NOT NULL DEFAULT 1,
	`fieldtype` varchar(50) NOT NULL DEFAULT '',
	`populate_from_group_admin` tinyint NOT NULL DEFAULT 0,
	`access` int UNSIGNED NOT NULL DEFAULT 1,
	`synchronize_data` tinyint NOT NULL DEFAULT 0,
	`position` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`readonly` tinyint NOT NULL DEFAULT 0,
	`receive_emails` tinyint NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `#__osmembership_field_plan` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`field_id` int NOT NULL DEFAULT 0,
	`plan_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_field_value` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`field_id` int NOT NULL DEFAULT 0,
	`subscriber_id` int NOT NULL DEFAULT 0,
	`field_value` text,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__osmembership_k2items` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`article_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_menus` (
	`id` int NOT NULL AUTO_INCREMENT,
	`menu_name` varchar(255) NOT NULL DEFAULT '',
	`menu_parent_id` int NOT NULL DEFAULT 0,
	`menu_link` varchar(255) NOT NULL DEFAULT '',
	`published` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`ordering` int NOT NULL DEFAULT 0,
	`menu_class` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_messages` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`message_key` varchar(100) NOT NULL DEFAULT '',
	`message` text,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS `#__osmembership_mmtemplates` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL DEFAULT '',
	`message` text,
	`ordering` int NOT NULL DEFAULT 0,
	`published` tinyint NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__osmembership_plans` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL DEFAULT '',
	`subscription_length` int NOT NULL DEFAULT 0,
	`thumb` varchar(255) NOT NULL DEFAULT '',
	`short_description` mediumtext,
	`description` text,
	`price` decimal(10,2) DEFAULT '0.00',
	`expired_date` datetime,
	`enable_cancel` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`cancel_before_number_days` int NOT NULL DEFAULT 0,
	`params` text,
	`access` int UNSIGNED NOT NULL DEFAULT 1,
	`subscribe_access` int NOT NULL DEFAULT 1,
	`ordering` int NOT NULL DEFAULT 0,
	`published` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`send_first_reminder` int NOT NULL DEFAULT 0,
	`send_second_reminder` int NOT NULL DEFAULT 0,
	`last_payment_action` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`extend_duration` int NOT NULL DEFAULT 0,
	`extend_duration_unit` char(1) NOT NULL DEFAULT '',
	`enable_sms_reminder` int NOT NULL DEFAULT 0,
	`payment_day` int NOT NULL DEFAULT 0,
	`created_by` int NOT NULL DEFAULT 0,
	`admin_email_body` text,
	`admin_renew_email_body` text,
	`admin_upgrade_email_body` text,
	`subscriptions_manage_user_id` int NOT NULL DEFAULT 0,
	`grace_period` int NOT NULL DEFAULT 0,
	`invoice_layout` text,
	`activate_member_card_feature` int NOT NULL DEFAULT 0,
	`card_bg_image` varchar(255) NOT NULL DEFAULT '',
	`card_layout` text,
	`renew_thanks_message` text,
	`renew_thanks_message_offline` text,
	`upgrade_thanks_message` text,
	`upgrade_thanks_message_offline` text,
	`subscription_end_email_subject` varchar(255) NOT NULL DEFAULT '',
	`subscription_end_email_body` text,
	`free_plan_subscription_status` tinyint NOT NULL DEFAULT 1,
	`page_title` varchar(255) NOT NULL DEFAULT '',
	`page_heading` varchar(255) NOT NULL DEFAULT '',
	`meta_keywords` varchar(255) NOT NULL DEFAULT '',
	`meta_description` varchar(255) NOT NULL DEFAULT '',
	`publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`subscription_length_unit` char(1) NOT NULL DEFAULT '',
	`lifetime_membership` tinyint NOT NULL DEFAULT 0,
	`recurring_subscription` tinyint NOT NULL DEFAULT 0,
	`enable_renewal` tinyint NOT NULL DEFAULT 1,
	`require_coupon` tinyint NOT NULL DEFAULT 0,
	`trial_amount` decimal(10,2) DEFAULT '0.00',
	`trial_duration` int NOT NULL DEFAULT 0,
	`trial_duration_unit` char(1) NOT NULL DEFAULT '',
	`number_payments` int NOT NULL DEFAULT 0,
	`subscription_complete_url` text,
	`offline_payment_subscription_complete_url` text,
	`category_id` int NOT NULL DEFAULT 0,
	`send_third_reminder` int NOT NULL DEFAULT 0,
	`send_subscription_end` int NOT NULL DEFAULT 0,
	`alias` varchar(255) NOT NULL DEFAULT '',
	`tax_rate` decimal(10,2) DEFAULT '0.00',
	`notification_emails` varchar(255) NOT NULL DEFAULT '',
	`paypal_email` varchar(255) NOT NULL DEFAULT '',
	`terms_and_conditions_article_id` int NOT NULL DEFAULT 0,
	`payment_methods` varchar(255) NOT NULL DEFAULT '',
	`number_group_members` int NOT NULL DEFAULT 0,
	`number_members_field` int NOT NULL DEFAULT 0,
	`login_redirect_menu_id` int NOT NULL DEFAULT 0,
	`currency` varchar(10) NOT NULL DEFAULT '',
	`currency_symbol` varchar(20) NOT NULL DEFAULT '',
	`conversion_tracking_code` text,
	`subscription_form_message` text,
	`subscription_renew_form_msg` text,
	`user_email_subject` varchar(255) NOT NULL DEFAULT '',
	`user_email_body` text,
	`user_email_body_offline` text,
	`subscription_approved_email_subject` varchar(255) NOT NULL DEFAULT '',
	`subscription_approved_email_body` text,
	`thanks_message` text,
	`thanks_message_offline` text,
	`user_renew_email_subject` varchar(255) NOT NULL DEFAULT '',
	`user_renew_email_body` text,
	`first_reminder_email_subject` varchar(255) NOT NULL DEFAULT '',
	`first_reminder_email_body` text,
	`second_reminder_email_subject` varchar(255) NOT NULL DEFAULT '',
	`second_reminder_email_body` text,
	`third_reminder_email_subject` varchar(255) NOT NULL DEFAULT '',
	`third_reminder_email_body` text,
	`user_renew_email_body_offline` text,
	`user_upgrade_email_body` text,
	`user_upgrade_email_body_offline` text,
	`setup_fee` decimal(10,2) DEFAULT '0.00',
	`prorated_signup_cost` tinyint NOT NULL DEFAULT 0,
	`hidden` tinyint NOT NULL DEFAULT 0,
	`custom_fields` text,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `#__osmembership_plan_documents` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`document_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `idx_plan_id` (`plan_id`),
	KEY `idx_document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_plugins` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL DEFAULT '',
	`title` varchar(100) NOT NULL DEFAULT '',
	`author` varchar(250) NOT NULL DEFAULT '',
	`creation_date` datetime,
	`copyright` varchar(255) NOT NULL DEFAULT '',
	`license` varchar(255) NOT NULL DEFAULT '',
	`author_email` varchar(50) NOT NULL DEFAULT '',
	`author_url` varchar(50) NOT NULL DEFAULT '',
	`version` varchar(50) NOT NULL DEFAULT '',
	`description` varchar(255) NOT NULL DEFAULT '',
	`params` text,
	`ordering` int NOT NULL DEFAULT 0,
	`published` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`position` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`support_recurring_subscription` tinyint NOT NULL DEFAULT 0,
	`access` int UNSIGNED NOT NULL DEFAULT '1',
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

CREATE TABLE IF NOT EXISTS `#__osmembership_renewrates` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`number_days` int NOT NULL DEFAULT 0,
	`price` decimal(10,2) DEFAULT '0.00',
	`renew_option_length` int NOT NULL DEFAULT 0,
	`renew_option_length_unit` char(1) NOT NULL DEFAULT '',
	`ordering` int UNSIGNED NOT NULL DEFAULT 0,
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

CREATE TABLE IF NOT EXISTS `#__osmembership_scheduledocuments` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`document` varchar(255) NOT NULL DEFAULT '',
	`number_days` int NOT NULL DEFAULT 0,
	`ordering` int UNSIGNED NOT NULL DEFAULT 0,
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

CREATE TABLE IF NOT EXISTS `#__osmembership_schedule_sppagebuilder_pages` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`page_id` int NOT NULL DEFAULT 0,
	`number_days` int NOT NULL DEFAULT 0,
	`ordering` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_sefurls` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`md5_key` text,
	`query` text,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_sppagebuilder_pages` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`page_id` int NOT NULL DEFAULT 0,
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

CREATE TABLE IF NOT EXISTS `#__osmembership_subscribers` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`user_id` int UNSIGNED NOT NULL DEFAULT 0,
	`coupon_id` int UNSIGNED NOT NULL DEFAULT 0,
	`first_name` varchar(255) NOT NULL DEFAULT '',
	`last_name` varchar(255) NOT NULL DEFAULT '',
	`organization` varchar(255) NOT NULL DEFAULT '',
	`address` varchar(255) NOT NULL DEFAULT '',
	`address2` varchar(255) NOT NULL DEFAULT '',
	`city` varchar(50) NOT NULL DEFAULT '',
	`state` varchar(50) NOT NULL DEFAULT '',
	`zip` varchar(50) NOT NULL DEFAULT '',
	`country` varchar(100) NOT NULL DEFAULT '',
	`phone` varchar(50) NOT NULL DEFAULT '',
	`fax` varchar(50) NOT NULL DEFAULT '',
	`email` varchar(255) NOT NULL DEFAULT '',
	`comment` text,
	`created_date` datetime,
	`payment_date` datetime,
	`from_date` datetime,
	`to_date` datetime,
	`published` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`amount` decimal(10,2) DEFAULT '0.00',
	`tax_amount` decimal(10,2) DEFAULT '0.00',
	`discount_amount` decimal(10,2) DEFAULT '0.00',
	`gross_amount` decimal(10,2) DEFAULT '0.00',
	`subscription_code` varchar(20) NOT NULL DEFAULT '',
	`payment_method` varchar(50) NOT NULL DEFAULT '',
	`transaction_id` varchar(100) NOT NULL DEFAULT '',
	`act` varchar(10) NOT NULL DEFAULT '',
	`from_subscription_id` int NOT NULL DEFAULT 0,
	`renew_option_id` int NOT NULL DEFAULT 0,
	`upgrade_option_id` int NOT NULL DEFAULT 0,
	`first_reminder_sent` tinyint NOT NULL DEFAULT 0,
	`second_reminder_sent` tinyint NOT NULL DEFAULT 0,
	`ip_address` varchar(255) NOT NULL DEFAULT '',
	`first_sms_reminder_sent` tinyint NOT NULL DEFAULT 0,
	`second_sms_reminder_sent` tinyint NOT NULL DEFAULT 0,
	`third_sms_reminder_sent` tinyint NOT NULL DEFAULT 0,
	`active_event_triggered` tinyint NOT NULL DEFAULT 1,
	`icps_notified` tinyint NOT NULL DEFAULT 0,
	`offline_payment_reminder_email_sent` tinyint NOT NULL DEFAULT 0,
	`formatted_invoice_number` varchar(255) NOT NULL DEFAULT '',
	`formatted_membership_id` varchar(255) NOT NULL DEFAULT '',
	`process_payment_for_subscription` tinyint NOT NULL DEFAULT 0,
	`vies_registered` tinyint NOT NULL DEFAULT 0,
	`offline_recurring_email_sent` tinyint NOT NULL DEFAULT 0,
	`show_on_members_list` tinyint NOT NULL DEFAULT 1,
	`refunded` tinyint NOT NULL DEFAULT 0,
	`parent_id` int NOT NULL DEFAULT 0,
	`auto_subscribe_processed` tinyint NOT NULL DEFAULT 0,
	`is_free_trial` tinyint NOT NULL DEFAULT 0,
	`subscribe_newsletter` tinyint NOT NULL DEFAULT 1,
	`agree_privacy_policy` tinyint NOT NULL DEFAULT 1,
	`mollie_customer_id` varchar(255) NOT NULL DEFAULT '',
	`mollie_recurring_start_date` datetime,
	`tax_rate` decimal(10,6) DEFAULT '0.000000',
	`trial_payment_amount` decimal(10,6) DEFAULT '0.000000',
	`payment_amount` decimal(10,6) DEFAULT '0.000000',
	`payment_currency` varchar(15) NOT NULL DEFAULT '',
	`receiver_email` varchar(255) NOT NULL DEFAULT '',
	`avatar` varchar(255) NOT NULL DEFAULT '',
	`payment_made` int NOT NULL DEFAULT 0,
	`params` text,
	`recurring_profile_id` varchar(255) NOT NULL DEFAULT '',
	`subscription_id` varchar(255) NOT NULL DEFAULT '',
	`recurring_subscription_cancelled` tinyint NOT NULL DEFAULT 0,
	`renewal_count` int NOT NULL DEFAULT 0,
	`from_plan_id` int NOT NULL DEFAULT 0,
	`membership_id` int NOT NULL DEFAULT 0,
	`invoice_year` int NOT NULL DEFAULT 0,
	`is_profile` tinyint NOT NULL DEFAULT 0,
	`invoice_number` int NOT NULL DEFAULT 0,
	`profile_id` int NOT NULL DEFAULT 0,
	`language` varchar(10) NOT NULL DEFAULT '',
	`username` varchar(50) NOT NULL DEFAULT '',
	`user_password` varchar(255) NOT NULL DEFAULT '',
	`payment_processing_fee` decimal(10,2) DEFAULT '0.00',
	`group_admin_id` int NOT NULL DEFAULT 0,
	`subscription_end_sent` tinyint NOT NULL DEFAULT 0,
	`third_reminder_sent` tinyint NOT NULL DEFAULT 0,
	`first_reminder_sent_at` datetime,
	`second_reminder_sent_at` datetime,
	`third_reminder_sent_at` datetime,
	`subscription_end_sent_at` datetime,
	`plan_main_record` tinyint NOT NULL DEFAULT 0,
	`plan_subscription_status` tinyint NOT NULL DEFAULT 0,
	`plan_subscription_from_date` datetime,
	`plan_subscription_to_date` datetime,
	`setup_fee` decimal(10,2) DEFAULT '0.00',
	`gateway_customer_id` varchar(100) NOT NULL DEFAULT '',
    `eb_coupon_id` int NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `idx_plan_id` (`plan_id`),
	KEY `idx_user_id` (`user_id`),
	KEY `idx_active_event_triggered` (`active_event_triggered`),
	KEY `idx_is_profile` (`is_profile`),
	KEY `idx_created_date` (`created_date`),
	KEY `idx_from_date` (`from_date`),
	KEY `idx_to_date` (`to_date`),
	KEY `idx_email` (`email`),
	KEY `idx_published` (`published`),
	KEY `idx_first_name` (`first_name`),
	KEY `idx_last_name` (`last_name`),
	KEY `idx_transaction_id` (`transaction_id`),
	KEY `idx_payment_method` (`payment_method`),
	KEY `idx_act` (`act`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

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

CREATE TABLE IF NOT EXISTS `#__osmembership_upgraderules` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`from_plan_id` int NOT NULL DEFAULT 0,
	`to_plan_id` int NOT NULL DEFAULT 0,
	`price` decimal(10,2) DEFAULT '0.00',
	`min_presence` int NOT NULL DEFAULT 0,
	`max_presence` int NOT NULL DEFAULT 0,
	`published` tinyint UNSIGNED NOT NULL DEFAULT 0,
	`upgrade_prorated` tinyint NOT NULL DEFAULT 0,
	`ordering` int UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__osmembership_urls` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_id` int NOT NULL DEFAULT 0,
	`url` text,
	`title` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `#__osmembership_accesstokens` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`token` varchar(400) NOT NULL DEFAULT '',
	`expire_at` bigint UNSIGNED NOT NULL DEFAULT 0,
	`vendor` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;