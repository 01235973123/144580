CREATE TABLE IF NOT EXISTS `#__jd_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `field_type` tinyint(3) unsigned DEFAULT NULL,
  `required` tinyint(3) unsigned DEFAULT NULL,
  `values` text,
  `default_values` text,
  `rows` int(11) DEFAULT NULL,
  `cols` int(11) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `css_class` varchar(50) DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(3) unsigned DEFAULT NULL,
  `campaign_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8;

INSERT INTO `#__jd_fields` (`id`, `campaign_id`, `name`, `title`, `description`, `field_type`, `required`, `values`, `default_values`, `rows`, `cols`, `size`, `css_class`, `field_mapping`, `ordering`, `published`, `datatype_validation`, `extra_attributes`, `max_length`, `place_holder`, `multiple`, `validation_rules`, `validation_error_message`, `is_core`, `fieldtype`) VALUES
(1, 0, 'first_name', 'First Name', '', 1, 1, '', '', 0, 0, 0, 'form-control', '', 1, 1, 0, '', 0, '', 0, 'validate[required]', '', 1, 'Text'),
(2, 0, 'last_name', 'Last Name', '', 1, 1, '', '', 0, 0, 0, 'form-control', NULL, 2, 1, 0, '', 0, NULL, 0, 'validate[required]', NULL, 1, 'Text'),
(3, 0, 'organization', 'Organization', '', 1, 1, '', '', 0, 0, 0, 'form-control', NULL, 3, 1, 0, '', 0, '', 0, 'validate[required]', '', 1, 'Text'),
(4, 0, 'address', 'Address', '', 1, 1, '', '', 0, 0, 0, 'form-control', NULL, 4, 1, 0, '', 0, NULL, 0, 'validate[required]', NULL, 1, 'Text'),
(5, 0, 'address2', 'Address 2', '', 1, 0, '', '', 0, 0, 0, 'form-control', NULL, 5, 0, 0, '', 0, NULL, 0, '', NULL, 1, 'Text'),
(6, 0, 'city', 'City', '', 1, 1, '', '', 0, 0, 0, 'form-control', NULL, 6, 1, 0, '', 0, NULL, 0, 'validate[required]', NULL, 1, 'Text'),
(7, 0, 'zip', 'Zip', '', 1, 1, '', '', 0, 0, 0, 'form-control', NULL, 7, 1, 0, '', 0, NULL, 0, 'validate[required]', NULL, 1, 'Text'),
(8, 0, 'country', 'Country', '', 3, 1, '', '', 0, 0, 0, 'form-control', NULL, 8, 1, 0, '', 0, NULL, 0, 'validate[required]', NULL, 1, 'Countries'),
(9, 0, 'state', 'State', '', 1, 1, '', '', 0, 0, 0, 'form-control', NULL, 9, 1, 0, '', 0, '', 0, 'validate[required]', '', 1, 'Text'),
(10, 0, 'phone', 'Phone', '', 1, 1, '', '', 0, 0, 0, 'form-control', NULL, 10, 1, 0, '', 0, '', 0, 'validate[required]', '', 1, 'Text'),
(11, 0, 'fax', 'Fax', '', 1, 0, '', '', 0, 0, 0, 'form-control', NULL, 11, 0, 0, '', 0, '', 0, '', '', 1, 'Text'),
(12, 0, 'email', 'Email', '', 1, 1, '', '', 0, 0, 0, 'form-control', NULL, 12, 1, 0, '', 0, '', 0, 'validate[required,custom[email],ajax[ajaxEmailCall]]', '', 1, 'Text'),
(13, 0, 'comment', 'Comment', '', 2, 1, '', '', 7, 0, 0, 'form-control', NULL, 13, 1, 0, '', 0, '', 0, 'validate[required]', '', 1, 'Textarea');