<?php

/**
 * @version        5.9.5
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Ossolution
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Registry\Registry;
use Joomla\CMS\Filesystem\Folder;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Change the db structure of the previous version
 *
 */
class com_jdonationInstallerScript
{
	public static $languageFiles = array('en-GB.com_jdonation.ini');

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->app = Factory::getApplication();
	}


	/**
	 * Method to run before installing the component
	 */
	function preflight($type, $parent)
	{

		//Backup the old language file
		foreach (self::$languageFiles as $languageFile)
		{
			if (File::exists(JPATH_ROOT . '/language/en-GB/' . $languageFile))
			{
				File::copy(JPATH_ROOT . '/language/en-GB/' . $languageFile, JPATH_ROOT . '/language/en-GB/bak.' . $languageFile);
			}

			if (File::exists(JPATH_ROOT . '/administrator/language/en-GB/' . $languageFile))
			{
				File::copy(JPATH_ROOT . '/administrator/language/en-GB/' . $languageFile, JPATH_ROOT . '/administrator/language/en-GB/bak.' . $languageFile);
			}
		}	
	}

	/**
	 * Enable the plugins which are needed for the extension to work properly
	 *
	 * @return void
	 */
	public static function enableRequiredPlugin($type = '')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$publishedItems = [
			'system'    => [
				'jdonation',
			],
			'installer' => [
				'jdonation',
			],
		];

		foreach ($publishedItems as $folder => $plugins)
		{
			foreach ($plugins as $plugin)
			{
				$query->clear()
					->update('#__extensions')
					->set('enabled = 1')
					->where('element = ' . $db->quote($plugin))
					->where('folder = ' . $db->quote($folder));
				$db->setQuery($query)
					->execute();
			}
		}
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		$this->updateDatabaseSchema();
	}

	function updateDatabaseSchema()
	{
		$config = new JConfig();
		$dbname = $config->db;
		$prefix = $config->dbprefix;
		require_once JPATH_ROOT . '/components/com_jdonation/helper/helper.php';
		$db = Factory::getDbo();
		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			$db->setQuery("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
			$db->execute();
		}

		
		//Fix description field of the custom field
		$sql = 'ALTER TABLE  `#__jd_fields` CHANGE  `description`  `description` TEXT  NULL DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();
		$fields = array_keys($db->getTableColumns('#__jd_donors'));
		if (!in_array('campaign_id', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `campaign_id` INT NOT NULL DEFAULT '0' AFTER `id` ;";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('donation_type', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `donation_type` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('r_times', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `r_times` INT NOT NULL DEFAULT '0';";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('r_frequency', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `r_frequency` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('language', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `language` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('payment_made', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `payment_made` INT NOT NULL DEFAULT '0';";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('subscr_id', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `subscr_id` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('hide_me', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `hide_me` TINYINT NOT NULL DEFAULT '0' AFTER `email` ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('receive_user_id', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `receive_user_id` INT NOT NULL DEFAULT '0' AFTER `user_id` ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('address2', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `address2` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('fax', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `fax` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}


		if (!in_array('ip_address', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `ip_address` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('recurring_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `recurring_amount` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0' AFTER  `amount`;";
			$db->setQuery($sql);
			$db->execute();

			$sql = 'UPDATE #__jd_donors SET `recurring_amount` = `amount` WHERE donation_type="R"';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('payment_fee', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `payment_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0'";
			$db->setQuery($sql);
			$db->execute();
		}

		//from 4.5 to 4.6
		if (!in_array('currency_code', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `currency_code` VARCHAR(25) NOT NULL DEFAULT '';";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('amount_converted', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `amount_converted` DECIMAL( 12, 2 ) NOT NULL DEFAULT '0' AFTER `currency_code`";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('mollie_customer_id', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `mollie_customer_id` TEXT NULL";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('mollie_recurring_start_date', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `mollie_recurring_start_date` datetime NOT NULL";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('agree_privacy_policy', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `agree_privacy_policy` tinyint(1) NOT NULL DEFAULT '0'";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('currency_converted', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `currency_converted` TEXT NULL";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('newsletter_subscription', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `newsletter_subscription` tinyint(1) NOT NULL DEFAULT '0' after `agree_privacy_policy`";
			$db->setQuery($sql);
			$db->execute();
		}

		//5.4.9
		if (!in_array('show_dedicate', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `show_dedicate` tinyint(1) NOT NULL DEFAULT '0'";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('dedicate_type', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `dedicate_type` tinyint(1) NOT NULL DEFAULT '1'";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('dedicate_name', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `dedicate_name` TEXT NULL";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('dedicate_email', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `dedicate_email` TEXT NULL";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('params', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `params` TEXT NULL";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('recurring_donation_cancelled', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `recurring_donation_cancelled` TINYINT(1) NOT NULL DEFAULT 0";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('invoice_number', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `invoice_number` INT(11) NOT NULL DEFAULT 0";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('invoice_year', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_donors` ADD  `invoice_year` INT(4) NOT NULL DEFAULT 0";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('gateway_customer_id', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `gateway_customer_id` VARCHAR(255) NOT NULL DEFAULT ''; ";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('gift_aid', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `gift_aid` tinyint(1) NOT NULL DEFAULT '0'; ";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('username', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `username`  	varchar(50) NULL DEFAULT ''; ";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('user_password', $fields))
		{
			$sql = "ALTER TABLE `#__jd_donors` ADD `user_password`  varchar(250) NULL DEFAULT ''; ";
			$db->setQuery($sql);
			$db->execute();
		}

		//Campaign table : 2.2 to 2.3
		$fields = array_keys($db->getTableColumns('#__jd_campaigns'));

		// Handle Alias
		if (!in_array('alias', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_campaigns` ADD  `alias` TEXT NULL;";
			$db->setQuery($sql);
			$db->execute();
			$sql = 'SELECT id, title FROM #__jd_campaigns';
			$db->setQuery($sql);
			$rowCampaigns = $db->loadObjectList();
			if (count($rowCampaigns))
			{
				foreach ($rowCampaigns as $rowCampaign)
				{
					$alias = ApplicationHelper::stringUrlSafe($rowCampaign->title);
					$sql   = 'UPDATE #__jd_campaigns SET `alias`=' . $db->quote($alias) . ' WHERE id=' . $rowCampaign->id;
					$db->setQuery($sql);
					$db->execute();
				}
			}
		}

		//2.2 to 2.3
		if (!in_array('ordering', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `ordering` INT NOT NULL DEFAULT '0' AFTER `donated_amount` ;";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('user_email_subject', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `user_email_subject` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('user_email_body', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `user_email_body` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('recurring_email_subject', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `recurring_email_subject` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('recurring_email_body', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `recurring_email_body` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('notification_emails', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `notification_emails` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('donation_form_msg', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `donation_form_msg` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('confirmation_message', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `confirmation_message` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('thanks_message', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `thanks_message` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('thanks_message_offline', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `thanks_message_offline` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('cancel_message', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `cancel_message` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}
		#2.6
		if (!in_array('amounts', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `amounts` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('amounts_explanation', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `amounts_explanation` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}
		//2.6.1 to 2.6.2
		if (!in_array('authorize_api_login', $fields))
		{
			$sql = 'ALTER TABLE `#__jd_campaigns` ADD `authorize_api_login` TEXT NULL ;';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('authorize_transaction_key', $fields))
		{
			$sql = 'ALTER TABLE `#__jd_campaigns` ADD `authorize_transaction_key` TEXT NULL ;';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('paypal_id', $fields))
		{
			$sql = 'ALTER TABLE `#__jd_campaigns` ADD `paypal_id` TEXT NULL ;';
			$db->setQuery($sql);
			$db->execute();
		}
		//2.6.3 to 2.6.4
		if (!in_array('enable_recurring', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_campaigns` ADD  `enable_recurring` TINYINT NOT NULL DEFAULT  '1';";
			$db->setQuery($sql);
			$db->execute();
		}


		if (!in_array('donation_type', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `donation_type` TINYINT NOT NULL DEFAULT '0';";
			$db->setQuery($sql);
			$db->execute();
			$sql = 'UPDATE #__jd_campaigns SET donation_type = 1 WHERE enable_recurring = 0';
			$db->setQuery($sql);
			$db->execute();
		}

		//4.3 to 4.4
		if (!in_array('campaign_photo', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `campaign_photo` TEXT NULL;";
			$db->setQuery($sql);
			$db->execute();
		}

		//4.4 to 4.5
		if (!in_array('user_id', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `user_id` INT NOT NULL DEFAULT '0' AFTER `id` ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('paypal_redirection_message', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `paypal_redirection_message` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		//5.4.9
		if (!in_array('activate_dedicate', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `activate_dedicate` TINYINT(1) NOT NULL DEFAULT '0' ;";
			$db->setQuery($sql);
			$db->execute();
		}

		//5.5.10
		if (!in_array('payment_plugins', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `payment_plugins` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('from_name', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `from_name` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('from_email', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `from_email` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('limit_donors', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `limit_donors` int(9) NOT NULL DEFAULT '0' ;";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('short_description', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `short_description` text DEFAULT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('show_campaign', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `show_campaign` tinyint(1) NOT NULL DEFAULT '-1' ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('show_goal', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `show_goal` tinyint(1) NOT NULL DEFAULT '1' ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('currency', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `currency` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('meta_keywords', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `meta_keywords` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('meta_description', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `meta_description` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('browser_page_title', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `browser_page_title` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('use_parameter', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `use_parameter` tinyint (1) NOT NULL DEFAULT 0 ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('private_campaign', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `private_campaign` tinyint (1) NOT NULL DEFAULT 0 ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('currency_symbol', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `currency_symbol` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('user_email_body_offline', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `user_email_body_offline` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('recurring_frequencies', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `recurring_frequencies` TEXT NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('display_amount_textbox', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `display_amount_textbox` TINYINT(1) NOT NULL DEFAULT '0' ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('reply_email', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `reply_email` varchar(70) NULL ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('highlight_color', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `highlight_color` varchar(50) NULL DEFAULT 'FE9301';";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('border_highlight_color', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `border_highlight_color` varchar(50) NULL DEFAULT 'EB5901';";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('progress_color', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `progress_color` varchar(50) NULL DEFAULT '0e90d2';";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('gradient_progress_color', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `gradient_progress_color` varchar(50) NULL DEFAULT '149bdf';";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('gradient_progress_color1', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `gradient_progress_color1` varchar(50) NULL DEFAULT '0480be';";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('category_id', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `category_id` INT(11) NULL DEFAULT '0' ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('access', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `access` TINYINT (1) NOT NULL DEFAULT '1' ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('show_amounts', $fields))
		{
			$sql = "ALTER TABLE `#__jd_campaigns` ADD `show_amounts` TINYINT (1) NOT NULL DEFAULT '1' ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('min_donation_amount', $fields))
		{

			$sql = "ALTER TABLE `#__jd_campaigns` ADD `min_donation_amount` INT(11) NOT NULL DEFAULT '0' AFTER `access`, ADD `max_donation_amount` INT(11) NOT NULL DEFAULT '0' AFTER `min_donation_amount`; ";

			$db->setQuery($sql);
			$db->execute();
		}

		$db->setQuery("CREATE TABLE IF NOT EXISTS `#__jd_categories` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
					  `alias` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
					  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
					  `ordering` int(11) DEFAULT 0,
					  `access` int(11) NOT NULL DEFAULT 0,
					  `published` tinyint(1) NOT NULL DEFAULT 0,
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
		$db->execute();


		$db->setQuery("CREATE TABLE IF NOT EXISTS `#__jd_emails` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `email_type` varchar(200) DEFAULT NULL,
					  `sent_at` datetime DEFAULT NULL,
					  `sent_to` tinyint(4) NOT NULL,
					  `email` varchar(100) NOT NULL,
					  `subject` TEXT NOT NULL,
					  `body` text NOT NULL,
					  PRIMARY KEY (`id`)
					) AUTO_INCREMENT=1 ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;");
		$db->execute();

		//create table #__osrs_menus
		$db->setQuery("CREATE TABLE IF NOT EXISTS `#__jd_menus` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `menu_name` TEXT DEFAULT NULL,
					  `menu_parent_id` int(11) DEFAULT NULL,
					  `menu_link` TEXT DEFAULT NULL,
					  `published` tinyint(1) UNSIGNED DEFAULT NULL,
					  `ordering` int(11) DEFAULT NULL,
					  `menu_class` TEXT DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) AUTO_INCREMENT=1 ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci ;");
		$db->execute();
		
		$db->setQuery("Delete from `#__jd_menus`");
		$db->execute();
		
		$db->setQuery("INSERT INTO `#__jd_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES
					(1, 'JD_DASHBOARD', 0, 'index.php?option=com_jdonation&view=dashboard', 1, 1, 'home'),
					(2, 'JD_CAMPAIGNS', 0, 'index.php?option=com_jdonation&view=campaigns', 1, 3, 'list-view'),
					(3, 'JD_DONORS', 0, 'index.php?option=com_jdonation&view=donors', 1, 4, 'users'),
					(4, 'JD_PAYMENT_PLUGINS', 5, 'index.php?option=com_jdonation&view=plugins', 1, 2, 'shuffle'),
					(5, 'JD_OTHERS', 0, '', 1, 5, 'folder-open'),
					(6, 'JD_CUSTOM_FIELDS', 5, 'index.php?option=com_jdonation&view=fields', 1, 1, 'list'),
					(7, 'JD_IMPORT_DONORS', 5, 'index.php?option=com_jdonation&view=import', 1, 2, 'upload'),
					(8, 'JD_EXPORT_DONORS', 5, 'index.php?option=com_jdonation&task=donor.export', 1, 3, 'download'),
					(9, 'JD_TRANSLATION', 5, 'index.php?option=com_jdonation&view=language', 1, 4, 'flag'),
					(10, 'JD_CONFIGURATION', 0, 'index.php?option=com_jdonation&view=configuration', 1, 6, 'cog'),
					(11, 'JD_TOOLS', 0, '', 1, 11, 'tools'),
					(12, 'JD_SHARE_TRANSLATION', 11, 'index.php?option=com_jdonation&task=tool.share_translation', 1, 12, 'flag'),
					(13, 'JD_PURGE_URLS', 11, 'index.php?option=com_jdonation&task=tool.reset_urls', 1, 13, 'refresh'),
					(14, 'JD_REMOVE_UNPAID_DONATIONS', 11, 'index.php?option=com_jdonation&task=tool.remove_unpaid', 1, 14, 'delete'),
					(15, 'JD_STATISTIC', 0, 'index.php?option=com_jdonation&view=statistic', 1, 12, 'zoom-in'),
					(16, 'JD_EMAIL_LOGS', 11, 'index.php?option=com_jdonation&view=emails', 1, 15, 'mail'),
					(17, 'JD_REPORT', 11, 'index.php?option=com_jdonation&view=report', 1, 3, 'edit'),
					(18, 'JD_CATEGORIES', 0, 'index.php?option=com_jdonation&view=categories', 1, 2, 'folder-open'),
					(19, 'JD_BACKUP', 11, 'index.php?option=com_jdonation&view=export', 1, 16, 'download'),
					(20, 'JD_RESTORE', 11, 'index.php?option=com_jdonation&view=importdb', 1, 17, 'upload');");
		$db->execute();

		$db->setQuery("Select count(id) from #__jd_menus where `menu_name` like 'JD_CONFIGURATION'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("INSERT INTO `#__jd_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES (10, 'JD_CONFIGURATION', 0, 'index.php?option=com_jdonation&view=configuration', 1, 6, 'cog')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_menus where `menu_name` like 'JD_TOOLS'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("INSERT INTO `#__jd_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES (11, 'JD_TOOLS', 0, '', 1, 11, 'tools')");
			$db->execute();
		}
		$db->setQuery("Select count(id) from #__jd_menus where `menu_name` like 'JD_SHARE_TRANSLATION'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("INSERT INTO `#__jd_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES (12, 'JD_SHARE_TRANSLATION', 11, 'index.php?option=com_jdonation&task=tool.share_translation', 1, 12, 'flag')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_menus where `menu_name` like 'JD_PURGE_URLS'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("INSERT INTO `#__jd_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES (13, 'JD_PURGE_URLS', 11, 'index.php?option=com_jdonation&task=tool.reset_urls', 1, 13, 'refresh')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_menus where `menu_name` like 'JD_REMOVE_UNPAID_DONATIONS'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("INSERT INTO `#__jd_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES (14, 'JD_REMOVE_UNPAID_DONATIONS', 11, 'index.php?option=com_jdonation&task=tool.remove_unpaid', 1, 14, 'delete')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_menus where `menu_name` like 'JD_STATISTIC'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("INSERT INTO `#__jd_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES(15, 'JD_STATISTIC', 0, 'index.php?option=com_jdonation&view=statistic', 1, 12, 'zoom-in');");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_menus where `menu_name` like 'JD_EMAIL_LOGS'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("INSERT INTO `#__jd_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES(16, 'JD_EMAIL_LOGS', 11, 'index.php?option=com_jdonation&view=emails', 1, 15, 'mail');");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_menus where `menu_name` like 'JD_REPORT'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("INSERT INTO `#__jd_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES(17, 'JD_REPORT', 5, 'index.php?option=com_jdonation&view=report', 1, 3, 'edit');");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_menus where `menu_name` like 'JD_CATEGORIES'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("INSERT INTO `#__jd_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES(18, 'JD_CATEGORIES', 0, 'index.php?option=com_jdonation&view=categories', 1, 2, 'folder-open');");
			$db->execute();
		}

		$db->setQuery("Update `#__jd_menus` set `ordering` = '3' where `menu_name` = 'JD_CAMPAIGNS'");
		$db->execute();

		$db->setQuery("Update `#__jd_menus` set `ordering` = '4' where `menu_name` = 'JD_DONORS'");
		$db->execute();

		$db->setQuery("Update `#__jd_menus` set `menu_parent_id` = '5', `ordering` = '2' where `menu_name` = 'JD_PAYMENT_PLUGINS'");
		$db->execute();

		$db->setQuery("Select count(id) from #__jd_currencies where `currency_code` like 'KES'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("INSERT INTO `#__jd_currencies` (`id`, `currency_code`, `currency_name`) VALUES (NULL, 'KES', 'Kenyan Shilling'); ");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_currencies where `currency_code` like 'CNY'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("INSERT INTO `#__jd_currencies` (`id`, `currency_code`, `currency_name`) VALUES (NULL, 'CNY', 'Chinese Yuan'); ");
			$db->execute();
		}

		$sql = ' ALTER TABLE `#__jd_configs` CHANGE `config_value` `config_value` TEXT NULL DEFAULT NULL ';
		$db->setQuery($sql);
		$db->execute();
		//Load config config data
		$sql = 'SELECT COUNT(*) FROM #__jd_configs';
		$db->setQuery($sql);
		$total = $db->loadResult();
		if (!$total)
		{
			$configSql = JPATH_ADMINISTRATOR . '/components/com_jdonation/sql/config.jdonation.sql';
			$sql       = file_get_contents($configSql);
			$queries   = $db->splitSql($sql);
			if (count($queries))
			{
				foreach ($queries as $query)
				{
					if ($query != '' && $query[0] != '#')
					{
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
			//Invoice data
			$sql = 'SELECT COUNT(*) FROM #__jd_configs WHERE config_key="activate_donation_receipt_feature"';
			$db->setQuery($sql);
			$total = $db->loadResult();
			if (!$total)
			{
				$pluginsSql = JPATH_ADMINISTRATOR . '/components/com_jdonation/sql/config.invoice.sql';
				$sql        = file_get_contents($pluginsSql);
				$queries    = $db->splitSql($sql);
				if (count($queries))
				{
					foreach ($queries as $query)
					{
						$query = trim($query);
						if ($query != '' && $query[0] != '#')
						{
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
			}

			$sql = 'UPDATE #__jd_configs SET config_value="m-d-Y" WHERE config_key="date_format"';
			$db->setQuery($sql);
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_configs where config_key = 'social_sharing'");
		$count = $db->loadResult();
		if($count == 0){
			$db->setQuery("Insert into #__jd_configs (id,config_key,config_value) values (NULL,'social_sharing','1')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_configs where config_key = 'honoree_email_subject'");
		$count = $db->loadResult();
		if($count == 0){
			$db->setQuery("Insert into #__jd_configs (id,config_key,config_value) values (NULL,'honoree_email_subject','Someone just gave donation in remembrance of you')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_configs where config_key = 'honoree_email_body'");
		$count = $db->loadResult();
		if($count == 0){
			$db->setQuery("Insert into #__jd_configs (id,config_key,config_value) values (NULL,'honoree_email_body','&lt;p&gt;Dear [HONOREE_NAME],&lt;/p&gt; &lt;p&gt;A donation was made in [DEDICATE_TYPE] [HONOREE_NAME] by [FIRST_NAME] [LAST_NAME]. The [AMOUNT] donation that [FIRST_NAME] made to the [CAMPAIGN] fund in your name will help our cause and be put to good use making a difference.&lt;/p&gt;&lt;p&gt;With our thanks,&lt;/p&gt;')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_configs where config_key = 'cancel_recurring_email_subject'");
		$count = $db->loadResult();
		if($count == 0){
			$db->setQuery("Insert into #__jd_configs (id,config_key,config_value) values (NULL,'cancel_recurring_email_subject','Recurring donation cancelled confirmation')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_configs where config_key = 'cancel_recurring_email_body'");
		$count = $db->loadResult();
		if($count == 0){
			$db->setQuery("Insert into #__jd_configs (id,config_key,config_value) values (NULL,'cancel_recurring_email_body','&lt;p&gt;Dear &lt;strong&gt;[FIRST_NAME] [LAST_NAME]&lt;/strong&gt;&lt;/p&gt;&lt;p&gt;Your recurring donation for plan &lt;strong&gt;[CAMPAIGN]&lt;/strong&gt; has just been cancelled.&lt;/p&gt;&lt;p&gt;Regards,&lt;/p&gt;&lt;p&gt;Company Name&lt;/p&gt;')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_configs where config_key = 'cancel_recurring_admin_email_subject'");
		$count = $db->loadResult();
		if($count == 0){
			$db->setQuery("Insert into #__jd_configs (id,config_key,config_value) values (NULL,'cancel_recurring_admin_email_subject','Recurring donation cancelled')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_configs where config_key = 'cancel_recurring_admin_email_body'");
		$count = $db->loadResult();
		if($count == 0){
			$db->setQuery("Insert into #__jd_configs (id,config_key,config_value) values (NULL,'cancel_recurring_admin_email_body','&lt;p&gt;Dear Administrator&lt;/p&gt;&lt;p&gt;User &lt;strong&gt;[FIRST_NAME] [LAST_NAME]&lt;/strong&gt; has just cancelled his recurring donation for &lt;strong&gt;[CAMPAIGN]&lt;/strong&gt;.&lt;/p&gt;&lt;p&gt;Regards,&lt;/p&gt;&lt;p&gt;Company Name&lt;/p&gt;')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_configs where config_key = 'share_campaign_sbj'");
		$count = $db->loadResult();
		if($count == 0){
			$db->setQuery("Insert into #__jd_configs (id,config_key,config_value) values (NULL,'share_campaign_sbj','Help Us Make a Difference Donate Today')");
			$db->execute();
		}

		$db->setQuery("Select count(id) from #__jd_configs where config_key = 'share_campaign_body'");
		$count = $db->loadResult();
		if($count == 0)
		{
			$db->setQuery("Insert into #__jd_configs (id,config_key,config_value) values (NULL,'share_campaign_body','&lt;p&gt;Dear [FRIEND_NAME],&lt;BR /&gt;We hope this message finds you well. Today, we&#039;re reaching out to share an exciting opportunity to make a real difference in the lives of those who need it most.&lt;BR /&gt;At [CAMPAIGN_NAME], we are dedicated to [briefly describe your cause &ndash; e.g., &#039;providing essential education to underprivileged children&#039; or &#039;offering life-saving medical treatments to those in need&#039;]. But to continue our vital work, we need your help.&lt;BR /&gt;Here&rsquo;s how you can make a difference: Your generosity will help us reach our goal of raising [CAMPAIGN_GOAL] to fund critical initiatives and bring lasting change.&lt;BR /&gt;How to Contribute: It&rsquo;s quick and easy to donate. Simply click the button below to make your contribution: [CAMPAIGN_LINK]&lt;BR /&gt;[CAMPAIGN_QR]&lt;BR /&gt;Your support helps us continue our mission and ensures that those who need it most have the resources and care they deserve.&lt;BR /&gt;Thank you for being a part of this important journey. Your generosity has the power to change lives.&lt;BR /&gt;With gratitude,&lt;BR /&gt;[MY_NAME]&lt;BR /&gt;[MY_EMAIL]&lt;/p&gt;')");
			$db->execute();
		}

		$sql = 'SELECT config_value FROM #__jd_configs WHERE config_key="m_address2"';
		$db->setQuery($sql);
		$configValue = $db->loadResult();
		if (!$configValue)
		{
			$sql = 'UPDATE #__jd_configs SET config_value="address2" WHERE config_key="m_address2"';
			$db->setQuery($sql);
			$db->execute();
		}
		$sql = 'SELECT config_value FROM #__jd_configs WHERE config_key="m_fax"';
		$db->setQuery($sql);
		$configValue = $db->loadResult();
		if (!$configValue)
		{
			$sql = 'UPDATE #__jd_configs SET config_value="fax" WHERE config_key="m_fax"';
			$db->setQuery($sql);
			$db->execute();
		}

		$sql = 'SELECT config_value FROM #__jd_configs WHERE config_key="js_address2"';
		$db->setQuery($sql);
		$configValue = $db->loadResult();
		if (!$configValue)
		{
			$sql = 'UPDATE #__jd_configs SET config_value="address2" WHERE config_key="js_address2"';
			$db->setQuery($sql);
			$db->execute();
		}
		$sql = 'SELECT config_value FROM #__jd_configs WHERE config_key="js_fax"';
		$db->setQuery($sql);
		$configValue = $db->loadResult();
		if (!$configValue)
		{
			$sql = 'UPDATE #__jd_configs SET config_value="fax" WHERE config_key="js_fax"';
			$db->setQuery($sql);
			$db->execute();
		}
		$replaces = array(
			'{donation_detail}' => '[DONATION_DETAIL]',
			'{donation_amount}' => '[DONATION_AMOUNT]',
			'{campaign}'        => '[CAMPAIGN]',
			'{first_name}'      => '[FIRST_NAME]',
			'{last_name}'       => '[LAST_NAME]',
			'{organization}'    => '[ORGANIZATION]',
			'{address}'         => '[ADDRESS]',
			'{address2}'        => '[ADDRESS2]',
			'{city}'            => '[CITY]',
			'{state}'           => '[STATE]',
			'{zip}'             => '[ZIP]',
			'{country}'         => '[COUNTRY]',
			'{phone}'           => '[PHONE]',
			'{fax}'             => '[FAX]',
			'{email}'           => '[EMAIL]',
			'{comment}'         => '[COMMENT]',
			'{amount}'          => '[AMOUNT]',
			'{Name}'            => '[NAME]'
		);
		foreach ($replaces as $key => $value)
		{
			$sql = 'UPDATE #__jd_configs SET config_value = REPLACE(config_value, "' . $key . '", "' . $value . '")';
			$db->setQuery($sql);
			$db->execute();
		}

		$sql = 'SELECT count(id) FROM #__jd_configs WHERE config_key="show_update_available_message_in_dashboard"';
		$db->setQuery($sql);
		$configValue = $db->loadResult();
		if ((int)$configValue == 0)
		{
			$sql = "Insert into #__jd_configs (id, config_key, config_value) values (NULL, 'show_update_available_message_in_dashboard', 1)";
			$db->setQuery($sql);
			$db->execute();
		}		

		$config = DonationHelper::getConfig();

		$db->setQuery("CREATE TABLE IF NOT EXISTS `#__jd_email_templates` (
					  `id` int NOT NULL AUTO_INCREMENT,
					  `email_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
					  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
					  `content` text COLLATE utf8mb4_unicode_ci,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
		$db->execute();

		//Check the payment plugins database
		$sql = 'SELECT COUNT(*) FROM #__jd_email_templates';
		$db->setQuery($sql);
		$total = $db->loadResult();
		if (!$total)
		{
			$configSql = JPATH_ADMINISTRATOR . '/components/com_jdonation/sql/emais.joomdonation.sql';
			$sql       = file_get_contents($configSql);
			$queries   = $db->splitSql($sql);
			if (count($queries))
			{
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query[0] != '#')
					{
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}

		$sql = 'SELECT COUNT(*) FROM #__jd_payment_plugins';
		$db->setQuery($sql);
		$total = $db->loadResult();
		if (!$total)
		{
			$configSql = JPATH_ADMINISTRATOR . '/components/com_jdonation/sql/plugins.jdonation.sql';
			$sql       = file_get_contents($configSql);
			$queries   = $db->splitSql($sql);
			if (count($queries))
			{
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query[0] != '#')
					{
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
			if (isset($config->paypal_id))
			{
				//Paypal
				$txt   = array();
				$txt[] = 'paypal_id=' . $config->paypal_id;
				$txt[] = 'paypal_mode=' . $config->paypal_mode;
				$txt[] = 'paypal_currency=' . $config->paypal_currency;
				$txt   = implode("\n", $txt);
				$sql   = 'UPDATE  #__jd_payment_plugins SET params=' . $db->Quote($txt) . ' WHERE name="os_paypal"';
				$db->setQuery($sql);
				$db->execute();
				//Authorize.net
				$txt   = array();
				$txt[] = 'authnet_mode=' . $config->authnet_mode;
				$txt[] = 'x_login=' . $config->x_login;
				$txt[] = 'x_tran_key=' . $config->x_tran_key;
				$txt   = implode("\n", $txt);
				$sql   = 'UPDATE  #__jd_payment_plugins SET params=' . $db->Quote($txt) . ' WHERE name="os_authnet"';
				$db->setQuery($sql);
				$db->execute();
				//Eway
				$txt   = array();
				$txt[] = 'mb_merchant_email=' . $config->mb_merchant_email;
				$txt[] = 'mb_merchant_id=' . $config->mb_merchant_id;
				$txt[] = 'mb_secret_word=' . $config->mb_secret_word;
				$txt[] = 'mb_currency=' . $config->mb_currency;
				$txt   = implode("\n", $txt);
				$sql   = 'UPDATE  #__jd_payment_plugins SET params=' . $db->Quote($txt) . ' WHERE name="os_moneybooker"';
				$db->setQuery($sql);
				$db->execute();
			}
		}

		$fields = array_keys($db->getTableColumns('#__jd_payment_plugins'));
		if (!in_array('access', $fields))
		{
			$sql = "ALTER TABLE `#__jd_payment_plugins` ADD `access` INT(11) NOT NULL DEFAULT '1' AFTER `published`;";
			$db->setQuery($sql);
			$db->execute();
		}

		$fields = array_keys($db->getTableColumns('#__jd_payment_plugins'));
		if (!in_array('payment_description', $fields))
		{
			$sql = "ALTER TABLE `#__jd_payment_plugins` ADD `payment_description` TEXT NULL;";
			$db->setQuery($sql);
			$db->execute();
		}

		##Version 3.6, fields structure
		$fields = array_keys($db->getTableColumns('#__jd_fields'));
		if (!in_array('datatype_validation', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `datatype_validation` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('extra_attributes', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `extra_attributes` TEXT NULL;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('max_length', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `max_length` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('place_holder', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD   `place_holder` TEXT NULL;";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('multiple', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `multiple` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('validation_rules', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `validation_rules` TEXT NULL;";
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('validation_error_message', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `validation_error_message` TEXT NULL;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('field_mapping', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `field_mapping` TEXT NULL;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('input_mask', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `input_mask` varchar (255) NULL;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('container_size', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `container_size` varchar (255) NULL;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('container_class', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `container_class` varchar (255) NULL;";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('access', $fields))
		{
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `access` TINYINT (1) NULL DEFAULT '1';";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('is_core', $fields))
		{
			$sql = "ALTER TABLE `#__jd_fields` ADD `is_core` TINYINT(1) NOT NULL DEFAULT '0' ";
			$db->setQuery($sql);
			$db->execute();
			$sql = "ALTER TABLE  `#__jd_fields` ADD  `fieldtype` TEXT NULL;";
			$db->setQuery($sql);
			$db->execute();
			//Setup core fields
			$sql = 'UPDATE #__jd_fields SET id=id+13, ordering = ordering + 13 ORDER BY id DESC';
			$db->setQuery($sql);
			$db->execute();
			$sql = 'UPDATE #__jd_field_value SET field_id=field_id + 13';
			$db->setQuery($sql);
			$db->execute();
			$coreFieldsSql = JPATH_ADMINISTRATOR . '/components/com_jdonation/sql/fields.jdonation.sql';
			$sql           = file_get_contents($coreFieldsSql);
			$queries       = $db->splitSql($sql);
			if (count($queries))
			{
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query[0] != '#')
					{
						$db->setQuery($query);
						$db->execute();
					}
				}
			}

			

			$sql = 'SELECT MAX(id) FROM #__jd_fields';
			$db->setQuery($sql);
			$maxId         = (int) $db->loadResult();
			$autoincrement = $maxId + 1;
			$sql           = 'ALTER TABLE #__jd_fields AUTO_INCREMENT=' . $autoincrement;
			$db->setQuery($sql);
			$db->execute();
			//Update field type , change it to something meaningful
			$typeMapping = array(
				0 => 'Text',
				1 => 'Textarea',
				2 => 'List',
				3 => 'Checkboxes',
				4 => 'Radio',
				5 => 'Date',
				6 => 'Heading',
				7 => 'Message');

			foreach ($typeMapping as $key => $value)
			{
				$sql = "UPDATE #__jd_fields SET fieldtype='$value' WHERE field_type='$key' AND is_core = 0";
				$db->setQuery($sql);
				$db->execute();
			}

			$db->setQuery("ALTER TABLE `#__jd_fields` CHANGE `rows` `rows` INT(11) NULL DEFAULT '0', CHANGE `cols` `cols` INT(11) NULL DEFAULT '0', CHANGE `size` `size` INT(11) NULL DEFAULT '0';");
			$db->execute();

			$sql = "UPDATE #__jd_fields SET fieldtype='List', multiple=1 WHERE field_type='8'";
			$db->setQuery($sql);
			$db->execute();

			$sql = 'UPDATE #__jd_fields SET fieldtype="Countries" WHERE name="country"';
			$db->setQuery($sql);
			$db->execute();
			//MySql, convert data to Json
			$sql = 'SELECT id, field_value FROM #__jd_field_value WHERE field_id IN (SELECT id FROM #__jd_fields WHERE field_type=3 OR field_type=8)';
			$db->setQuery($sql);
			$rowFieldValues = $db->loadObjectList();
			if (count($rowFieldValues))
			{
				foreach ($rowFieldValues as $rowFieldValue)
				{
					$fieldValue = $rowFieldValue->field_value;
					if (strpos($fieldValue, ',') !== false)
					{
						$fieldValue = explode(',', $fieldValue);
					}
					$fieldValue = json_encode($fieldValue);
					$sql        = 'UPDATE #__jd_field_value SET field_value=' . $db->quote($fieldValue) . ' WHERE id=' . $rowFieldValue->id;
					$db->setQuery($sql);
					$db->execute();
				}
			}
			//if ($config->display_state_dropdown)
			//{
				$sql = 'UPDATE #__jd_fields SET fieldtype="State" WHERE name="state"';
				$db->setQuery($sql);
				$db->execute();
			//}

			//Process publish status of core fields
			/*
			$publishStatus = array(
				'first_name'   => 1,
				'last_name'    => $config->s_lastname,
				'organization' => $config->s_organization,
				'address'      => $config->s_address,
				'address2'     => $config->s_address2,
				'city'         => $config->s_city,
				'state'        => $config->s_state,
				'zip'          => $config->s_zip,
				'country'      => $config->s_country,
				'phone'        => $config->s_phone,
				'fax'          => $config->s_fax,
				'comment'      => $config->s_comment,
				'email'        => 1);

			foreach ($publishStatus as $key => $value)
			{
				$value = (int) $value;
				$sql   = 'UPDATE #__jd_fields SET published=' . $value . ' WHERE name=' . $db->quote($key);
				$db->setQuery($sql);
				$db->execute();
			}

			$requiredStatus = array(
				'first_name'   => 1,
				'last_name'    => $config->r_lastname,
				'organization' => $config->r_organization,
				'address'      => $config->r_address,
				'address2'     => $config->r_address2,
				'city'         => $config->r_city,
				'state'        => $config->r_state,
				'zip'          => $config->r_zip,
				'country'      => $config->r_country,
				'phone'        => $config->r_phone,
				'fax'          => $config->r_fax,
				'comment'      => $config->r_comment,
				'email'        => 1);

			foreach ($requiredStatus as $key => $value)
			{
				$value = (int) $value;
				$sql   = 'UPDATE #__jd_fields SET required=' . $value . ' WHERE name=' . $db->quote($key);
				$db->setQuery($sql);
				$db->execute();
			}
			*/
		}


		$sql = "SELECT id, validation_rules FROM #__jd_fields WHERE required = 1";
		$db->setQuery($sql);
		$fields = $db->loadObjectList();
		foreach ($fields as $field)
		{
			if (empty($field->validation_rules))
			{
				$sql = 'UPDATE #__jd_fields SET validation_rules = "validate[required]" WHERE id=' . $field->id;
				$db->setQuery($sql);
				$db->execute();
			}
		}
		//Make sure validation is empty when required=0
		$sql = 'UPDATE #__jd_fields SET validation_rules = "" WHERE required=0 AND validation_rules="validate[required]"';
		$db->setQuery($sql);
		$db->execute();
		// Urls table to support sef router

		$sql = "CREATE TABLE IF NOT EXISTS `#__jd_urls` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`md5_key` text,
			`query` text,
			PRIMARY KEY (`id`)
			) AUTO_INCREMENT=1 ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
		";
		$db->setQuery($sql);
		$db->execute();
		$db->truncateTable('#__jd_urls');


		// Multilingual
		//if (Multilanguage::isEnabled())
		//{
			//DonationHelper::setupMultilingual();
		//}
	}

	function update($parent)
	{
		$this->updateDatabaseSchema();
	}

	/**
	 * Method to run after installing the component
	 */
	function postflight($type, $parent)
	{
		// We do not have to do anything on uninstall
		if (strtolower($type) == 'uninstall')
		{
			return;
		}

		//Restore the modified language strings by merging to language files
		
		foreach (self::$languageFiles as $languageFile)
		{
			$registry = new Registry();
			$backupFile  = JPATH_ROOT . '/language/en-GB/bak.' . $languageFile;
			$currentFile = JPATH_ROOT . '/language/en-GB/' . $languageFile;
			if (File::exists($currentFile) && File::exists($backupFile))
			{
				$registry->loadFile($currentFile, 'INI');
				$currentItems = $registry->toArray();
				$registry->loadFile($backupFile, 'INI');
				$backupItems = $registry->toArray();
				$items       = array_merge($currentItems, $backupItems);
				$content     = "";
				foreach ($items as $key => $value)
				{
					$content .= "$key=\"$value\"\n";
				}
				File::write($currentFile, $content);
				//Delete the backup file
				//JFile::delete($backupFile);
			}

			/*
			$registry = new Registry();
			$backupFile  = JPATH_ADMINISTRATOR . '/language/en-GB/bak.' . $languageFile;
			$currentFile = JPATH_ADMINISTRATOR . '/language/en-GB/' . $languageFile;
			if (File::exists($currentFile) && File::exists($backupFile))
			{
				$registry->loadFile($currentFile, 'INI');
				$currentItems = $registry->toArray();
				$registry->loadFile($backupFile, 'INI');
				$backupItems = $registry->toArray();
				$items       = array_merge($currentItems, $backupItems);
				$content     = "";
				foreach ($items as $key => $value)
				{
					$content .= "$key=\"$value\"\n";
				}
				File::write($currentFile, $content);
				//Delete the backup file
				//JFile::delete($backupFile);
			}
			*/
		}

		//Create a blank css file
		$customCss = JPATH_ROOT . '/media/com_jdonation/assets/css/custom.css';
		if (!file_exists($customCss))
		{
			$fp = fopen($customCss, 'w');
			fclose($fp);
			@chmod($customCss, 0644);
		}
		else
		{
			@chmod($customCss, 0644);
		}

		if (Folder::exists(JPATH_ROOT . '/components/com_jdonation/assets'))
		{
			Folder::delete(JPATH_ROOT . '/components/com_jdonation/assets');
		}

		if (!Folder::exists(JPATH_ROOT. '/images/jdonation')){
			Folder::create(JPATH_ROOT. '/images/jdonation');
		}

		@chmod(JPATH_ROOT . '/components/com_jdonation/tcpdf/cache', 0777);


		require_once JPATH_ROOT . '/components/com_jdonation/helper/helper.php';
		
		$db = Factory::getDbo();
		$db->setQuery("Select count(id) from #__jd_campaigns");
		$count1 = $db->loadResult();

		$db->setQuery("Select count(id) from #__jd_donors");
		$count2 = $db->loadResult();

		if(DonationHelper::isJoomla4() && $count1 == 0 && $count2 == 0 && $type == "install")
		{
			$db->setQuery("Update #__jd_configs set config_value = '5' where config_key = 'twitter_bootstrap_version'");
			$db->execute();

			$db->setQuery("Update #__jd_configs set config_value = '0' where config_key = 'load_twitter_bootstrap'");
			$db->execute();
		}

		$tables = [
			'#__jd_campaigns',
			'#__jd_donors',
			'#__jd_fields',
			'#__jd_emails',
			'#__jd_states',
		];

		try
		{
			foreach ($tables as $table)
			{
				$sql = "ALTER TABLE `$table` ROW_FORMAT = DYNAMIC";
				$db->setQuery($sql)
					->execute();
			}
		}
		catch (Exception $e)
		{
		}

		// Enable required plugins
		self::enableRequiredPlugin($type);
	}
}
