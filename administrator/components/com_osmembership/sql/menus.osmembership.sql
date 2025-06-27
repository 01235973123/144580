DROP TABLE IF EXISTS `#__osmembership_menus`;
CREATE TABLE `#__osmembership_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(255) DEFAULT NULL,
  `menu_parent_id` int(11) DEFAULT NULL,
  `menu_link` varchar(255) DEFAULT NULL,
  `published` tinyint(1) unsigned DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  `menu_class` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `#__eb_menus`
--

INSERT INTO `#__osmembership_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES
(1, 'OSM_DASHBOARD', 0, 'index.php?option=com_osmembership&view=dashboard', 1, 1, 'home'),

(2, 'OSM_SETUP', 0, NULL, 1, 2, 'list-view'),
(3, 'OSM_PLAN_CATEGORIES', 2, 'index.php?option=com_osmembership&view=categories', 1, 1, 'folder-open'),
(4, 'OSM_PLANS', 2, 'index.php?option=com_osmembership&view=plans', 1, 2, 'folder-close'),
(5, 'OSM_CUSTOM_FIELDS', 2, 'index.php?option=com_osmembership&view=fields', 1, 3, 'list'),
(6, 'OSM_TAX_RULES', 2, 'index.php?option=com_osmembership&view=taxes', 1, 4, 'location'),
(7, 'OSM_EMAIL_MESSAGES', 2, 'index.php?option=com_osmembership&view=mitems', 1, 5, 'envelope'),
(23, 'OSM_PAYMENT_PLUGINS', 2, 'index.php?option=com_osmembership&view=plugins', 1, 6, 'wrench'),
(8, 'OSM_COUNTRIES', 2, 'index.php?option=com_osmembership&view=countries', 1, 7, 'flag'),
(9, 'OSM_STATES', 2, 'index.php?option=com_osmembership&view=states', 1, 8, 'book'),
(50, 'OSM_DISCOUNTS', 2, 'index.php?option=com_osmembership&view=discounts', 1, 9, 'tags'),
(53, 'OSM_EXPORT_TEMPLATES', 2, 'index.php?option=com_osmembership&view=exporttmpls', 1, 10, 'tablet'),
(54, 'OSM_MASS_MAIL_TEMPLATES', 2, 'index.php?option=com_osmembership&view=mmtemplates', 1, 11, 'tablet'),
(52, 'OSM_BATCH_DISCOUNTS', 2, 'index.php?option=com_osmembership&view=discount&layout=batch', 1, 12, 'list'),

(10, 'OSM_SUBSCRIPTIONS', 0, NULL, 1, 3, 'user'),
(11, 'OSM_SUBSCRIPTIONS', 10, 'index.php?option=com_osmembership&view=subscriptions', 1, 1, 'folder-open'),
(12, 'OSM_SUBSCRIBERS', 10, 'index.php?option=com_osmembership&view=subscribers', 1, 2, 'user'),
(13, 'OSM_GROUPMEMBERS', 10, 'index.php?option=com_osmembership&view=groupmembers', 1, 3, 'user'),
(14, 'OSM_IMPORT', 10, 'index.php?option=com_osmembership&view=import', 1, 4, 'upload'),
(15, 'OSM_EXPORT', 10, 'index.php?option=com_osmembership&task=subscription.export', 1, 5, 'download'),
(16, 'OSM_CSV_IMPORT_TEMPLATE', 10, 'index.php?option=com_osmembership&task=subscription.csv_import_template', 1, 6, 'list'),
(32, 'OSM_EXPORT_EXPIRED_SUBSCRIBERS', 10, 'index.php?option=com_osmembership&task=subscription.export_expired_subscribers', 1, 7, 'download'),

(17, 'OSM_SUBSCRIBERS_REPORT', 0, 'index.php?option=com_osmembership&view=reports', 1, 4, 'bars'),

(18, 'OSM_COUPONS', 0, NULL, 1, 5, 'tags'),
(19, 'OSM_COUPONS', 18, 'index.php?option=com_osmembership&view=coupons', 1, 1, 'tags'),
(20, 'OSM_IMPORT', 18, 'index.php?option=com_osmembership&view=coupon&layout=import', 1, 2, 'upload'),
(21, 'OSM_EXPORT', 18, 'index.php?option=com_osmembership&task=coupon.export', 1, 3, 'download'),
(22, 'OSM_BATCH_COUPONS', 18, 'index.php?option=com_osmembership&view=coupon&layout=batch', 1, 4, 'list'),

(24, 'OSM_TRANSLATION', 0, 'index.php?option=com_osmembership&view=language', 1, 7, 'flag'),
(25, 'OSM_CONFIGURATION', 0, 'index.php?option=com_osmembership&view=configuration', 1, 8, 'cog'),

(26, 'OSM_TOOLS', 0, NULL, 1, 9, 'tools'),
(31, 'OSM_EMAILS_LOG', 26, 'index.php?option=com_osmembership&view=emails', 1, 1, 'envelope'),
(51, 'OSM_LEGACY_EMAIL_MESSAGES', 26, 'index.php?option=com_osmembership&view=message', 1, 2, 'envelope'),
(35, 'OSM_CHECKIN_LOGS', 26, 'index.php?option=com_osmembership&view=checkinlogs', 1, 3, 'envelope'),
(27, 'OSM_PURGE_URLS', 26, 'index.php?option=com_osmembership&task=tool.reset_urls', 1, 4, 'refresh'),
(28, 'OSM_FIX_DATABASE', 26, 'index.php?option=com_osmembership&task=update.update', 1, 5, 'ok'),
(29, 'OSM_SHARE_TRANSLATION', 26, 'index.php?option=com_osmembership&task=tool.share_translation', 1, 6, 'heart'),
(30, 'OSM_BUILD_EU_TAX_RULES', 26, 'javascript:confirmBuildTaxRules();', 1, 7, 'location'),
(34, 'Download MPDF Fonts', 26, 'index.php?option=com_osmembership&task=tool.download_mpdf_font', 1, 8, 'download'),
(33, 'OSM_DOWNLOAD_ID', 26, 'index.php?option=com_osmembership&view=downloadids', 1, 9, 'download');