<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class pkg_eshopInstallerScript
{
	private $installedVersion;

	/**
	 * Language files
	 *
	 * @var array
	 */
	public static $languageFiles = ['en-GB.com_eshop.ini', 'admin.en-GB.com_eshop.ini'];

	/**
	 *
	 * Function to run before installing the component
	 */
	public function preflight($type, $parent)
	{
		//Backup the old language files
		foreach (self::$languageFiles as $languageFile)
		{
			if (strpos($languageFile, 'admin') !== false)
			{
				$languageFolder = JPATH_ADMINISTRATOR . '/language/en-GB/';
				$languageFile   = substr($languageFile, 6);
			}
			else
			{
				$languageFolder = JPATH_ROOT . '/language/en-GB/';
			}

			if (is_file($languageFolder . $languageFile))
			{
				File::copy($languageFolder . $languageFile, $languageFolder . 'bak.' . $languageFile);
			}
		}

		//Delete log file of payment gateway if it is existed
		if (is_file(JPATH_ROOT . '/components/com_eshop/ipn_logs.txt'))
		{
			File::delete(JPATH_ROOT . '/components/com_eshop/ipn_logs.txt');
		}
		//Backup files which need to be keep 
		if (is_file(JPATH_ROOT . '/components/com_eshop/fields.xml'))
		{
			File::copy(JPATH_ROOT . '/components/com_eshop/fields.xml', JPATH_ROOT . '/components/com_eshop/bak.fields.xml');
		}

		if (strtoupper($type) === 'update')
		{
			// Check and store installed version
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('manifest_cache')
				->from('#__extensions')
				->where($db->quoteName('element') . ' = "com_eshop"')
				->where($db->quoteName('type') . ' = "component"');
			$db->setQuery($query);
			$manifestCache = $db->loadResult();

			if ($manifestCache)
			{
				$manifest               = json_decode($manifestCache);
				$this->installedVersion = $manifest->version;
			}
		}
	}

	/**
	 *
	 * Function to run when installing the component
	 * @return void
	 */
	public function install($parent)
	{
		$this->updateDatabaseSchema(false);
		$this->displayEshopWelcome(false);
	}

	/**
	 *
	 * Function to run when updating the component
	 * @return void
	 */
	function update($parent)
	{
		$this->updateDatabaseSchema(true);
		$this->displayEshopWelcome(true);
	}

	/**
	 *
	 * Function to update database schema
	 */
	public function updateDatabaseSchema($update)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->clear()
			->update('#__extensions')
			->set('enabled = 1')
			->where('element = "eshop"')
			->where('folder = "installer"');
		$db->setQuery($query)
			->execute();

		if ($update)
		{
			//Rename old checkout folder of other themes
			$query->clear()
				->select('*')
				->from('#__eshop_themes')
				->where('name != "default" AND name != "fashionpro"');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			if (count($rows))
			{
				foreach ($rows as $row)
				{
					if (is_dir(JPATH_ROOT . '/components/com_eshop/themes/' . $row->name . '/views/checkout') && !is_dir(
							JPATH_ROOT . '/components/com_eshop/themes/' . $row->name . '/views/checkout_backup'
						))
					{
						Folder::copy(
							JPATH_ROOT . '/components/com_eshop/themes/' . $row->name . '/views/checkout',
							JPATH_ROOT . '/components/com_eshop/themes/' . $row->name . '/views/checkout_backup'
						);
						Folder::delete(JPATH_ROOT . '/components/com_eshop/themes/' . $row->name . '/views/checkout');
					}
				}
			}
		}
		$query->clear();
		$query->select('COUNT(*)')
			->from('#__eshop_configs');
		$db->setQuery($query);
		$total = $db->loadResult();
		if (!$total)
		{
			$configSql = JPATH_ADMINISTRATOR . '/components/com_eshop/sql/config.eshop.sql';
			$query     = file_get_contents($configSql);
			$queries   = $db->splitSql($query);
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

		$query = $db->getQuery(true);
		$query->clear();
		$query->select('id')
			->from('#__eshop_configs')
			->where('config_Key = "product_fields_display"');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_configs')
				->values('0, "product_fields_display", "product_sku,product_quantity"');
			$db->setQuery($query);
			$db->execute();
		}
		
		$query->clear();
		$query->select('id')
			->from('#__eshop_configs')
			->where('config_Key = "notify_button_code"');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_configs')
				->values('0, "notify_button_code", "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" viewBox=\"0 0 448 512\"><path d=\"M224 0c-17.7 0-32 14.3-32 32V51.2C119 66 64 130.6 64 208v18.8c0 47-17.3 92.4-48.5 127.6l-7.4 8.3c-8.4 9.4-10.4 22.9-5.3 34.4S19.4 416 32 416H416c12.6 0 24-7.4 29.2-18.9s3.1-25-5.3-34.4l-7.4-8.3C401.3 319.2 384 273.9 384 226.8V208c0-77.4-55-142-128-156.8V32c0-17.7-14.3-32-32-32zm45.3 493.3c12-12 18.7-28.3 18.7-45.3H224 160c0 17 6.7 33.3 18.7 45.3s28.3 18.7 45.3 18.7s33.3-6.7 45.3-18.7z\"/></svg>"');
			$db->setQuery($query);
			$db->execute();
		}
		
		$query->clear();
		$query->select('id')
			->from('#__eshop_configs')
			->where('config_Key = "wishlist_button_code"');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_configs')
				->values('0, "wishlist_button_code", "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" viewBox=\"0 0 512 512\"><path d=\"M225.8 468.2l-2.5-2.3L48.1 303.2C17.4 274.7 0 234.7 0 192.8v-3.3c0-70.4 50-130.8 119.2-144C158.6 37.9 198.9 47 231 69.6c9 6.4 17.4 13.8 25 22.3c4.2-4.8 8.7-9.2 13.5-13.3c3.7-3.2 7.5-6.2 11.5-9c0 0 0 0 0 0C313.1 47 353.4 37.9 392.8 45.4C462 58.6 512 119.1 512 189.5v3.3c0 41.9-17.4 81.9-48.1 110.4L288.7 465.9l-2.5 2.3c-8.2 7.6-19 11.9-30.2 11.9s-22-4.2-30.2-11.9zM239.1 145c-.4-.3-.7-.7-1-1.1l-17.8-20c0 0-.1-.1-.1-.1c0 0 0 0 0 0c-23.1-25.9-58-37.7-92-31.2C81.6 101.5 48 142.1 48 189.5v3.3c0 28.5 11.9 55.8 32.8 75.2L256 430.7 431.2 268c20.9-19.4 32.8-46.7 32.8-75.2v-3.3c0-47.3-33.6-88-80.1-96.9c-34-6.5-69 5.4-92 31.2c0 0 0 0-.1 .1s0 0-.1 .1l-17.8 20c-.3 .4-.7 .7-1 1.1c-4.5 4.5-10.6 7-16.9 7s-12.4-2.5-16.9-7z\"/></svg>"');
			$db->setQuery($query);
			$db->execute();
		}
		
		$query->clear();
		$query->select('id')
			->from('#__eshop_configs')
			->where('config_Key = "compare_button_code"');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_configs')
				->values('0, "compare_button_code", "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" viewBox=\"0 0 512 512\"> <path d=\"M0 168v-16c0-13.3 10.7-24 24-24h360V80c0-21.4 25.9-32 41-17l80 80c9.4 9.4 9.4 24.6 0 33.9l-80 80C410 272 384 261.5 384 240v-48H24c-13.3 0-24-10.7-24-24zm488 152H128v-48c0-21.3-25.9-32.1-41-17l-80 80c-9.4 9.4-9.4 24.6 0 33.9l80 80C102.1 464 128 453.4 128 432v-48h360c13.3 0 24-10.7 24-24v-16c0-13.3-10.7-24-24-24z\"/></svg>"');
			$db->setQuery($query);
			$db->execute();
		}
		
		$query->clear();
		$query->select('id')
			->from('#__eshop_configs')
			->where('config_Key = "question_button_code"');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_configs')
				->values('0, "question_button_code", "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" viewBox=\"0 0 512 512\"><path d=\"M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm169.8-90.7c7.9-22.3 29.1-37.3 52.8-37.3h58.3c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24V250.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1H222.6c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z\"/></svg>"');
			$db->setQuery($query);
			$db->execute();
		}
		
		$query->clear();
		$query->select('id')
			->from('#__eshop_configs')
			->where('config_Key = "email_button_code"');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_configs')
				->values('0, "email_button_code", "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" viewBox=\"0 0 512 512\"><path d=\"M64 112c-8.8 0-16 7.2-16 16v22.1L220.5 291.7c20.7 17 50.4 17 71.1 0L464 150.1V128c0-8.8-7.2-16-16-16H64zM48 212.2V384c0 8.8 7.2 16 16 16H448c8.8 0 16-7.2 16-16V212.2L322 328.8c-38.4 31.5-93.7 31.5-132 0L48 212.2zM0 128C0 92.7 28.7 64 64 64H448c35.3 0 64 28.7 64 64V384c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V128z\"/></svg>"');
			$db->setQuery($query);
			$db->execute();
		}
		
		$query->clear();
		$query->select('id')
			->from('#__eshop_configs')
			->where('config_Key = "pdf_button_code"');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_configs')
				->values('0, "pdf_button_code", "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" viewBox=\"0 0 384 512\"><path d=\"M369.9 97.9L286 14C277 5 264.8-.1 252.1-.1H48C21.5 0 0 21.5 0 48v416c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V131.9c0-12.7-5.1-25-14.1-34zM332.1 128H256V51.9l76.1 76.1zM48 464V48h160v104c0 13.3 10.7 24 24 24h104v288H48zm250.2-143.7c-12.2-12-47-8.7-64.4-6.5-17.2-10.5-28.7-25-36.8-46.3 3.9-16.1 10.1-40.6 5.4-56-4.2-26.2-37.8-23.6-42.6-5.9-4.4 16.1-.4 38.5 7 67.1-10 23.9-24.9 56-35.4 74.4-20 10.3-47 26.2-51 46.2-3.3 15.8 26 55.2 76.1-31.2 22.4-7.4 46.8-16.5 68.4-20.1 18.9 10.2 41 17 55.8 17 25.5 0 28-28.2 17.5-38.7zm-198.1 77.8c5.1-13.7 24.5-29.5 30.4-35-19 30.3-30.4 35.7-30.4 35zm81.6-190.6c7.4 0 6.7 32.1 1.8 40.8-4.4-13.9-4.3-40.8-1.8-40.8zm-24.4 136.6c9.7-16.9 18-37 24.7-54.7 8.3 15.1 18.9 27.2 30.1 35.5-20.8 4.3-38.9 13.1-54.8 19.2zm131.6-5s-5 6-37.3-7.8c35.1-2.6 40.9 5.4 37.3 7.8z\"/></svg>"');
			$db->setQuery($query);
			$db->execute();
		}
		
		$query->clear();
		$query->select('id')
			->from('#__eshop_configs')
			->where('config_Key = "match_button_code"');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_configs')
				->values('0, "match_button_code", "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\"  viewBox=\"0 0 512 512\"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d=\"M497.9 225.9L286.1 14.1A48 48 0 0 0 252.1 0H48C21.5 0 0 21.5 0 48v204.1a48 48 0 0 0 14.1 33.9l211.9 211.9c18.7 18.7 49.1 18.7 67.9 0l204.1-204.1c18.7-18.7 18.7-49.1 0-67.9zM112 160c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48zm513.9 133.8L421.8 497.9c-18.7 18.7-49.1 18.7-67.9 0l-.4-.4L527.6 323.5c17-17 26.4-39.6 26.4-63.6s-9.4-46.6-26.4-63.6L331.4 0h48.7a48 48 0 0 1 33.9 14.1l211.9 211.9c18.7 18.7 18.7 49.1 0 67.9z\"/></svg>"');
			$db->setQuery($query);
			$db->execute();
		}
		// Update database
		// Update to #__eshop_orders table
		$sql = 'ALTER TABLE `#__eshop_orders` CHANGE `payment_method` `payment_method` VARCHAR(100) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'ALTER TABLE `#__eshop_orders` CHANGE `shipping_method` `shipping_method` VARCHAR(100) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		$fields = array_keys($db->getTableColumns('#__eshop_orders'));
		if (!in_array('invoice_number', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `invoice_number` INT(11) DEFAULT NULL AFTER `id`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('payment_method_title', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `payment_method_title` VARCHAR(100) DEFAULT NULL AFTER `payment_method`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('shipping_method_title', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `shipping_method_title` TEXT DEFAULT NULL AFTER `shipping_method`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (in_array('shipping_method_title', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` CHANGE `shipping_method_title` `shipping_method_title` TEXT DEFAULT NULL';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('shipping_tracking_number', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `shipping_tracking_number` VARCHAR(255) DEFAULT NULL AFTER `shipping_method_title`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('shipping_tracking_url', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `shipping_tracking_url` TEXT DEFAULT NULL AFTER `shipping_tracking_number`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('coupon_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `coupon_id` int(11) DEFAULT NULL AFTER `currency_exchanged_value`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('coupon_code', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `coupon_code` varchar(32) DEFAULT NULL AFTER `coupon_id`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('voucher_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `voucher_id` int(11) DEFAULT NULL AFTER `coupon_code`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('voucher_code', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `voucher_code` varchar(32) DEFAULT NULL AFTER `voucher_id`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('delivery_date', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `delivery_date` datetime DEFAULT NULL AFTER `coupon_code`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('params', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `params` TEXT DEFAULT NULL AFTER `delivery_date`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('order_number', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `order_number` VARCHAR(255) DEFAULT NULL AFTER `id`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('payment_email', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `payment_email` VARCHAR(96) DEFAULT NULL AFTER `payment_lastname`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('payment_telephone', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `payment_telephone` VARCHAR(32) DEFAULT NULL AFTER `payment_email`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('payment_fax', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `payment_fax` VARCHAR(32) DEFAULT NULL AFTER `payment_telephone`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('shipping_email', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `shipping_email` VARCHAR(96) DEFAULT NULL AFTER `shipping_lastname`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('shipping_telephone', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `shipping_telephone` VARCHAR(32) DEFAULT NULL AFTER `shipping_email`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('shipping_fax', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `shipping_fax` VARCHAR(32) DEFAULT NULL AFTER `shipping_telephone`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('payment_eu_vat_number', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `payment_eu_vat_number` TEXT DEFAULT NULL AFTER `payment_method_title`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('shipping_eu_vat_number', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `shipping_eu_vat_number` TEXT DEFAULT NULL AFTER `shipping_tracking_url`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('user_ip', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orders` ADD `user_ip` VARCHAR(100) DEFAULT NULL AFTER `params`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('newsletter_interest', $fields))
		{
			$sql = "ALTER TABLE `#__eshop_orders` ADD `newsletter_interest` TINYINT(1) NOT NULL DEFAULT  '1' AFTER `user_ip`";
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('privacy_policy_agree', $fields))
		{
			$sql = "ALTER TABLE `#__eshop_orders` ADD `privacy_policy_agree` TINYINT(1) NOT NULL DEFAULT  '1' AFTER `newsletter_interest`";
			$db->setQuery($sql);
			$db->execute();
		}

		//Product Details table
		$fields = array_keys($db->getTableColumns('#__eshop_productdetails'));
		if (!in_array('tab1_title', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `tab1_title` VARCHAR(255) DEFAULT NULL AFTER `product_page_heading`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('tab1_content', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `tab1_content` TEXT DEFAULT NULL AFTER `tab1_title`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('tab2_title', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `tab2_title` VARCHAR(255) DEFAULT NULL AFTER `tab1_content`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('tab2_content', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `tab2_content` TEXT DEFAULT NULL AFTER `tab2_title`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('tab3_title', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `tab3_title` VARCHAR(255) DEFAULT NULL AFTER `tab2_content`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('tab3_content', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `tab3_content` TEXT DEFAULT NULL AFTER `tab3_title`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('tab4_title', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `tab4_title` VARCHAR(255) DEFAULT NULL AFTER `tab3_content`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('tab4_content', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `tab4_content` TEXT DEFAULT NULL AFTER `tab4_title`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('tab5_title', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `tab5_title` VARCHAR(255) DEFAULT NULL AFTER `tab4_content`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('tab5_content', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `tab5_content` TEXT DEFAULT NULL AFTER `tab5_title`';
			$db->setQuery($sql);
			$db->execute();
		}
		// Update to #__eshop_payments table
		$query->clear();
		$query->select('MAX(ordering)')
			->from('#__eshop_payments');
		$db->setQuery($query);
		$ordering = $db->loadResult();
		$query->clear();
		$query->select('id')
			->from('#__eshop_payments')
			->where('name = "os_authnet"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$ordering++;
			$query->clear();
			$query->insert('#__eshop_payments')
				->values(
					'0, "os_authnet", "Authorize.net", "Giang Dinh Truong", "0000-00-00 00:00:00", "Copyright 2010-2013 Ossolution Team", "http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2", "contact@joomdonation.com", "www.joomdonation.com", "1.0", "Authorize.net Payment Plugin for EShop", NULL, ' . $ordering . ', 0'
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_payments')
			->where('name = "os_eway"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$ordering++;
			$query->clear();
			$query->insert('#__eshop_payments')
				->values(
					'0, "os_eway", "Eway", "Giang Dinh Truong", "0000-00-00 00:00:00", "Copyright 2010-2013 Ossolution Team", "http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2", "contact@joomdonation.com", "www.joomdonation.com", "1.0", "Eway Payment Plugin for EShop", NULL, ' . $ordering . ', 0'
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_payments')
			->where('name = "os_creditcard"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$ordering++;
			$query->clear();
			$query->insert('#__eshop_payments')
				->values(
					'0, "os_creditcard", "Offline Credit Card Processing", "Giang Dinh Truong", "0000-00-00 00:00:00", "Copyright 2010-2013 Ossolution Team", "http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2", "contact@joomdonation.com", "www.joomdonation.com", "1.0", "This payment plugin collects the Credit Card information from customers and send it to administrator for offline processing.", NULL, ' . $ordering . ', 0'
				);
			$db->setQuery($query);
			$db->execute();
		}

		//Update to #__eshop_coupons table
		$fields = array_keys($db->getTableColumns('#__eshop_coupons'));

		if (!in_array('coupon_per_customer', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_coupons` ADD `coupon_per_customer` int(11) DEFAULT NULL AFTER `coupon_used`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('coupon_for_free_shipping', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_coupons` ADD `coupon_for_free_shipping` tinyint(1) DEFAULT 0 AFTER `coupon_shipping`';
			$db->setQuery($sql);
			$db->execute();
		}

		// Update to #__eshop_options table
		$fields = array_keys($db->getTableColumns('#__eshop_options'));
		if (!in_array('option_image', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_options` ADD `option_image` VARCHAR(255) DEFAULT NULL AFTER `option_type`';
			$db->setQuery($sql);
			$db->execute();
		}

		// Update to #__eshop_optiondetails table
		$fields = array_keys($db->getTableColumns('#__eshop_optiondetails'));
		if (!in_array('option_desc', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_optiondetails` ADD `option_desc` TEXT DEFAULT NULL AFTER `option_name`';
			$db->setQuery($sql);
			$db->execute();
		}

		//Option value sku
		$fields = array_keys($db->getTableColumns('#__eshop_productoptionvalues'));
		if (!in_array('sku', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productoptionvalues` ADD `sku` VARCHAR(64) DEFAULT NULL AFTER `option_value_id`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('image', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productoptionvalues` ADD `image` VARCHAR(255) DEFAULT NULL AFTER `weight_sign`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('price_type', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productoptionvalues` ADD `price_type` CHAR(1) DEFAULT NULL AFTER `price_sign`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('shipping', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productoptionvalues` ADD `shipping` TINYINT(1) UNSIGNED DEFAULT NULL AFTER `weight_sign`';
			$db->setQuery($sql);
			$db->execute();

			$sql = 'UPDATE `#__eshop_productoptionvalues` SET `shipping` = 1';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('published', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productoptionvalues` ADD `published` TINYINT(1) NOT NULL DEFAULT "1" AFTER `shipping`';
			$db->setQuery($sql);
			$db->execute();

			$sql = 'UPDATE `#__eshop_productoptionvalues` SET `published` = 1';
			$db->setQuery($sql);
			$db->execute();
		}

		$fields = array_keys($db->getTableColumns('#__eshop_orderoptions'));

		if (!in_array('sku', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orderoptions` ADD `sku` VARCHAR(64) DEFAULT NULL AFTER `option_type`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orderoptions` ADD `product_id` INT(11) DEFAULT NULL AFTER `order_id`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('quantity', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orderoptions` ADD `quantity` INT(11) DEFAULT NULL AFTER `option_type`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('price', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orderoptions` ADD `price` DECIMAL(15,4) DEFAULT NULL AFTER `quantity`';
			$db->setQuery($sql);
			$db->execute();
		}

		$sql = 'ALTER TABLE `#__eshop_orderoptions` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT';
		$db->setQuery($sql);
		$db->execute();

		// Check and add more shippings methods
		$query->clear();
		$query->select('MAX(ordering)')
			->from('#__eshop_shippings');
		$db->setQuery($query);
		$ordering = $db->loadResult();

		$query->clear();
		$query->select('id')
			->from('#__eshop_shippings')
			->where('name = "eshop_ups"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$ordering++;
			$query->clear();
			$query->insert('#__eshop_shippings')
				->values(
					'0, "eshop_ups", "UPS", "Giang Dinh Truong", "0000-00-00 00:00:00", "Copyright 2013 Ossolution Team", "http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2", "contact@joomdonation.com", "www.joomdonation.com", "1.0.0", "This is UPS Shipping method for Eshop", NULL, ' . $ordering . ', 0'
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_shippings')
			->where('name = "eshop_quantity"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$ordering++;
			$query->clear();
			$query->insert('#__eshop_shippings')
				->values(
					'0, "eshop_quantity", "Quantity Shipping", "Giang Dinh Truong", "0000-00-00 00:00:00", "Copyright 2013 Ossolution Team", "http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2", "contact@joomdonation.com", "www.joomdonation.com", "1.0.0", "This is Quantity Shipping method for Eshop", "{\"package_fee\":\"0\",\"rates\":\"\",\"taxclass_id\":\"0\",\"geozone_id\":\"0\"}", ' . $ordering . ', 0'
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_shippings')
			->where('name = "eshop_auspostpac"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$ordering++;
			$query->clear();
			$query->insert('#__eshop_shippings')
				->values(
					'0, "eshop_auspostpac", "AusPost - Postage Assesment Calculator", "Giang Dinh Truong", "0000-00-00 00:00:00", "Copyright 2013 Ossolution Team", "http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2", "contact@joomdonation.com", "www.joomdonation.com", "1.0.0", "This is Australia Post Shipping method for Eshop", NULL, ' . $ordering . ', 0'
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_shippings')
			->where('name = "eshop_flatitem"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$ordering++;
			$query->clear();
			$query->insert('#__eshop_shippings')
				->values(
					'0, "eshop_flatitem", "Flat Item Shipping", "Giang Dinh Truong", "0000-00-00 00:00:00", "Copyright 2013 Ossolution Team", "http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2", "contact@joomdonation.com", "www.joomdonation.com", "1.0.0", "This is Flat Item Shipping method for Eshop", NULL, ' . $ordering . ', 0'
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_shippings')
			->where('name = "eshop_postcode"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$ordering++;
			$query->clear();
			$query->insert('#__eshop_shippings')
				->values(
					'0, "eshop_postcode", "Postcode Shipping", "Giang Dinh Truong", "0000-00-00 00:00:00", "Copyright 2013 Ossolution Team", "http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2", "contact@joomdonation.com", "www.joomdonation.com", "1.0.0", "Postcode shipping plugin allows you to set shipping cost based on Postcode range", NULL, ' . $ordering . ', 0'
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_shippings')
			->where('name = "eshop_itemgeozones"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$ordering++;
			$query->clear();
			$query->insert('#__eshop_shippings')
				->values(
					'0, "eshop_itemgeozones", "Item Geozones Shipping", "Giang Dinh Truong", "0000-00-00 00:00:00", "Copyright 2013 Ossolution Team", "http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2", "contact@joomdonation.com", "www.joomdonation.com", "1.0.0", "This is Item Geozones Shipping plugin for Eshop", NULL, ' . $ordering . ', 0'
				);
			$db->setQuery($query);
			$db->execute();
		}

		//Update menus
		$fields = array_keys($db->getTableColumns('#__eshop_menus'));
		if (!in_array('menu_class', $fields))
		{
			$sql = 'DROP TABLE IF EXISTS `#__eshop_menus`;';
			$db->setQuery($sql);
			$db->execute();

			$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_menus` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `menu_name` varchar(255) DEFAULT NULL,
			  `menu_parent_id` int(11) DEFAULT NULL,
			  `menu_view` varchar(255) DEFAULT NULL,
			  `menu_layout` varchar(255) DEFAULT NULL,
			  `menu_class` varchar(255) DEFAULT NULL,
			  `published` tinyint(1) unsigned DEFAULT NULL,
			  `ordering` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
			$db->setQuery($sql);
			$db->execute();

			$sql = "INSERT INTO `#__eshop_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_view`, `menu_layout`, `menu_class`, `published`, `ordering`) VALUES
			(1, 'ESHOP_DASHBOARD', 0, 'dashboard', NULL, 'home', 1, 1),
			(2, 'ESHOP_CATALOG', 0, NULL, NULL, 'list-view', 1, 2),
			(3, 'ESHOP_CATEGORIES', 2, 'categories', NULL, 'folder', 1, 1),
			(4, 'ESHOP_PRODUCTS', 2, 'products', NULL, 'cube', 1, 2),
			(5, 'ESHOP_OPTIONS', 2, 'options', NULL, 'checkbox', 1, 5),
			(6, 'ESHOP_MANUFACTURERS', 2, 'manufacturers', NULL, 'briefcase', 1, 6),
			(7, 'ESHOP_ORDERS', 8, 'orders', NULL, 'loop', 1, 1),
			(8, 'ESHOP_SALES', 0, NULL, NULL, 'cart', 1, 4),
			(9, 'ESHOP_ATTRIBUTEGROUPS', 2, 'attributegroups', NULL, 'file-add', 1, 4),
			(10, 'ESHOP_ATTRIBUTES', 2, 'attributes', NULL, 'file-add', 1, 3),
			(11, 'ESHOP_HELP', 0, 'help', NULL, 'support', 1, 7),
			(12, 'ESHOP_COUPONS', 8, 'coupons', NULL, 'minus', 1, 4),
			(13, 'ESHOP_TAXCLASSES', 15, 'taxclasses', NULL, 'plus-2', 1, 10),
			(14, 'ESHOP_TAXRATES', 15, 'taxrates', NULL, 'plus-2', 1, 11),
			(15, 'ESHOP_SYSTEM', 0, '', NULL, 'cog', 1, 5),
			(16, 'ESHOP_COUNTRIES', 15, 'countries', NULL, 'flag', 1, 2),
			(17, 'ESHOP_ZONES', 15, 'zones', NULL, 'location', 1, 8),
			(18, 'ESHOP_GEOZONES', 15, 'geozones', NULL, 'location', 1, 9),
			(19, 'ESHOP_CUSTOMERGROUPS', 8, 'customergroups', NULL, 'user', 1, 3),
			(20, 'ESHOP_CONFIGURATION', 15, 'configuration', NULL, 'move', 1, 1),
			(21, 'ESHOP_CUSTOMERS', 8, 'customers', NULL, 'user', 1, 2),
			(22, 'ESHOP_REPORTS', 0, 'reports', NULL, 'calendar-2', 1, 6),
			(23, 'ESHOP_PLUGINS', 0, NULL, NULL, 'wrench', 1, 3),
			(24, 'ESHOP_PAYMENTS', 23, 'payments', NULL, 'play', 1, 1),
			(25, 'ESHOP_SHIPPINGS', 23, 'shippings', NULL, 'share', 1, 2),
			(26, 'ESHOP_REVIEWS', 2, 'reviews', NULL, 'comments', 1, 7),
			(27, 'ESHOP_CURRENCIES', 15, 'currencies', NULL, 'shuffle', 1, 3),
			(28, 'ESHOP_STOCKSTATUSES', 15, 'stockstatuses', NULL, 'cube', 1, 4),
			(29, 'ESHOP_ORDERSTATUSES', 15, 'orderstatuses', NULL, 'loop', 1, 5),
			(30, 'ESHOP_LENGTHS', 15, 'lengths', NULL, 'checkbox-partial', 1, 6),
			(31, 'ESHOP_WEIGHTS', 15, 'weights', NULL, 'checkbox-partial', 1, 7),
			(32, 'ESHOP_ORDERS', 22, 'reports', 'orders', 'loop', 1, 1),
			(33, 'ESHOP_VIEWED_PRODUCTS', 22, 'reports', 'viewedproducts', 'eye', 1, 2),
			(34, 'ESHOP_PURCHASED_PRODUCTS', 22, 'reports', 'purchasedproducts', 'star', 1, 3),
			(35, 'ESHOP_THEMES', 23, 'themes', NULL, 'plus', 1, 3),
			(36, 'ESHOP_MESSAGES', 15, 'messages', 'messages', 'envelope', 1, 12),
			(37, 'ESHOP_TRANSLATION', 15, 'language', NULL, 'pencil', 1, 13),
			(38, 'ESHOP_EXPORTS', 15, 'exports', NULL, 'out', 1, 14),
			(39, 'ESHOP_VOUCHERS', 8, 'vouchers', NULL, 'heart', 1, 5),
			(40, 'ESHOP_LABELS', 2, 'labels', NULL, 'pencil', 1, 8),
			(41, 'ESHOP_DOWNLOADS', 2, 'downloads', NULL, 'download', 1, 9),
			(42, 'ESHOP_TOOLS', 15, 'tools', NULL, 'wrench', 1, 15),
			(43, 'ESHOP_FIELDS', 8, 'fields', NULL, 'checkbox-unchecked', 1, 6),
			(44, 'ESHOP_QUOTES', 8, 'quotes', NULL, 'question', 1, 7),
			(45, 'ESHOP_NOTIFY', 8, 'notify', NULL, 'info', 1, 8),
			(46, 'ESHOP_DISCOUNTS', 8, 'discounts', NULL, 'download', 1, 9);";
			$db->setQuery($sql);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_menus')
			->where('menu_name = "ESHOP_QUOTES"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_menus')
				->values('0, "ESHOP_QUOTES", "8", "quotes", "", "question", "1", "7"');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_menus')
			->where('menu_name = "ESHOP_NOTIFY"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_menus')
				->values('0, "ESHOP_NOTIFY", "8", "notify", "", "info", "1", "8"');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_menus')
			->where('menu_name = "ESHOP_DISCOUNTS"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_menus')
				->values('0, "ESHOP_DISCOUNTS", "8", "discounts", "", "download", "1", "9"');
			$db->setQuery($query);
			$db->execute();
		}
		
		$query->clear();
		$query->select('id')
			->from('#__eshop_menus')
			->where('menu_name = "ESHOP_QUESTIONS"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_menus')
				->values('0, "ESHOP_QUESTIONS", "8", "questions", "", "question", "1", "10"');
			$db->setQuery($query);
			$db->execute();
		}

		//Check and add messages
		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "admin_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Admin Notification Email", "admin_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">You have received an order.</p>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Order ID:</strong> [ORDER_ID]<br /> <strong>Date Added:</strong> [DATE_ADDED]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[PAYMENT_ADDRESS]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "customer_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Customer Notification Email", "customer_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;">To view your order click on the link below:</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;"><a href="[ORDER_LINK]"> [ORDER_LINK] </a></p>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Order ID:</strong> [ORDER_ID]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[PAYMENT_ADDRESS]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline_payment_customer_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Offline Payment Customer Notification Email", "offline_payment_customer_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;">To view your order click on the link below:</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;"><a href="[ORDER_LINK]"> [ORDER_LINK] </a></p>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Order ID:</strong> [ORDER_ID]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[PAYMENT_ADDRESS]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "guest_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Guest Notification Email", "guest_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Order ID:</strong> [ORDER_ID]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[PAYMENT_ADDRESS]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline_payment_guest_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Offline Payment Guest Notification Email", "offline_payment_guest_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Order ID:</strong> [ORDER_ID]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[PAYMENT_ADDRESS]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "manufacturer_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Manufacturer Notification Email", "manufacturer_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Hello [MANUFACTURER_NAME],<br /> You are receiving this email because following your product(s) are ordered at [STORE_NAME]:</p>\r\n[PRODUCTS_LIST]</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "order_status_change_customer"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Order Status Change - Customer Notification Email", "order_status_change_customer", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\n<p style="margin-top: 0px; margin-bottom: 20px;">Hello,</p>\n<p style="margin-top: 0px; margin-bottom: 20px;">Your order status is changed from [ORDER_STATUS_FROM] to [ORDER_STATUS_TO].</p>\n<p style="margin-top: 0px; margin-bottom: 20px;">To view your order click on the link below:</p>\n<p style="margin-top: 0px; margin-bottom: 20px;"><a href="[ORDER_LINK]"> [ORDER_LINK]</a></p>\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\n<thead>\n<tr>\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order Details</td>\n</tr>\n</thead>\n<tbody>\n<tr>\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Order ID:</strong> [ORDER_ID]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\n</tr>\n</tbody>\n</table>\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\n<thead>\n<tr>\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Comment</td>\n</tr>\n</thead>\n<tbody>\n<tr>\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[COMMENT]</td>\n</tr>\n</tbody>\n</table>\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\n<thead>\n<tr>\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Delivery Date</td>\n</tr>\n</thead>\n<tbody>\n<tr>\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[DELIVERY_DATE]</td>\n</tr>\n</tbody>\n</table>\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\n<thead>\n<tr>\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>\n</tr>\n</thead>\n<tbody>\n<tr>\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[PAYMENT_ADDRESS]</td>\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[SHIPPING_ADDRESS]</td>\n</tr>\n</tbody>\n</table>\n[PRODUCTS_LIST]\n<p style="margin-top: 0px; margin-bottom: 20px;">Please reply to this email if you have any questions.</p>\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "order_status_change_guest"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Order Status Change - Guest Notification Email", "order_status_change_guest", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Hello,</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Your order status is changed from [ORDER_STATUS_FROM] to [ORDER_STATUS_TO].</p>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Order ID:</strong> [ORDER_ID]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[PAYMENT_ADDRESS]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "customer_notification_email_with_download"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Customer Notification Email With Downloadable Products", "customer_notification_email_with_download", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;">To view your order click on the link below:</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;"><a href="[ORDER_LINK]"> [ORDER_LINK] </a></p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Once your payment has been confirmed you can click on the link below to access your downloadable products:</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;"><a href="[DOWNLOAD_LINK]">[DOWNLOAD_LINK]</a></p>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Order ID:</strong> [ORDER_ID]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[PAYMENT_ADDRESS]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline_payment_customer_notification_email_with_download"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values(
					'0, "Offline Payment Customer Notification Email With Downloadable Products", "offline_payment_customer_notification_email_with_download", "textarea"'
				);
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;">To view your order click on the link below:</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;"><a href="[ORDER_LINK]"> [ORDER_LINK] </a></p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Once your payment has been confirmed you can click on the link below to access your downloadable products:</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;"><a href="[DOWNLOAD_LINK]">[DOWNLOAD_LINK]</a></p>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Order ID:</strong> [ORDER_ID]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[PAYMENT_ADDRESS]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "admin_quote_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Admin Quote Email", "admin_quote_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">You have received a new quotation request from [NAME].</p>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Customer Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Name:</strong> [NAME]<br /><strong>Email:</strong> [EMAIL]<br /><strong>Company:</strong> [COMPANY]<br /><strong>Telephone:</strong> [TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Message</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[MESSAGE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "customer_quote_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Customer Quote Email", "customer_quote_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<p>Thank you for sending us the quotation for the following products:</p>\r\n<p>[PRODUCTS_LIST]</p>\r\n<p>We will try to?get back to you as soon as possible.</p>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "shipping_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Shipping Notification Email", "shipping_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<p>Dear <strong>[CUSTOMER_NAME]</strong>,</p>\n<p>We have shipped your order #[ORDER_ID]</p>\n<p>Track Your Package:?<a href="[SHIPPING_TRACKING_URL]">[SHIPPING_TRACKING_NUMBER]</a></p>\n<p>If the above link does not work (or is visible), you may copy and paste the following into your browser:?<a href="[SHIPPING_TRACKING_URL]">[SHIPPING_TRACKING_URL]</a></p>\n<p><strong>Shipping?Information</strong></p>\n<p>[SHIPPING_ADDRESS]</p>\n<p>Thank you!</p>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "guest_notification_email_with_download"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Guest Notification Email With Downloadable Products", "guest_notification_email_with_download", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Order Number:</strong> [ORDER_NUMBER]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[PAYMENT_ADDRESS]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline_payment_guest_notification_email_with_download"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values(
					'0, "Offline Payment Guest Notification Email With Downloadable Products", "offline_payment_guest_notification_email_with_download", "textarea"'
				);
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Order Number:</strong> [ORDER_NUMBER]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">\r\n<thead>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Payment Address</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[PAYMENT_ADDRESS]</td>\r\n<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style="margin-top: 0px; margin-bottom: 20px;">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "shop_introduction"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Shop Introduction", "shop_introduction", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'<p>Enter your shop introduction here</p>\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "notify_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Notify Email", "notify_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<p>Hello,</p>\r\n<p>Thank you for your patience.<br /><br />Our [PRODUCT_NAME] is now in stock and can be purchased by the following link:?<a href="[PRODUCT_LINK]">[PRODUCT_LINK]</a>.<br /><br />This is an one time notice, you will not receive this e-mail again.</p>\r\n<p>Thank you!</p>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "reminder_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Reminder Email", "reminder_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<p>Hello [STORE_NAME],</p>\r\n<p>The following products in your store are nearly out of stock:</p>\r\n<p>[PRODUCTS_LIST]</p>\r\n<p>Please go to your store to update their quantity before they are out of stock.</p>\r\n<p>Sincerely</p>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "admin_notification_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Admin Notification Email Subject", "admin_notification_email_subject", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'[STORE_NAME] - Order #[ORDER_ID]\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "admin_quote_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Admin Quote Email Subject", "admin_quote_email_subject", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'You have received an enquiry from [CUSTOMER_NAME]\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "customer_guest_notification_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Customer/Guest Notification Email Subject", "customer_guest_notification_email_subject", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'[STORE_NAME] - Order #[ORDER_ID]\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "customer_quote_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Customer Quote Email Subject", "customer_quote_email_subject", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'Your quote has been sent\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "manufacturer_notification_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Manufacturer Notification Email Subject", "manufacturer_notification_email_subject", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'Your product(s) are ordered\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "ask_question_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Ask Question Notification Email", "ask_question_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style="width: 680px;">\r\n<p style="margin-top: 0px; margin-bottom: 20px;">You have received a question about a product.</p>\r\n[PRODUCTS_LIST]</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "ask_question_notification_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Ask Question Notification Email Subject", "ask_question_notification_email_subject", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'Question about your product\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "notify_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Notify Email Subject", "notify_email_subject", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'[PRODUCT_NAME] has arrived!\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "reminder_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Reminder Email Subject", "reminder_email_subject", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'Products in your store are nearly out of stock!\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "order_status_change_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Order Status Change Subject", "order_status_change_subject", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'[STORE_NAME] - Your order status is changed\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "shipping_notification_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Shipping Notification Email Subject", "shipping_notification_email_subject", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'Shipping Notification\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "product_pdf_layout"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Product PDF Layout", "product_pdf_layout", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<table width="100%">\r\n<tbody>\r\n<tr>\r\n<td align="left">\r\n<h3>[STORE_NAME] - [STORE_ADDRESS]</h3>\r\n</td>\r\n<td align="right">\r\n<h3>Phone: [STORE_TELEPHONE] - Email: [STORE_EMAIL]</h3>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<hr />\r\n<p>[PRODUCT_DETAILS]</p>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "email_a_friend_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Email a Friend Subject", "email_a_friend_subject", "textbox"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'[STORE_NAME] - A friend sent you a link to [PRODUCT_NAME]\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "email_a_friend"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Email a Friend", "email_a_friend", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<p>Hi [INVITEE_NAME],</p>\r\n<p>A friend has sent you a link to a product that (s)he thinks may interest you.</p>\r\n<p>Click here to view this item: <a href="[PRODUCT_LINK]">[PRODUCT_LINK]</a></p>\r\n<p><strong>Sender Name</strong>: [SENDER_NAME]</p>\r\n<p><strong>Sender Email</strong>: [SENDER_EMAIL]</p>\r\n<p><strong>Message from Sender</strong>:</p>\r\n<p>-----</p>\r\n<p>[MESSAGE]</p>\r\n<p>-----</p>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "review_notification_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Review Notification Email Subject", "review_notification_email_subject", "textbox"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'New review for your product\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "review_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Review Notification Email", "review_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<p>A shopper just added a new review for your product. Please go to Reviews Manager at the back-end side to see it.</p>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline1_payment_customer_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Offline 1 Payment Customer Notification Email", "offline1_payment_customer_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style=\"width: 680px;\">\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">To view your order click on the link below:</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\"><a href=\"[ORDER_LINK]\"> [ORDER_LINK] </a></p>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\" colspan=\"2\">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Order Number:</strong> [ORDER_NUMBER]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Payment Address</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[PAYMENT_ADDRESS]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline2_payment_customer_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Offline 2 Payment Customer Notification Email", "offline2_payment_customer_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style=\"width: 680px;\">\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">To view your order click on the link below:</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\"><a href=\"[ORDER_LINK]\"> [ORDER_LINK] </a></p>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\" colspan=\"2\">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Order Number:</strong> [ORDER_NUMBER]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Payment Address</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[PAYMENT_ADDRESS]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline1_payment_guest_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Offline 1 Payment Guest Notification Email", "offline1_payment_guest_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style=\"width: 680px;\">\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\" colspan=\"2\">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Order Number:</strong> [ORDER_NUMBER]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Payment Address</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[PAYMENT_ADDRESS]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline2_payment_guest_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Offline 2 Payment Guest Notification Email", "offline2_payment_guest_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style=\"width: 680px;\">\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\" colspan=\"2\">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Order Number:</strong> [ORDER_NUMBER]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Payment Address</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[PAYMENT_ADDRESS]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline1_payment_customer_notification_email_with_download"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values(
					'0, "Offline 1 Payment Customer Notification Email With Downloadable Products", "offline1_payment_customer_notification_email_with_download", "textarea"'
				);
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style=\"width: 680px;\">\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">To view your order click on the link below:</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\"><a href=\"[ORDER_LINK]\"> [ORDER_LINK] </a></p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Once your payment has been confirmed you can click on the link below to access your downloadable products:</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\"><a href=\"[DOWNLOAD_LINK]\">[DOWNLOAD_LINK]</a></p>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\" colspan=\"2\">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Order Number:</strong> [ORDER_NUMBER]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Payment Address</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[PAYMENT_ADDRESS]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline2_payment_customer_notification_email_with_download"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values(
					'0, "Offline 2 Payment Customer Notification Email With Downloadable Products", "offline2_payment_customer_notification_email_with_download", "textarea"'
				);
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style=\"width: 680px;\">\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">To view your order click on the link below:</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\"><a href=\"[ORDER_LINK]\"> [ORDER_LINK] </a></p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Once your payment has been confirmed you can click on the link below to access your downloadable products:</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\"><a href=\"[DOWNLOAD_LINK]\">[DOWNLOAD_LINK]</a></p>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\" colspan=\"2\">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Order Number:</strong> [ORDER_NUMBER]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Payment Address</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[PAYMENT_ADDRESS]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline1_payment_guest_notification_email_with_download"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values(
					'0, "Offline 1 Payment Guest Notification Email With Downloadable Products", "offline1_payment_guest_notification_email_with_download", "textarea"'
				);
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style=\"width: 680px;\">\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\" colspan=\"2\">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Order Number:</strong> [ORDER_NUMBER]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Payment Address</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[PAYMENT_ADDRESS]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "offline2_payment_guest_notification_email_with_download"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values(
					'0, "Offline 2 Payment Guest Notification Email With Downloadable Products", "offline2_payment_guest_notification_email_with_download", "textarea"'
				);
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style=\"width: 680px;\">\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Thank you for your interest in [STORE_NAME] products. Your order has been received and will be processed once payment has been confirmed.</p>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\" colspan=\"2\">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Order Number:</strong> [ORDER_NUMBER]<br /> <strong>Date Added:</strong> [DATE_ADDED]<br /> <strong>Payment Method:</strong> [PAYMENT_METHOD]<br /> <strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Email:</strong> [CUSTOMER_EMAIL]<br /> <strong>Telephone:</strong> [CUSTOMER_TELEPHONE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Payment Address</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[PAYMENT_ADDRESS]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please send the offline payment to our bank account:<br /> Enter your bank information here</p>\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">Please reply to this email if you have any questions.</p>\r\n</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "admin_cancel_notification_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Admin Cancel Notification Email Subject", "admin_cancel_notification_email_subject", "textbox"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'The order #[ORDER_ID] was cancelled\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "admin_cancel_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Admin Cancel Notification Email", "admin_cancel_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style=\"width: 680px;\">\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">The following order was cancelled.</p>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\" colspan=\"2\">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Order Number:</strong> [ORDER_NUMBER]<br /><strong>Date Added:</strong> [DATE_ADDED]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Payment Method:</strong> [PAYMENT_METHOD]<br /><strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Payment Address</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[PAYMENT_ADDRESS]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}
		
		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "admin_failure_notification_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Admin Failure Notification Email Subject", "admin_failure_notification_email_subject", "textbox"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'The order #[ORDER_ID] failed\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "admin_failure_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Admin Failure Notification Email", "admin_failure_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<div style=\"width: 680px;\">\r\n<p style=\"margin-top: 0px; margin-bottom: 20px;\">The following order failed.</p>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\" colspan=\"2\">Order Details</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Order Number:</strong> [ORDER_NUMBER]<br /><strong>Date Added:</strong> [DATE_ADDED]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\"><strong>Payment Method:</strong> [PAYMENT_METHOD]<br /><strong>Shipping Method:</strong> [SHIPPING_METHOD]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Comment</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[COMMENT]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Delivery Date</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[DELIVERY_DATE]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;\">\r\n<thead>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Payment Address</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #efefef; font-weight: bold; text-align: left; padding: 7px; color: #222222;\">Shipping Address</td>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[PAYMENT_ADDRESS]</td>\r\n<td style=\"font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;\">[SHIPPING_ADDRESS]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n[PRODUCTS_LIST]</div>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "1st_abandon_cart_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "1st Abandon Cart Email Subject", "1st_abandon_cart_email_subject", "textbox"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'Did you forget something?\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "1st_abandon_cart_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "1st Abandon Cart Email", "1st_abandon_cart_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<p>We see that you left some product(s) in your cart. Did you have any questions or experience any problems? Feel free to reply to this email and we will get back to you as fast as possible. <a href=\"[CART_LINK]\">View Cart</a></p>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "2nd_abandon_cart_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "2nd Abandon Cart Email Subject", "2nd_abandon_cart_email_subject", "textbox"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'Still keeping your items for you\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "2nd_abandon_cart_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "2nd Abandon Cart Email", "2nd_abandon_cart_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<p>Still thinking it over? We are holding the items in your cart for you, but do not wait too long! Order today! <a href=\"[CART_LINK]\">View Cart</a></p>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "3rd_abandon_cart_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "3rd Abandon Cart Email Subject", "3rd_abandon_cart_email_subject", "textbox"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values('0, ' . $messageId . ', \'Last reminder and a free gift\', \'en-GB\'');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "3rd_abandon_cart_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "3rd Abandon Cart Email", "3rd_abandon_cart_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
					'0, ' . $messageId . ', \'<p>Before we empty your cart we wanted to give you one last reason to complete your purchase. Use code GIFT10 to get a 10% discount at checkout. <a href=\"[CART_LINK]\">View Cart</a></p>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}
		
		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "price_match_notification_email_subject"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Price Match Notification Email Subject", "price_match_notification_email_subject", "textbox"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
				'0, ' . $messageId . ', \'Product Price Match Request\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}
		
		$query->clear();
		$query->select('id')
			->from('#__eshop_messages')
			->where('message_name = "price_match_notification_email"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_messages')
				->values('0, "Price Match Notification Email", "price_match_notification_email", "textarea"');
			$db->setQuery($query);
			$db->execute();
			$messageId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_messagedetails')
				->values(
				'0, ' . $messageId . ', \'<p>You received a new price match request from your online store.</p>\n<p><strong>Name: </strong>[CUSTOMER_NAME]</p>\n<p><strong>Email:</strong> <a href=\"mailto:[CUSTOMER_EMAIL]\">[CUSTOMER_EMAIL]</a></p>\n<p><strong>Product SKU:</strong> [PRODUCT_SKU]</p>\n<p><strong>Match Price Url:</strong> <a href=\"[MATCH_PRICE_URL]\">[MATCH_PRICE_URL]</a></p>\n<p><strong>Our Price:</strong> [PRODUCT_PRICE]</p>\n<p><strong>Match Price:</strong> [MATCH_PRICE]</p>\', \'en-GB\''
				);
			$db->setQuery($query);
			$db->execute();
		}

		//Find and replace old address format by new address format
		$sql = 'UPDATE #__eshop_messagedetails SET message_value = REPLACE(message_value, "[PAYMENT_ADDRESS]<br /> [PAYMENT_EMAIL]<br /> [PAYMENT_TELEPHONE]", "[PAYMENT_ADDRESS]");';
		$db->setQuery($sql);
		$db->execute();

		//Add voucher tables
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_voucherhistory` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) DEFAULT NULL,
			  `voucher_id` int(11) DEFAULT NULL,
			  `user_id` int(11) DEFAULT NULL,
			  `amount` decimal(15,8) DEFAULT NULL,
			  `created_date` datetime DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_vouchers` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `voucher_code` varchar(32) DEFAULT NULL,
			  `voucher_amount` decimal(15,8) DEFAULT NULL,
			  `voucher_start_date` datetime DEFAULT NULL,
			  `voucher_end_date` datetime DEFAULT NULL,
			  `published` tinyint(1) DEFAULT NULL,
			  `created_date` datetime DEFAULT NULL,
			  `created_by` int(11) DEFAULT NULL,
			  `modified_date` datetime DEFAULT NULL,
			  `modified_by` int(11) DEFAULT NULL,
			  `checked_out` int(11) DEFAULT NULL,
			  `checked_out_time` datetime DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		//Add label tables
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_labeldetails` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `label_id` int(11) DEFAULT NULL,
			  `label_name` varchar(255) DEFAULT NULL,
			  `language` char(7) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_labelelements` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `label_id` int(11) DEFAULT NULL,
			  `element_id` int(11) DEFAULT NULL,
			  `element_type` varchar(32) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_labels` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `label_style` varchar(32) DEFAULT NULL,
			  `label_position` varchar(32) DEFAULT NULL,
			  `label_bold` tinyint(1) DEFAULT NULL,
			  `label_background_color` varchar(6) DEFAULT NULL,
			  `label_foreground_color` varchar(6) DEFAULT NULL,
			  `label_opacity` float(5,2) DEFAULT NULL,
			  `enable_image` tinyint(1) unsigned DEFAULT NULL,
			  `label_image` varchar(255) DEFAULT NULL,
			  `label_image_width` int(11) DEFAULT NULL,
			  `label_image_height` int(11) DEFAULT NULL,
			  `label_start_date` datetime DEFAULT NULL,
		      `label_out_of_stock_products` tinyint(1) unsigned DEFAULT NULL,
			  `label_end_date` datetime DEFAULT NULL,
			  `ordering` int(11) DEFAULT NULL,
			  `published` tinyint(1) unsigned DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		//Add download tables
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_downloaddetails` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `download_id` int(11) DEFAULT NULL,
			  `download_name` varchar(255) DEFAULT NULL,
			  `language` char(7) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_downloads` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `filename` varchar(255) DEFAULT NULL,
			  `total_downloads_allowed` int(11) DEFAULT NULL,
			  `created_date` datetime DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_orderdownloads` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) DEFAULT NULL,
			  `order_product_id` int(11) DEFAULT NULL,
			  `download_id` int(11) DEFAULT NULL,
			  `download_name` varchar(255) DEFAULT NULL,
			  `filename` varchar(255) DEFAULT NULL,
			  `download_code` varchar(255) DEFAULT NULL,
			  `remaining` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_productdownloads` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `product_id` int(11) DEFAULT NULL,
			  `download_id` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		//Add fields tables
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_fields` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(50) DEFAULT NULL,
			  `fieldtype` varchar(50) DEFAULT NULL,
			  `address_type` varchar(10) DEFAULT NULL,
			  `validation_rule` varchar(255) DEFAULT NULL,
			  `validation_rules_string` varchar(255) DEFAULT NULL,
			  `size` tinyint(3) unsigned DEFAULT NULL,
			  `max_length` tinyint(3) unsigned DEFAULT NULL,
			  `rows` tinyint(3) unsigned DEFAULT NULL,
			  `cols` tinyint(3) unsigned DEFAULT NULL,
			  `css_class` varchar(255) DEFAULT NULL,
			  `extra_attributes` varchar(255) DEFAULT NULL,
			  `access` tinyint(3) unsigned DEFAULT NULL,
			  `multiple` tinyint(3) unsigned DEFAULT NULL,
			  `required` tinyint(3) unsigned DEFAULT NULL,
			  `ordering` int(11) DEFAULT NULL,
			  `published` int(11) DEFAULT NULL,
			  `is_core` tinyint(4) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_fielddetails` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `field_id` int(11) DEFAULT NULL,
			  `title` varchar(255) DEFAULT NULL,
			  `description` text,
			  `place_holder` varchar(255) DEFAULT NULL,
			  `language` varchar(10) NOT NULL,
			  `default_values` text,
			  `values` text,
			  `validation_error_message` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$query->clear();
		$query->select('COUNT(*)')
			->from('#__eshop_fields');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$sql = 'TRUNCATE TABLE #__eshop_fields';
			$db->setQuery($sql);
			$db->execute();
			$sql = 'TRUNCATE TABLE #__eshop_fielddetails';
			$db->setQuery($sql);
			$db->execute();
			$sql = "INSERT INTO `#__eshop_fields` (`id`, `name`, `fieldtype`, `address_type`, `validation_rule`, `validation_rules_string`, `size`, `max_length`, `rows`, `cols`, `css_class`, `extra_attributes`, `access`, `multiple`, `required`, `ordering`, `published`, `is_core`) VALUES
				(1, 'firstname', 'Text', 'A', 'max_len,32|min_len,1', 'required|max_len,32|min_len,1', 0, 0, 0, 0, NULL, '', 1, NULL, 1, 1, 1, 1),
				(2, 'lastname', 'Text', 'A', 'max_len,32|min_len,1', 'required|max_len,32|min_len,1', 0, 0, 0, 0, NULL, '', 1, NULL, 1, 2, 1, 1),
				(3, 'email', 'Text', 'A', 'valid_email', 'required|valid_email', 0, 0, 0, 0, NULL, '', 1, NULL, 1, 3, 1, 1),
				(4, 'telephone', 'Text', 'A', 'max_len,32|min_len,1', 'required|max_len,32|min_len,3', 0, 0, 0, 0, NULL, '', 1, NULL, 1, 4, 1, 1),
				(5, 'fax', 'Text', 'A', '0', '', 0, 0, 0, 0, NULL, '', 1, NULL, 0, 5, 1, 1),
				(6, 'company', 'Text', 'A', '0', '', 0, 0, 0, 0, NULL, '', 1, NULL, 0, 6, 1, 1),
				(7, 'company_id', 'Text', 'A', '0', '', 0, 0, 0, 0, NULL, '', 1, NULL, 0, 7, 1, 1),
				(8, 'address_1', 'Text', 'A', '0', 'required', 0, 0, 0, 0, NULL, '', 1, NULL, 1, 8, 1, 1),
				(9, 'address_2', 'Text', 'A', '0', '', 0, 0, 0, 0, NULL, '', 1, NULL, 0, 9, 1, 1),
				(10, 'city', 'Text', 'A', '0', 'required|max_len,128|min_len,2', 0, 0, 0, 0, NULL, '', 1, NULL, 1, 10, 1, 1),
				(11, 'postcode', 'Text', 'A', 'max_len,32|min_len,1', 'required|max_len,10|min_len,2', 0, 0, 0, 0, NULL, '', 1, NULL, 1, 11, 1, 1),
				(12, 'country_id', 'Countries', 'A', '0', 'required', 0, 0, 0, 0, NULL, '', 1, NULL, 1, 12, 1, 1),
				(13, 'zone_id', 'Zone', 'A', '0', 'required', 0, 0, 0, 0, NULL, '', 1, NULL, 1, 13, 1, 1),
				(14, 'eu_vat_number', 'Text', 'B', '0', '', 0, 0, 0, 0, NULL, '', 1, NULL, 0, 14, 0, 0);";
			$db->setQuery($sql);
			$db->execute();
			$sql = "INSERT INTO `#__eshop_fielddetails` (`id`, `field_id`, `title`, `description`, `place_holder`, `language`, `default_values`, `values`, `validation_error_message`) VALUES
				(1, 1, 'ESHOP_FIRST_NAME', '', NULL, 'en-GB', '', '', NULL),
				(2, 2, 'ESHOP_LAST_NAME', '', NULL, 'en-GB', '', '', NULL),
				(3, 3, 'ESHOP_EMAIL', '', NULL, 'en-GB', '', '', NULL),
				(4, 4, 'ESHOP_TELEPHONE', '', NULL, 'en-GB', '', '', NULL),
				(5, 5, 'ESHOP_FAX', '', NULL, 'en-GB', '', '', NULL),
				(6, 6, 'ESHOP_COMPANY', '', NULL, 'en-GB', '', '', NULL),
				(7, 7, 'ESHOP_COMPANY_ID', '', NULL, 'en-GB', '', '', NULL),
				(8, 8, 'ESHOP_ADDRESS_1', '', NULL, 'en-GB', '', '', ''),
				(9, 9, 'ESHOP_ADDRESS_2', '', NULL, 'en-GB', '', '', NULL),
				(10, 10, 'ESHOP_CITY', '', NULL, 'en-GB', '', '', NULL),
				(11, 11, 'ESHOP_POST_CODE', '', NULL, 'en-GB', '', '', NULL),
				(12, 12, 'ESHOP_COUNTRY', '', NULL, 'en-GB', '', '', NULL),
				(13, 13, 'ESHOP_REGION_STATE', '', NULL, 'en-GB', '', '', NULL),
				(14, 14, 'ESHOP_EU_VAT_NUMBER', '', NULL, 'en-GB', '', '', NULL);";
			$db->setQuery($sql);
			$db->execute();
		}

		$query->clear();
		$query->select('id')
			->from('#__eshop_fields')
			->where('name = "eu_vat_number"');
		$db->setQuery($query);
		if (!$db->loadResult())
		{
			$query->clear();
			$query->insert('#__eshop_fields')
				->values('0, "eu_vat_number", "Text", "B", "0", "", "0", "0", "0", "0", NULL, "", "1", NULL, "0", "14", "0", "0"');
			$db->setQuery($query);
			$db->execute();
			$fieldId = $db->insertid();
			$query->clear();
			$query->insert('#__eshop_fielddetails')
				->values('0, "' . $fieldId . '", "ESHOP_EU_VAT_NUMBER", "", "", "en-GB", "", "", ""');
			$db->setQuery($query);
			$db->execute();
		}

		//Add quotes tables
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_quotetotals` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `quote_id` int(11) DEFAULT NULL,
			  `name` varchar(32) DEFAULT NULL,
			  `title` varchar(255) DEFAULT NULL,
			  `text` varchar(255) DEFAULT NULL,
			  `value` decimal(15,4) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `idx_quote_id` (`quote_id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();
		
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_quoteoptions` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `quote_id` int(11) DEFAULT NULL,
			  `quote_product_id` int(11) DEFAULT NULL,
			  `product_option_id` int(11) DEFAULT NULL,
			  `product_option_value_id` int(11) DEFAULT NULL,
			  `option_name` varchar(255) DEFAULT NULL,
			  `option_value` text,
			  `option_type` varchar(32) DEFAULT NULL,
			  `sku` varchar(64) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_quoteproducts` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `quote_id` int(11) DEFAULT NULL,
			  `product_id` int(11) DEFAULT NULL,
			  `product_name` varchar(255) DEFAULT NULL,
			  `product_sku` varchar(64) DEFAULT NULL,
			  `quantity` int(11) DEFAULT NULL,
  			  `price` decimal(15,4) DEFAULT NULL,
			  `total_price` decimal(15,4) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_quotes` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `customer_id` int(11) DEFAULT NULL,
			  `name` varchar(255) DEFAULT NULL,
			  `email` varchar(96) DEFAULT NULL,
			  `company` varchar(255) DEFAULT NULL,
			  `telephone` varchar(32) DEFAULT NULL,
			  `address` varchar(128) DEFAULT NULL,
			  `city` varchar(128) DEFAULT NULL,
			  `postcode` varchar(10) DEFAULT NULL,
			  `country_id` int(11) DEFAULT NULL,
			  `country_name` varchar(128) DEFAULT NULL,
			  `zone_id` int(11) DEFAULT NULL,
			  `zone_name` varchar(128) DEFAULT NULL,
			  `message` text,
			  `total` decimal(15,4) DEFAULT NULL,
  			  `currency_id` int(11) DEFAULT NULL,
			  `currency_code` varchar(10) DEFAULT NULL,
			  `currency_exchanged_value` float(15,8) DEFAULT NULL,
			  `created_date` datetime DEFAULT NULL,
			  `modified_date` datetime DEFAULT NULL,
			  `modified_by` int(11) DEFAULT NULL,
			  `checked_out` int(11) DEFAULT NULL,
			  `checked_out_time` datetime DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_wishlists` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `customer_id` int(11) DEFAULT NULL,
			  `product_id` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `customer_id` (`customer_id`),
			  KEY `product_id` (`product_id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();
		
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_questions` (
		  `id` int(11) NOT NULL,
		  `product_id` int(11) DEFAULT NULL,
		  `name` varchar(255) DEFAULT NULL,
		  `email` varchar(96) DEFAULT NULL,
		  `company` varchar(255) DEFAULT NULL,
		  `phone` varchar(32) DEFAULT NULL,
		  `message` text DEFAULT NULL,
		  `created_date` datetime DEFAULT NULL,
		  `created_by` int(11) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `idx_product_id` (`product_id`)
		) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		// Copy data to other languages

		// #__eshop_attributedetails
		// #__eshop_attributegroupdetails
		// #__eshop_categorydetails
		// #__eshop_customergroupdetails
		//// #__eshop_downloaddetails
		//// #__eshop_fielddetails
		//// #__eshop_labeldetails
		// #__eshop_lengthdetails
		// #__eshop_manufacturerdetails
		// #__eshop_messagedetails
		// #__eshop_optiondetails
		// #__eshop_optionvaluedetails
		// #__eshop_orderstatusdetails
		// #__eshop_productattributedetails
		// #__eshop_productdetails
		// #__eshop_stockstatusdetails
		// #__eshop_weightdetails

		$query->clear();
		$query->select('element')
			->from('#__extensions')
			->where('type = "language"')
			->where('client_id = 0');
		$db->setQuery($query);
		$langCodes = $db->loadColumn();
		if (count($langCodes))
		{
			foreach ($langCodes as $langCode)
			{
				$sql = 'INSERT INTO #__eshop_attributedetails (attribute_id, attribute_name, language)' .
					' SELECT attribute_id, attribute_name, "' . $langCode . '"' .
					' FROM #__eshop_attributedetails WHERE (language = "en-GB") AND attribute_id NOT IN (select attribute_id FROM #__eshop_attributedetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_attributegroupdetails (attributegroup_id, attributegroup_name, language)' .
					' SELECT attributegroup_id, attributegroup_name, "' . $langCode . '"' .
					' FROM #__eshop_attributegroupdetails WHERE (language = "en-GB") AND attributegroup_id NOT IN (select attributegroup_id FROM #__eshop_attributegroupdetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_categorydetails (category_id, category_name, category_alias, category_desc, meta_key, meta_desc, language)' .
					' SELECT category_id, category_name, category_alias, category_desc, meta_key, meta_desc, "' . $langCode . '"' .
					' FROM #__eshop_categorydetails WHERE (language = "en-GB") AND category_id NOT IN (select category_id FROM #__eshop_categorydetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_customergroupdetails (customergroup_id, customergroup_name, language)' .
					' SELECT customergroup_id, customergroup_name, "' . $langCode . '"' .
					' FROM #__eshop_customergroupdetails WHERE (language = "en-GB") AND customergroup_id NOT IN (select customergroup_id FROM #__eshop_customergroupdetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_downloaddetails (download_id, download_name, language)' .
					' SELECT download_id, download_name, "' . $langCode . '"' .
					' FROM #__eshop_downloaddetails WHERE (language = "en-GB") AND download_id NOT IN  (select download_id FROM #__eshop_downloaddetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_fielddetails (field_id, title, description, place_holder, language, default_values, `values`, validation_error_message)' .
					' SELECT field_id, title, description, place_holder, "' . $langCode . '", default_values, `values`, validation_error_message' .
					' FROM #__eshop_fielddetails WHERE (language = "en-GB") AND field_id NOT IN (select field_id FROM #__eshop_fielddetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_labeldetails (label_id, label_name, language)' .
					' SELECT label_id, label_name, "' . $langCode . '"' .
					' FROM #__eshop_labeldetails WHERE (language = "en-GB") AND label_id NOT IN  (select label_id FROM #__eshop_labeldetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_lengthdetails (length_id, length_name, length_unit, language)' .
					' SELECT length_id, length_name, length_unit, "' . $langCode . '"' .
					' FROM #__eshop_lengthdetails WHERE (language = "en-GB") AND length_id NOT IN (select length_id FROM #__eshop_lengthdetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_manufacturerdetails (manufacturer_id, manufacturer_name, manufacturer_alias, manufacturer_desc, language)' .
					' SELECT manufacturer_id, manufacturer_name, manufacturer_alias, manufacturer_desc, "' . $langCode . '"' .
					' FROM #__eshop_manufacturerdetails WHERE (language = "en-GB") AND manufacturer_id NOT IN (select manufacturer_id FROM #__eshop_manufacturerdetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_messagedetails (message_id, message_value, language)' .
					' SELECT message_id, message_value, "' . $langCode . '"' .
					' FROM #__eshop_messagedetails WHERE (language = "en-GB") AND message_id NOT IN (select message_id FROM #__eshop_messagedetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_optiondetails (option_id, option_name, option_desc, language)' .
					' SELECT option_id, option_name, option_desc, "' . $langCode . '"' .
					' FROM #__eshop_optiondetails WHERE (language = "en-GB") AND option_id NOT IN (select option_id FROM #__eshop_optiondetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_optionvaluedetails (optionvalue_id, option_id, value, language)' .
					' SELECT optionvalue_id, option_id, value, "' . $langCode . '"' .
					' FROM #__eshop_optionvaluedetails WHERE (language = "en-GB") AND optionvalue_id NOT IN (select optionvalue_id FROM #__eshop_optionvaluedetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_orderstatusdetails (orderstatus_id, orderstatus_name, language)' .
					' SELECT orderstatus_id, orderstatus_name, "' . $langCode . '"' .
					' FROM #__eshop_orderstatusdetails WHERE (language = "en-GB") AND orderstatus_id NOT IN (select orderstatus_id FROM #__eshop_orderstatusdetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_productattributedetails (productattribute_id, product_id, value, language)' .
					' SELECT productattribute_id, product_id, value, "' . $langCode . '"' .
					' FROM #__eshop_productattributedetails WHERE (language = "en-GB") AND productattribute_id NOT IN (select productattribute_id FROM #__eshop_productattributedetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_productdetails (product_id, product_name, product_alias, product_desc, product_short_desc, meta_key, meta_desc, language)' .
					' SELECT product_id, product_name, product_alias, product_desc, product_short_desc, meta_key, meta_desc, "' . $langCode . '"' .
					' FROM #__eshop_productdetails WHERE (language = "en-GB") AND product_id NOT IN (select product_id FROM #__eshop_productdetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_stockstatusdetails (stockstatus_id, stockstatus_name, language)' .
					' SELECT stockstatus_id, stockstatus_name, "' . $langCode . '"' .
					' FROM #__eshop_stockstatusdetails WHERE (language = "en-GB") AND stockstatus_id NOT IN (select stockstatus_id FROM #__eshop_stockstatusdetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();

				$sql = 'INSERT INTO #__eshop_weightdetails (weight_id, weight_name, weight_unit, language)' .
					' SELECT weight_id, weight_name, weight_unit, "' . $langCode . '"' .
					' FROM #__eshop_weightdetails WHERE (language = "en-GB") AND weight_id NOT IN (select weight_id FROM #__eshop_weightdetails WHERE language = "' . $langCode . '")';
				$db->setQuery($sql);
				$db->execute();
			}
		}

		$fields = array_keys($db->getTableColumns('#__eshop_orderdownloads'));
		if (!in_array('download_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_orderdownloads` ADD `download_id` INT(11) DEFAULT NULL AFTER `order_product_id`';
			$db->setQuery($sql);
			$db->execute();
		}

		$fields = array_keys($db->getTableColumns('#__eshop_products'));
		if (!in_array('product_call_for_price', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_call_for_price` TINYINT(1) DEFAULT NULL AFTER `product_price`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('product_minimum_quantity', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_minimum_quantity` INT(11) DEFAULT NULL AFTER `product_quantity`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_maximum_quantity', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_maximum_quantity` INT(11) DEFAULT NULL AFTER `product_minimum_quantity`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_customergroups', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_customergroups` TEXT DEFAULT NULL AFTER `product_featured`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_stock_status_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_stock_status_id` INT(11) DEFAULT NULL AFTER `product_customergroups`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_quote_mode', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_quote_mode` TINYINT(1) UNSIGNED DEFAULT NULL AFTER `product_stock_status_id`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_cart_mode', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_cart_mode` VARCHAR(20) DEFAULT NULL AFTER `product_stock_status_id`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (in_array('product_cart_mode', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` CHANGE `product_cart_mode` `product_cart_mode` VARCHAR(20) DEFAULT NULL';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_threshold', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_threshold` INT(11) DEFAULT NULL AFTER `product_quantity`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_threshold_notify', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_threshold_notify` TINYINT(1) UNSIGNED DEFAULT 0 AFTER `product_threshold`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_stock_checkout', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_stock_checkout` TINYINT(1) UNSIGNED DEFAULT NULL AFTER `product_threshold_notify`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('params', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `params` TEXT DEFAULT NULL AFTER `hits`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('custom_fields', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `custom_fields` TEXT DEFAULT NULL AFTER `product_quote_mode`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_languages', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_languages` TEXT DEFAULT NULL AFTER `product_quote_mode`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_shipping_cost_geozones', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_shipping_cost_geozones` TEXT DEFAULT NULL AFTER `product_shipping_cost`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_cost', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_cost` decimal(15,4) DEFAULT NULL AFTER `product_length_id`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_manage_stock', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_manage_stock` TINYINT(1) DEFAULT 1 AFTER `product_taxclass_id`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_stock_display', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_stock_display` TINYINT(1) DEFAULT 1 AFTER `product_manage_stock`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_stock_warning', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_stock_warning` TINYINT(1) DEFAULT 1 AFTER `product_stock_display`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_inventory_global', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_inventory_global` TINYINT(1) DEFAULT 1 AFTER `product_stock_warning`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_end_date', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_end_date` DATETIME DEFAULT NULL AFTER `product_available_date`';
			$db->setQuery($sql);
			$db->execute();

			$sql = 'UPDATE #__eshop_products SET product_end_date = "0000-00-00 00:00:00"';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_show_availability', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `product_show_availability` TINYINT(1) DEFAULT 1 AFTER `product_stock_display`';
			$db->setQuery($sql);
			$db->execute();
		}
		
		if (!in_array('main_category_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD `main_category_id` int(11) DEFAULT NULL AFTER `id`';
			$db->setQuery($sql);
			$db->execute();
			
			$sql = 'UPDATE #__eshop_products AS p SET p.main_category_id = (SELECT pc.category_id FROM #__eshop_productcategories AS pc WHERE p.id = pc.product_id AND pc.main_category = 1)';
			$db->setQuery($sql);
			$db->execute();
		}

		// Update to #__eshop_categorydetails table
		$fields = array_keys($db->getTableColumns('#__eshop_categorydetails'));
		if (!in_array('category_page_title', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_categorydetails` ADD `category_page_title` VARCHAR(255) DEFAULT NULL AFTER `category_desc`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('category_page_heading', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_categorydetails` ADD `category_page_heading` VARCHAR(255) DEFAULT NULL AFTER `category_page_title`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('category_alt_image', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_categorydetails` ADD `category_alt_image` VARCHAR(255) DEFAULT NULL AFTER `category_page_heading`';
			$db->setQuery($sql);
			$db->execute();
		}
		
		if (!in_array('category_canoncial_link', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_categorydetails` ADD `category_canoncial_link` TEXT DEFAULT NULL AFTER `category_alt_image`';
			$db->setQuery($sql);
			$db->execute();
		}

		// Update to #__eshop_productdetails table
		$fields = array_keys($db->getTableColumns('#__eshop_productdetails'));
		if (!in_array('product_page_title', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `product_page_title` VARCHAR(255) DEFAULT NULL AFTER `product_short_desc`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_page_heading', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `product_page_heading` VARCHAR(255) DEFAULT NULL AFTER `product_page_title`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('product_alt_image', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `product_alt_image` VARCHAR(255) DEFAULT NULL AFTER `product_page_heading`';
			$db->setQuery($sql);
			$db->execute();
		}
		
		if (!in_array('product_canoncial_link', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `product_canoncial_link` TEXT DEFAULT NULL AFTER `product_alt_image`';
			$db->setQuery($sql);
			$db->execute();
		}
		
		if (!in_array('product_price_text', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `product_price_text` TEXT DEFAULT NULL AFTER `product_canoncial_link`';
			$db->setQuery($sql);
			$db->execute();
		}
		
		if (!in_array('product_custom_message', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD `product_custom_message` TEXT DEFAULT NULL AFTER `product_price_text`';
			$db->setQuery($sql);
			$db->execute();
		}

		// Update to #__eshop_manufacturerdetails table
		$fields = array_keys($db->getTableColumns('#__eshop_manufacturerdetails'));
		if (!in_array('manufacturer_page_title', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_manufacturerdetails` ADD `manufacturer_page_title` VARCHAR(255) DEFAULT NULL AFTER `manufacturer_desc`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('manufacturer_page_heading', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_manufacturerdetails` ADD `manufacturer_page_heading` VARCHAR(255) DEFAULT NULL AFTER `manufacturer_page_title`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('manufacturer_alt_image', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_manufacturerdetails` ADD `manufacturer_alt_image` VARCHAR(255) DEFAULT NULL AFTER `manufacturer_page_heading`';
			$db->setQuery($sql);
			$db->execute();
		}

		// Add index to improve the speed
		// #__eshop_categories
		$sql = 'SHOW INDEX FROM #__eshop_categories';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('category_parent_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_categories` ADD INDEX ( `category_parent_id` )';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('published', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_categories` ADD INDEX ( `published` )';
			$db->setQuery($sql);
			$db->execute();
		}

		// #__eshop_products
		$sql = 'SHOW INDEX FROM #__eshop_products';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('manufacturer_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD INDEX ( `manufacturer_id` )';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('published', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_products` ADD INDEX ( `published` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_productcategories table
		$sql = 'SHOW INDEX FROM #__eshop_productcategories';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('product_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productcategories` ADD INDEX ( `product_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('category_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productcategories` ADD INDEX ( `category_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_productdetails table
		$sql = 'SHOW INDEX FROM #__eshop_productdetails';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('product_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productdetails` ADD INDEX ( `product_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_categorydetails table
		$sql = 'SHOW INDEX FROM #__eshop_categorydetails';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('category_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_categorydetails` ADD INDEX ( `category_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_manufacturerdetails table
		$sql = 'SHOW INDEX FROM #__eshop_manufacturerdetails';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('manufacturer_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_manufacturerdetails` ADD INDEX ( `manufacturer_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_attributes table
		$sql = 'SHOW INDEX FROM #__eshop_attributes';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('attributegroup_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_attributes` ADD INDEX ( `attributegroup_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_attributedetails table
		$sql = 'SHOW INDEX FROM #__eshop_attributedetails';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('attribute_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_attributedetails` ADD INDEX ( `attribute_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_attributegroupdetails table
		$sql = 'SHOW INDEX FROM #__eshop_attributegroupdetails';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('attributegroup_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_attributegroupdetails` ADD INDEX ( `attributegroup_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_optiondetails table
		$sql = 'SHOW INDEX FROM #__eshop_optiondetails';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('option_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_optiondetails` ADD INDEX ( `option_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_optionvalues table
		$sql = 'SHOW INDEX FROM #__eshop_optionvalues';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('option_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_optionvalues` ADD INDEX ( `option_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_optionvaluedetails table
		$sql = 'SHOW INDEX FROM #__eshop_optionvaluedetails';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('optionvalue_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_optionvaluedetails` ADD INDEX ( `optionvalue_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_productattributes table
		$sql = 'SHOW INDEX FROM #__eshop_productattributes';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('product_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productattributes` ADD INDEX ( `product_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('attribute_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productattributes` ADD INDEX ( `attribute_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_productattributedetails table
		$sql = 'SHOW INDEX FROM #__eshop_productattributedetails';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('productattribute_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productattributedetails` ADD INDEX ( `productattribute_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('product_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productattributedetails` ADD INDEX ( `product_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_productoptions table
		$sql = 'SHOW INDEX FROM #__eshop_productoptions';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('product_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productoptions` ADD INDEX ( `product_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('option_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productoptions` ADD INDEX ( `option_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		// #__eshop_productoptionvalues table
		$sql = 'SHOW INDEX FROM #__eshop_productoptionvalues';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}
		if (!in_array('product_option_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productoptionvalues` ADD INDEX ( `product_option_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('product_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productoptionvalues` ADD INDEX ( `product_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('option_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productoptionvalues` ADD INDEX ( `option_id` )';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('option_value_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_productoptionvalues` ADD INDEX ( `option_value_id` )';
			$db->setQuery($sql);
			$db->execute();
		}

		//Add coupon customer groups table
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_couponcustomergroups` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `coupon_id` int(11) DEFAULT NULL,
			  `customergroup_id` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		//Add coupon categories table
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_couponcategories` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `coupon_id` int(11) DEFAULT NULL,
			  `category_id` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		// #__eshop_categories table
		$fields = array_keys($db->getTableColumns('#__eshop_categories'));
		if (!in_array('category_customergroups', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_categories` ADD `category_customergroups` TEXT DEFAULT NULL AFTER `category_image`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('category_cart_mode_customergroups', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_categories` ADD `category_cart_mode_customergroups` TEXT DEFAULT NULL AFTER `category_customergroups`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('level', $fields))
		{
			$sql = "ALTER TABLE  `#__eshop_categories` ADD  `level` TINYINT( 4 ) NOT NULL DEFAULT '1';";
			$db->setQuery($sql);
			$db->execute();

			// Update level for categories
			$query = $db->getQuery(true);
			$query->select('id, `category_parent_id`');
			$query->from('#__eshop_categories');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			// first pass - collect children
			if (count($rows))
			{
				$children = [];

				foreach ($rows as $v)
				{
					$pt   = $v->category_parent_id;
					$list = @$children[$pt] ? $children[$pt] : [];
					array_push($list, $v);
					$children[$pt] = $list;
				}

				$list = self::calculateCategoriesLevel(0, [], $children, 4);

				foreach ($list as $id => $category)
				{
					$sql = "UPDATE #__eshop_categories SET `level` = $category->level WHERE id = $id";
					$db->setQuery($sql);
					$db->execute();
				}
			}
		}

		if (!in_array('category_layout', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_categories` ADD `category_layout` varchar(32) NOT NULL DEFAULT \'default\' AFTER `category_image`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('params', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_categories` ADD `params` TEXT DEFAULT NULL AFTER `hits`';
			$db->setQuery($sql);
			$db->execute();
		}

		// #__eshop_manufacturers table
		$fields = array_keys($db->getTableColumns('#__eshop_manufacturers'));
		if (!in_array('manufacturer_customergroups', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_manufacturers` ADD `manufacturer_customergroups` TEXT DEFAULT NULL AFTER `manufacturer_image`';
			$db->setQuery($sql);
			$db->execute();
		}

		// #__eshop_quotes table
		$fields = array_keys($db->getTableColumns('#__eshop_quotes'));

		if (!in_array('address', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quotes` ADD `address` VARCHAR(128) DEFAULT NULL AFTER `telephone`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('city', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quotes` ADD `city` VARCHAR(128) DEFAULT NULL AFTER `address`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('postcode', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quotes` ADD `postcode` VARCHAR(10) DEFAULT NULL AFTER `city`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('country_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quotes` ADD `country_id` INT(11) DEFAULT NULL AFTER `postcode`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('country_name', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quotes` ADD `country_name` VARCHAR(128) DEFAULT NULL AFTER `country_id`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('zone_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quotes` ADD `zone_id` INT(11) DEFAULT NULL AFTER `country_name`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('zone_name', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quotes` ADD `zone_name` VARCHAR(128) DEFAULT NULL AFTER `zone_id`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('total', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quotes` ADD `total` decimal(15,4) DEFAULT NULL AFTER `message`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('currency_id', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quotes` ADD `currency_id` int(11) DEFAULT NULL AFTER `total`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('currency_code', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quotes` ADD `currency_code` varchar(10) DEFAULT NULL AFTER `currency_id`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('currency_exchanged_value', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quotes` ADD `currency_exchanged_value` float(15,8) DEFAULT NULL AFTER `currency_code`';
			$db->setQuery($sql);
			$db->execute();
		}


		// #__eshop_quoteproducts table
		$fields = array_keys($db->getTableColumns('#__eshop_quoteproducts'));
		if (!in_array('price', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quoteproducts` ADD `price` decimal(15,4) DEFAULT NULL AFTER `quantity`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('total_price', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_quoteproducts` ADD `total_price` decimal(15,4) DEFAULT NULL AFTER `price`';
			$db->setQuery($sql);
			$db->execute();
		}

		// #__eshop_addresses table
		$fields = array_keys($db->getTableColumns('#__eshop_addresses'));
		if (!in_array('email', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_addresses` ADD `email` varchar(96) DEFAULT NULL AFTER `lastname`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('telephone', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_addresses` ADD `telephone` varchar(32) DEFAULT NULL AFTER `email`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('fax', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_addresses` ADD `fax` varchar(32) DEFAULT NULL AFTER `telephone`';
			$db->setQuery($sql);
			$db->execute();
		}
		if (!in_array('eu_vat_number', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_addresses` ADD `eu_vat_number` TEXT DEFAULT NULL AFTER `zone_id`';
			$db->setQuery($sql);
			$db->execute();
		}

		// Product Tags table
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_producttags` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `product_id` int(11) DEFAULT NULL,
		  `tag_id` int(11) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		// Product Attachments table
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_productattachments` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `product_id` int(11) DEFAULT NULL,
			  `file_name` text,
			  `published` tinyint(1) unsigned DEFAULT NULL,
			  `ordering` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_tags` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `tag_name` varchar(100) DEFAULT NULL,
		  `hits` int(11) DEFAULT NULL,
		  `published` tinyint(1) unsigned DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_notify` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `product_id` int(11) NOT NULL,
		  `notify_email` varchar(255) DEFAULT NULL,
		  `sent_email` tinyint(1) NOT NULL,
		  `sent_date` datetime DEFAULT NULL,
		  `language` char(7) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$fields = array_keys($db->getTableColumns('#__eshop_notify'));
		if (!in_array('language', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_notify` ADD `language` char(7) DEFAULT NULL AFTER `sent_date`';
			$db->setQuery($sql);
			$db->execute();
		}

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_discountelements` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `discount_id` int(11) DEFAULT NULL,
		  `element_id` int(11) DEFAULT NULL,
		  `element_type` varchar(32) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_discounts` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `discount_type` char(1) DEFAULT NULL,
		  `discount_value` decimal(15,8) DEFAULT NULL,
		  `discount_customergroups` text,
		  `discount_start_date` datetime DEFAULT NULL,
		  `discount_end_date` datetime DEFAULT NULL,
		  `published` tinyint(1) unsigned DEFAULT NULL,
		  `created_date` datetime DEFAULT NULL,
		  `created_by` int(11) DEFAULT NULL,
		  `modified_date` datetime DEFAULT NULL,
		  `modified_by` int(11) DEFAULT NULL,
		  `checked_out` int(11) DEFAULT NULL,
		  `checked_out_time` datetime DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		//Add Geozone Postcodes table
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_geozonepostcodes` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `geozone_id` int(11) DEFAULT NULL,
		  `start_postcode` varchar(128) DEFAULT NULL,
		  `end_postcode` varchar(128) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		// Add carts table
		$sql = 'CREATE TABLE IF NOT EXISTS `#__eshop_carts` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `customer_id` int(11) DEFAULT NULL,
		  `cart_data` varbinary(50000) DEFAULT NULL,
		  `is_1st_sent` tinyint(1) NOT NULL DEFAULT 0,
		  `is_2nd_sent` tinyint(1) NOT NULL DEFAULT 0,
		  `is_3rd_sent` tinyint(1) DEFAULT 0,
		  `created_date` datetime DEFAULT NULL,
		  `modified_date` datetime DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8;';
		$db->setQuery($sql);
		$db->execute();

		$fields = array_keys($db->getTableColumns('#__eshop_carts'));

		if (!in_array('is_1st_sent', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_carts` ADD `is_1st_sent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `cart_data`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('is_2nd_sent', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_carts` ADD `is_2nd_sent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_1st_sent`';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('is_3rd_sent', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_carts` ADD `is_3rd_sent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_2nd_sent`';
			$db->setQuery($sql);
			$db->execute();
		}

		$fields = array_keys($db->getTableColumns('#__eshop_productcategories'));
		if (!in_array('main_category', $fields))
		{
			$sql = "ALTER TABLE `#__eshop_productcategories` ADD  `main_category` TINYINT(1) NOT NULL DEFAULT  '0';";
			$db->setQuery($sql);
			$db->execute();
			$sql = 'SELECT * FROM #__eshop_productcategories ORDER BY id DESC';
			$db->setQuery($sql);
			$rowProductCategories = $db->loadObjectList('product_id');
			if (count($rowProductCategories))
			{
				foreach ($rowProductCategories as $rowProductCategory)
				{
					$sql = 'UPDATE #__eshop_productcategories SET main_category=1 WHERE id=' . $rowProductCategory->id;
					$db->setQuery($sql);
					$db->execute();
				}
			}
		}

		$fields = array_keys($db->getTableColumns('#__eshop_labels'));
		if (!in_array('label_out_of_stock_products', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_labels` ADD `label_out_of_stock_products` TINYINT(1) DEFAULT 0 AFTER `label_end_date`';
			$db->setQuery($sql);
			$db->execute();
		}

		$fields = array_keys($db->getTableColumns('#__eshop_reviews'));
		if (!in_array('email', $fields))
		{
			$sql = 'ALTER TABLE `#__eshop_reviews` ADD `email` VARCHAR(96) DEFAULT NULL AFTER `author`';
			$db->setQuery($sql);
			$db->execute();
		}

		//Change the data type of creation_date field of #__eshop_payments table.
		$sql = 'ALTER TABLE `#__eshop_payments` CHANGE `creation_date` `creation_date` VARCHAR(50) NULL DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		//Change the data type of creation_date field of #__eshop_shippings table.
		$sql = 'ALTER TABLE `#__eshop_shippings` CHANGE `creation_date` `creation_date` VARCHAR(50) NULL DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		//Change the data type of creation_date field of #__eshop_themes table.
		$sql = 'ALTER TABLE `#__eshop_themes` CHANGE `creation_date` `creation_date` VARCHAR(50) NULL DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_couponhistory table
		$sql = 'ALTER TABLE `#__eshop_couponhistory` CHANGE `amount` `amount` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_coupons table
		$sql = 'ALTER TABLE `#__eshop_coupons` CHANGE `coupon_value` `coupon_value` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_coupons table
		$sql = 'ALTER TABLE `#__eshop_coupons` CHANGE `coupon_min_total` `coupon_min_total` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_coupons table
		$sql = 'ALTER TABLE `#__eshop_coupons` CHANGE `coupon_used` `coupon_used` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_discounts table
		$sql = 'ALTER TABLE `#__eshop_discounts` CHANGE `discount_value` `discount_value` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_lengths table
		$sql = 'ALTER TABLE `#__eshop_lengths` CHANGE `exchanged_value` `exchanged_value` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_orderoptions table
		$sql = 'ALTER TABLE `#__eshop_orderoptions` CHANGE `price` `price` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_orderproducts table
		$sql = 'ALTER TABLE `#__eshop_orderproducts` CHANGE `price` `price` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_orderproducts table
		$sql = 'ALTER TABLE `#__eshop_orderproducts` CHANGE `total_price` `total_price` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_orderproducts table
		$sql = 'ALTER TABLE `#__eshop_orderproducts` CHANGE `tax` `tax` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_orders table
		$sql = 'ALTER TABLE `#__eshop_orders` CHANGE `total` `total` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_ordertotals table
		$sql = 'ALTER TABLE `#__eshop_ordertotals` CHANGE `value` `value` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_productdiscounts table
		$sql = 'ALTER TABLE `#__eshop_productdiscounts` CHANGE `price` `price` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_productoptionvalues table
		$sql = 'ALTER TABLE `#__eshop_productoptionvalues` CHANGE `price` `price` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_productoptionvalues table
		$sql = 'ALTER TABLE `#__eshop_productoptionvalues` CHANGE `weight` `weight` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_products table
		$sql = 'ALTER TABLE `#__eshop_products` CHANGE `product_weight` `product_weight` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_products table
		$sql = 'ALTER TABLE `#__eshop_products` CHANGE `product_length` `product_length` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_products table
		$sql = 'ALTER TABLE `#__eshop_products` CHANGE `product_width` `product_width` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_products table
		$sql = 'ALTER TABLE `#__eshop_products` CHANGE `product_height` `product_height` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_products table
		$sql = 'ALTER TABLE `#__eshop_products` CHANGE `product_cost` `product_cost` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_products table
		$sql = 'ALTER TABLE `#__eshop_products` CHANGE `product_price` `product_price` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_products table
		$sql = 'ALTER TABLE `#__eshop_products` CHANGE `product_shipping_cost` `product_shipping_cost` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_productspecials table
		$sql = 'ALTER TABLE `#__eshop_productspecials` CHANGE `price` `price` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_quoteproducts table
		$sql = 'ALTER TABLE `#__eshop_quoteproducts` CHANGE `price` `price` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_quoteproducts table
		$sql = 'ALTER TABLE `#__eshop_quoteproducts` CHANGE `total_price` `total_price` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_quotes table
		$sql = 'ALTER TABLE `#__eshop_quotes` CHANGE `total` `total` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_taxes table
		$sql = 'ALTER TABLE `#__eshop_taxes` CHANGE `tax_rate` `tax_rate` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_weights table
		$sql = 'ALTER TABLE `#__eshop_weights` CHANGE `exchanged_value` `exchanged_value` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_voucherhistory table
		$sql = 'ALTER TABLE `#__eshop_voucherhistory` CHANGE `amount` `amount` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_vouchers table
		$sql = 'ALTER TABLE `#__eshop_vouchers` CHANGE `voucher_amount` `voucher_amount` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_currencies table
		$sql = 'ALTER TABLE `#__eshop_currencies` CHANGE `exchanged_value` `exchanged_value` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_labels table
		$sql = 'ALTER TABLE `#__eshop_labels` CHANGE `label_opacity` `label_opacity` DECIMAL(5,2) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_orders table
		$sql = 'ALTER TABLE `#__eshop_orders` CHANGE `currency_exchanged_value` `currency_exchanged_value` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		// Update to #__eshop_quotes table
		$sql = 'ALTER TABLE `#__eshop_quotes` CHANGE `currency_exchanged_value` `currency_exchanged_value` DECIMAL(15,4) DEFAULT NULL';
		$db->setQuery($sql);
		$db->execute();

		if ($update)
		{
			$this->addMissingIndexes();
		}
	}

	/**
	 *
	 * Function to display welcome page after installing
	 */
	public function displayEshopWelcome($update)
	{
		//Add style css
		Factory::getApplication()->getDocument()->addStyleSheet(Uri::base() . '/components/com_eshop/assets/css/style.css');
		//Load Eshop language file
		$lang = Factory::getLanguage();
		$lang->load('com_eshop', JPATH_ADMINISTRATOR, 'en_GB', true);
		?>
		<table cellspacing="0" cellpadding="0" width="100%">
			<tbody>
			<td valign="top">
				<?php
				echo HTMLHelper::_('image', 'media/com_eshop/logo_eshop.png', ''); ?><br/>
				<h2 class="eshop-welcome-title"><?php
					echo Text::_('ESHOP_WELCOME_TITLE'); ?></h2><br/>
				<p class="eshop-welcome-text"><?php
					echo Text::_('ESHOP_WELCOME_TEXT'); ?></p>
			</td>
			<td valign="top">
				<h2><?php
					echo $update ? Text::_('ESHOP_UPDATE_SUCCESSFULLY') : Text::_('ESHOP_INSTALLATION_SUCCESSFULLY'); ?></h2>
				<div id="cpanel">
					<?php
					if (!$update)
					{
						?>
						<div style="float:<?php
						echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
							<div class="icon">
								<a title="<?php
								echo Text::_('ESHOP_INSTALL_SAMPLE_DATA'); ?>" href="<?php
								echo Route::_('index.php?option=com_eshop&task=installSampleData'); ?>">
									<?php
									echo HTMLHelper::_(
										'image',
										'administrator/components/com_eshop/assets/icons/icon-48-install.png',
										Text::_('ESHOP_INSTALL_SAMPLE_DATA')
									); ?>
									<span><?php
										echo Text::_('ESHOP_INSTALL_SAMPLE_DATA'); ?></span>
								</a>
							</div>
						</div>
						<?php
					}
					?>
					<div style="float:<?php
					echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
						<div class="icon">
							<a title="<?php
							echo Text::_('ESHOP_GO_TO_HOME'); ?>" href="<?php
							echo Route::_('index.php?option=com_eshop&view=dashboard'); ?>">
								<?php
								echo HTMLHelper::_(
									'image',
									'administrator/components/com_eshop/assets/icons/icon-48-home.png',
									Text::_('ESHOP_GO_TO_HOME')
								); ?>
								<span><?php
									echo Text::_('ESHOP_GO_TO_HOME'); ?></span>
							</a>
						</div>
					</div>
				</div>
			</td>
			</tbody>
		</table>
		<?php
	}

	/**
	 *
	 * Function to run after installing the component
	 */
	public function postflight($type, $parent)
	{
		//Restore the modified language strings by merging to language files
		foreach (self::$languageFiles as $languageFile)
		{
			$registry = new Registry;

			if (strpos($languageFile, 'admin') !== false)
			{
				$languageFolder = JPATH_ADMINISTRATOR . '/language/en-GB/';
				$languageFile   = substr($languageFile, 6);
			}
			else
			{
				$languageFolder = JPATH_ROOT . '/language/en-GB/';
			}

			$backupFile  = $languageFolder . 'bak.' . $languageFile;
			$currentFile = $languageFolder . $languageFile;

			if (is_file($currentFile) && is_file($backupFile))
			{
				$registry->loadFile($currentFile, 'INI');
				$currentItems = $registry->toArray();
				$registry->loadFile($backupFile, 'INI');
				$backupItems = $registry->toArray();
				$items       = array_merge($currentItems, $backupItems);

				LanguageHelper::saveToIniFile($currentFile, $items);
			}
		}

		//Restore the renamed files
		if (is_file(JPATH_ROOT . '/components/com_eshop/bak.fields.xml'))
		{
			File::copy(JPATH_ROOT . '/components/com_eshop/bak.fields.xml', JPATH_ROOT . '/components/com_eshop/fields.xml');
			File::delete(JPATH_ROOT . '/components/com_eshop/bak.fields.xml');
		}

		//Copy checkout folder of other themes
		$content = '';
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_themes')
			->where('name != "default"');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if (count($rows))
		{
			foreach ($rows as $row)
			{
				if (!is_dir(JPATH_ROOT . '/components/com_eshop/themes/' . $row->name . '/views/checkout'))
				{
					Folder::copy(
						JPATH_ROOT . '/components/com_eshop/themes/default/views/checkout',
						JPATH_ROOT . '/components/com_eshop/themes/' . $row->name . '/views/checkout'
					);
				}
				//Create custom.css file if it is not existed
				if (!is_file(JPATH_ROOT . '/components/com_eshop/themes/' . $row->name . '/css/custom.css'))
				{
					File::write(JPATH_ROOT . '/components/com_eshop/themes/' . $row->name . '/css/custom.css', $content);
				}
			}
		}

		//Create custom.css file if it is not existed
		if (!is_file(JPATH_ROOT . '/components/com_eshop/themes/default/css/custom.css'))
		{
			File::write(JPATH_ROOT . '/components/com_eshop/themes/default/css/custom.css', $content);
		}
	}

	/**
	 * Calculate level for categories, used when upgrade from old version to new version
	 *
	 * @param        $id
	 * @param        $list
	 * @param        $children
	 * @param   int  $maxlevel
	 * @param   int  $level
	 *
	 * @return mixed
	 */
	public static function calculateCategoriesLevel($id, $list, &$children, $maxlevel = 9999, $level = 1)
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id        = $v->id;
				$v->level  = $level;
				$list[$id] = $v;
				$list      = self::calculateCategoriesLevel($id, $list, $children, $maxlevel, $level + 1);
			}
		}

		return $list;
	}

	/**
	 * Add missing indexes to speed up EShop
	 *
	 * @return void
	 */
	private function addMissingIndexes(): void
	{
		// For performance reason, we do not only check and add indexes if the installed version smaller than 4.0.0
		if ($this->installedVersion && version_compare($this->installedVersion, '4.0.0', '>='))
		{
			return;
		}

		$db = Factory::getDbo();
		$indexes = [
			'#__eshop_addresses'               => ['customer_id'],
			'#__eshop_attributedetails'        => ['attribute_id', 'language'],
			'#__eshop_attributegroupdetails'   => ['attributegroup_id', 'language'],
			'#__eshop_attributegroups'         => ['published'],
			'#__eshop_attributes'              => ['attributegroup_id', 'published'],
			'#__eshop_carts'                   => ['customer_id', 'is_1st_sent', 'is_2nd_sent', 'is_3rd_sent'],
			'#__eshop_categories'              => ['category_parent_id', 'published'],
			'#__eshop_categorydetails'         => ['category_id', 'language'],
			'#__eshop_countries'               => ['published'],
			'#__eshop_couponcategories'        => ['coupon_id', 'category_id'],
			'#__eshop_couponcustomergroups'    => ['coupon_id', 'customergroup_id'],
			'#__eshop_couponhistory'           => ['coupon_id', 'order_id', 'user_id'],
			'#__eshop_couponproducts'          => ['coupon_id', 'product_id'],
			'#__eshop_coupons'                 => ['published'],
			'#__eshop_currencies'              => ['published'],
			'#__eshop_customergroupdetails'    => ['customergroup_id', 'language'],
			'#__eshop_customers'               => ['customergroup_id', 'customer_id', 'address_id', 'published'],
			'#__eshop_discountelements'        => ['discount_id', 'element_id', 'element_type'],
			'#__eshop_discounts'               => ['discount_type', 'published'],
			'#__eshop_manufacturerdetails'     => ['manufacturer_id', 'language'],
			'#__eshop_manufacturers'           => ['published'],
			'#__eshop_notify'                  => ['product_id'],
			'#__eshop_optiondetails'           => ['option_id', 'language'],
			'#__eshop_options'                 => ['published'],
			'#__eshop_optionvaluedetails'      => ['optionvalue_id', 'option_id', 'language'],
			'#__eshop_optionvalues'            => ['option_id', 'published'],
			'#__eshop_orderoptions'            => ['order_id', 'product_id', 'order_product_id', 'product_option_id', 'product_option_value_id'],
			'#__eshop_orderdownloads'          => ['order_id', 'order_product_id', 'download_id'],
			'#__eshop_orderproducts'           => ['order_id', 'product_id'],
			'#__eshop_ordertotals'             => ['order_id'],
			'#__eshop_productattributedetails' => ['productattribute_id', 'product_id'],
			'#__eshop_productattachments'      => ['product_id'],
			'#__eshop_productattributes'       => ['product_id', 'attribute_id', 'published'],
			'#__eshop_productcategories'       => ['product_id', 'category_id', 'main_category'],
			'#__eshop_productdetails'          => ['product_id', 'language'],
			'#__eshop_productdiscounts'    => ['product_id', 'customergroup_id', 'published'],
			'#__eshop_productdownloads'    => ['product_id', 'download_id'],
			'#__eshop_productimages'       => ['product_id', 'published'],
			'#__eshop_productoptions'      => ['product_id', 'option_id'],
			'#__eshop_productoptionvalues' => ['product_option_id', 'product_id', 'option_id', 'option_value_id'],
			'#__eshop_productrelations'    => ['product_id', 'related_product_id'],
			'#__eshop_products'            => ['manufacturer_id', 'published'],
			'#__eshop_productspecials'     => ['product_id', 'customergroup_id', 'published'],
			'#__eshop_producttags'         => ['product_id', 'tag_id'],
			'#__eshop_quoteoptions'        => ['quote_id', 'quote_product_id', 'product_option_id', 'product_option_value_id'],
			'#__eshop_quoteproducts'       => ['quote_id', 'product_id'],
			'#__eshop_quotes'              => ['customer_id'],
			'#__eshop_reviews'             => ['product_id', 'customer_id'],
			'#__eshop_tags'                => ['published'],
			'#__eshop_taxcustomergroups'   => ['tax_id', 'customergroup_id'],
			'#__eshop_taxes'               => ['geozone_id', 'published'],
			'#__eshop_taxrules'            => ['taxclass_id', 'tax_id', 'based_on'],
			'#__eshop_zones'               => ['country_id'],
			'#__eshop_voucherhistory'      => ['order_id', 'voucher_id', 'user_id'],
			'#__eshop_vouchers'            => ['published'],
		];

		foreach ($indexes as $table => $indexFields)
		{
			$sql = 'SHOW INDEX FROM ' . $table;
			$db->setQuery($sql);
			$fields = [];

			foreach ($db->loadObjectList() as $row)
			{
				$fields[] = $row->Column_name;
			}

			foreach ($indexFields as $indexField)
			{
				if (in_array($indexField, $fields))
				{
					continue;
				}

				$sql = "CREATE INDEX `idx_{$indexField}` ON `$table` (`{$indexField}`);";
				$db->setQuery($sql)
					->execute();
			}
		}
	}
}