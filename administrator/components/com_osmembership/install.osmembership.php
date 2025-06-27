<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

class com_osmembershipInstallerScript
{
	/**
	 * Minimum PHP version
	 */
	public const MIN_PHP_VERSION = '7.4.0';

	/**
	 * Minimum Joomla version
	 */
	public const MIN_JOOMLA_VERSION = '4.2.0';

	/**
	 * Minimum Membership Pro version to allow update
	 */
	public const MIN_MEMBERSHIP_PRO_VERSION = '2.7.0';

	/**
	 * The application object
	 *
	 * @var CMSApplication
	 */
	protected $app;

	/**
	 * Language files which need to be manipulated during upgrade process
	 *
	 * @var array
	 */
	public static $languageFiles = ['en-GB.com_osmembership.ini', 'admin.en-GB.com_osmembershipcommon.ini'];

	/**
	 * The original version, use for update process
	 *
	 * @var string
	 */
	protected $installedVersion = '2.7.0';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Get and store current installed version
		$this->getInstalledVersion();

		$this->app = Factory::getApplication();
	}

	/**
	 * Method to run before installing the component. Using to backup language file in this case
	 *
	 * @param   string            $type
	 * @param   ComponentAdapter  $parent
	 */
	public function preflight($type, $parent)
	{
		if (!version_compare(JVERSION, self::MIN_JOOMLA_VERSION, 'ge'))
		{
			$this->app->enqueueMessage(
				'Cannot install Membership Pro in a Joomla release prior to ' . self::MIN_JOOMLA_VERSION,
				'error'
			);

			return false;
		}

		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			$this->app->enqueueMessage(
				'Membership Pro requires PHP ' . self::MIN_PHP_VERSION . '+ to work. Please contact your hosting provider, ask them to update PHP version for your hosting account.',
				'error'
			);

			return false;
		}

		if (version_compare($this->installedVersion, self::MIN_MEMBERSHIP_PRO_VERSION, '<'))
		{
			$this->app->enqueueMessage(
				'Update from older version than ' . self::MIN_MEMBERSHIP_PRO_VERSION . ' is not supported! You need to update to version 2.26.0 first. Please contact support to get that old Membership Pro version',
				'error'
			);

			return false;
		}

		// If this is not update, we don't have to do anything
		if (strtolower($type) != 'update')
		{
			return true;
		}

		// Allow custom translation for backend language file from 3.0.0
		if (version_compare($this->installedVersion, '3.0.0', '>=')
			&& !in_array('admin.en-GB.com_osmembership.ini', self::$languageFiles))
		{
			self::$languageFiles[] = 'admin.en-GB.com_osmembership.ini';
		}

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

		// Delete files/folders from old version which is not needed in new version anymore
		$this->deleteFilesFolders();
	}

	/**
	 * Method to run after installing the component
	 *
	 * @param   string            $type
	 * @param   ComponentAdapter  $parent
	 */
	public function postflight($type, $parent)
	{
		// We do not need to run the process on uninstall
		if (strtolower($type) == 'uninstall')
		{
			return;
		}

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		//Restore the modified language strings by merging to language files
		$this->restoreLanguageFiles();

		// Create needed files and folders
		$this->createFilesFolders();

		// Delete old update site
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->delete('#__update_sites')
			->where('location = ' . $db->quote('https://www.joomdonation.com/updates/membershippro.xml'));
		$db->setQuery($query)
			->execute();

		if (strtolower($type) == 'update')
		{
			// Create new tables if not exist
			static::createTablesIfNotExist();

			// Synchronize db schema to latest version
			$this->updateDBSchema();

			// Create indexes
			if (version_compare($this->installedVersion, '2.26.0', '<'))
			{
				static::createIndexes();
			}

			if (version_compare($this->installedVersion, '3.6.2', '<='))
			{
				/* @var \Joomla\Database\DatabaseDriver $db */
				$db    = Factory::getContainer()->get('db');
				$query = $db->getQuery(true)
					->update('#__osmembership_mitems')
					->set('title = ' . $db->quote('OSM_INVITE_GROUP_MEMBERS_EMAIL_SUBJECT'))
					->where('name = ' . $db->quote('invite_group_members_email_subject'));
				$db->setQuery($query)
					->execute();

				$query->clear()
					->update('#__osmembership_mitems')
					->set('title = ' . $db->quote('OSM_INVITE_GROUP_MEMBERS_EMAIL_BODY'))
					->where('name = ' . $db->quote('invite_group_members_message'));
				$db->setQuery($query)
					->execute();
			}

			// Update wrong messages item update
			if (version_compare($this->installedVersion, '3.3.1', '<'))
			{
				$this->correctMessageItemsType();
			}

			// Convert tables to utf8mb4
			if (version_compare($this->installedVersion, '4.2.2', '<='))
			{
				$this->convertDBTablesToUtf8mb4();
			}
		}

		// Setup default data
		self::setupDefaultData();

		// Enable required plugins
		self::enableRequiredPlugin($type);

		if (Multilanguage::isEnabled())
		{
			OSMembershipHelper::callOverridableHelperMethod('Helper', 'setupMultilingual');
		}
	}

	/**
	 * Delete files/folders from old installation
	 *
	 * @return void
	 */
	private function deleteFilesFolders()
	{
		$deleteFiles = $deleteFolders = [];

		if (version_compare($this->installedVersion, '2.26.0', '<'))
		{
			$deleteFolders = [
				JPATH_ROOT . '/components/com_osmembership/assets/validate',
				JPATH_ROOT . '/components/com_osmembership/assets/models',
				JPATH_ROOT . '/components/com_osmembership/assets/views',
				JPATH_ROOT . '/components/com_osmembership/assets/libraries',
				JPATH_ROOT . '/components/com_osmembership/views',
				JPATH_ROOT . '/components/com_osmembership/view/common',
				JPATH_ROOT . '/components/com_osmembership/emailtemplates',
				JPATH_ADMINISTRATOR . '/components/com_osmembership/controllers',
				JPATH_ADMINISTRATOR . '/components/com_osmembership/models',
				JPATH_ADMINISTRATOR . '/components/com_osmembership/views',
				JPATH_ADMINISTRATOR . '/components/com_osmembership/tables',
				JPATH_ADMINISTRATOR . '/components/com_osmembership/libraries',
				JPATH_ADMINISTRATOR . '/components/com_osmembership/libraries/legacy',
			];

			$deleteFiles = [
				JPATH_ROOT . '/components/com_osmembership/helper/fields.php',
				JPATH_ROOT . '/components/com_osmembership/ipn_logs.txt',
				JPATH_ROOT . '/components/com_osmembership/plugins/os_authnet_arb.php',
				JPATH_ROOT . '/components/com_osmembership/views/complete/metadata.xml',
				JPATH_ROOT . '/components/com_osmembership/view/complete/metadata.xml',
				JPATH_ROOT . '/components/com_osmembership/controller.php',
				JPATH_ADMINISTRATOR . '/components/com_osmembership/controller.php',
				JPATH_ADMINISTRATOR . '/components/com_osmembership/libraries/factory.php',
			];
		}

		if (version_compare($this->installedVersion, '4.0.0', '<'))
		{
			$deleteFiles = array_merge($deleteFiles, [
				JPATH_ADMINISTRATOR . '/components/com_osmembership/libraries/mpf/ui/bootstrap2.php',
				JPATH_ADMINISTRATOR . '/components/com_osmembership/libraries/mpf/ui/bootstrap3.php',
				JPATH_ROOT . '/media/com_osmembership/assets/js/validate/js/jquery.validationEngine.min.js',
			]);
		}

		foreach ($deleteFolders as $folder)
		{
			if (is_dir($folder))
			{
				Folder::delete($folder);
			}
		}

		foreach ($deleteFiles as $file)
		{
			if (is_file($file))
			{
				File::delete($file);
			}
		}
	}

	/*
	 * Restore language files, merge the customized language string with the new language file
	 *
	 * @return void
	 */
	private function restoreLanguageFiles()
	{
		// Allow custom translation for backend language file from 3.0.0
		if (version_compare($this->installedVersion, '3.0.0', '>=')
			&& !in_array('admin.en-GB.com_osmembership.ini', self::$languageFiles))
		{
			self::$languageFiles[] = 'admin.en-GB.com_osmembership.ini';
		}

		foreach (self::$languageFiles as $languageFile)
		{
			$registry = new Registry();

			if ($languageFile == 'admin.en-GB.com_osmembership.ini')
			{
				$updateItemsFile = 'items.php';
			}
			elseif ($languageFile == 'admin.en-GB.com_osmembershipcommon.ini')
			{
				$updateItemsFile = 'cmitems.php';
			}
			elseif ($languageFile == 'en-GB.com_osmembership.ini')
			{
				$updateItemsFile = 'feitems.php';
			}
			else
			{
				$updateItemsFile = '';
			}

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

				if ($updateItemsFile)
				{
					$updateLanguageItems = require JPATH_ADMINISTRATOR . '/components/com_osmembership/updates/' . $updateItemsFile;

					foreach ($updateLanguageItems as $version => $itemsToUpdate)
					{
						if (version_compare($this->installedVersion, $version, '<='))
						{
							$items = array_merge($items, $itemsToUpdate);
						}
					}
				}

				LanguageHelper::saveToIniFile($currentFile, $items);
			}
		}

		if (is_file(JPATH_ROOT . '/components/com_osmembership/assets/css/custom.css'))
		{
			File::move(
				JPATH_ROOT . '/components/com_osmembership/com_osmembership/assets/css/custom.css',
				JPATH_ROOT . '/media/com_osmembership/assets/css/custom.css'
			);
		}

		if (is_dir(JPATH_ROOT . '/components/com_osmembership/assets'))
		{
			Folder::delete(JPATH_ROOT . '/components/com_osmembership/assets');
		}
	}

	/**
	 * Create necessary files and foldres
	 *
	 * @return void
	 */
	private function createFilesFolders()
	{
		$customCss = JPATH_ROOT . '/media/com_osmembership/assets/css/custom.css';

		if (!file_exists($customCss))
		{
			$fp = fopen($customCss, 'w');
			fclose($fp);
			@chmod($customCss, 0644);
		}

		$distFile   = JPATH_ROOT . '/components/com_osmembership/layouts/joomla/form/field/user.dist.php';
		$layoutFile = JPATH_ROOT . '/components/com_osmembership/layouts/joomla/form/field/user.php';

		if (is_file($layoutFile))
		{
			File::delete($layoutFile);
		}

		File::move($distFile, $layoutFile);
	}

	/**
	 * Create tables which were not exist in old version
	 *
	 * @return void
	 */
	public static function createTablesIfNotExist()
	{
		$sqlFile = JPATH_ADMINISTRATOR . '/components/com_osmembership/sql/createifnotexists.osmembership.sql';

		OSMembershipHelper::executeSqlFile($sqlFile);
	}

	/**
	 * Update database schema, add fields which are added in new version
	 *
	 * @return void
	 */
	private function updateDBSchema()
	{
		static::synchronizeDBSchema($this->installedVersion);
	}

	/**
	 * Proxy for updateDBSchema, the method needs to be static so that it can be easily called outside
	 *
	 * @param   string  $installedVersion
	 */
	public static function synchronizeDBSchema($installedVersion = '1.0.0')
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db     = Factory::getContainer()->get('db');
		$query  = $db->getQuery(true);
		$config = OSMembershipHelper::getConfig();

		$fields = array_keys($db->getTableColumns('#__osmembership_countries'));

		if (!in_array('ordering', $fields))
		{
			$sql = "ALTER TABLE `#__osmembership_countries` ADD `ordering` INT UNSIGNED NOT NULL DEFAULT 0";
			$db->setQuery($sql)
				->execute();

			$query->clear()
				->select('id')
				->from('#__osmembership_countries');

			if (in_array('featured', $fields))
			{
				$query->order('featured DESC');
			}

			$query->order('name');
			$db->setQuery($query);

			$ordering = 0;

			foreach ($db->loadObjectList() as $row)
			{
				$query->clear()
					->update('#__osmembership_countries')
					->set('ordering = ' . $ordering)
					->where('id = ' . $row->id);
				$db->setQuery($query)
					->execute();
				$ordering++;
			}
		}

		if (version_compare($installedVersion, '2.26.0', '<'))
		{
			// Change row format of the necessary tables
			$tables = [
				'#__osmembership_categories',
				'#__osmembership_plans',
				'#__osmembership_fields',
				'#__osmembership_subscribers',
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
		}

		$fields = array_keys($db->getTableColumns('#__osmembership_schedulecontent'));

		if (!in_array('ordering', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_schedulecontent` ADD  `ordering` INT NOT NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__osmembership_schedulecontent SET `ordering` = `id`';
			$db->setQuery($sql)
				->execute();
		}

		$fields = array_keys($db->getTableColumns('#__osmembership_schedule_k2items'));

		if (!in_array('ordering', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_schedule_k2items` ADD  `ordering` INT NOT NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__osmembership_schedule_k2items SET `ordering` = `id`';
			$db->setQuery($sql)
				->execute();
		}

		#Custom Fields table
		$fields = array_keys($db->getTableColumns('#__osmembership_fields'));

		if (!in_array('fee_usage', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD `fee_usage` TINYINT NOT NULL DEFAULT 2;";
			$db->setQuery($sql)
				->execute();

			$config      = OSMembershipHelper::getConfig();
			$feeBehavior = (int) $config->get('custom_fee_behavior', 2);
			$query->clear()
				->update('#__osmembership_fields')
				->set('fee_usage = ' . $feeBehavior)
				->where('fee_field = 1');
			$db->setQuery($query)
				->execute();
		}

		if (!in_array('synchronize_data', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `synchronize_data` tinyint UNSIGNED DEFAULT 0;";
			$db->setQuery($sql)
				->execute();

			// If synchronize data is enabled, set it to all fields
			if ($config->synchronize_data)
			{
				$sql = 'UPDATE #__osmembership_fields SET `synchronize_data` = 1';
				$db->setQuery($sql)
					->execute();
			}
		}

		if (!in_array('position', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `position` tinyint UNSIGNED DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('payment_method', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `payment_method` varchar(255) DEFAULT '';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('input_mask', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `input_mask` varchar(255) DEFAULT '';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('readonly', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD `readonly` TINYINT NOT NULL DEFAULT 0 ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('receive_emails', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD `receive_emails` TINYINT NOT NULL DEFAULT 0 ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('filter', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `filter` VARCHAR(100) NOT NULL DEFAULT '';";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__osmembership_fields SET filter = "STRING" WHERE is_core = 1';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('container_class', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `container_class` VARCHAR(255) NOT NULL DEFAULT '';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('container_size', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `container_size` VARCHAR(50) NOT NULL DEFAULT '';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('input_size', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `input_size` VARCHAR(50) NOT NULL DEFAULT '';";
			$db->setQuery($sql)
				->execute();
		}

		// Field assignment
		if (!in_array('assignment', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD `assignment` TINYINT NOT NULL DEFAULT 0 ;";
			$db->setQuery($sql)
				->execute();

			$query->clear()
				->update('#__osmembership_fields')
				->set('assignment = 1')
				->where('plan_id = 1');
			$db->setQuery($query)
				->execute();
		}

		if (!in_array('allowed_file_types', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `allowed_file_types` VARCHAR( 400 ) NULL DEFAULT  NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('show_on_subscription_payment', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD `show_on_subscription_payment` TINYINT NOT NULL DEFAULT 0 ;";
			$db->setQuery($sql)
				->execute();

			$query->clear()
				->update('#__osmembership_fields')
				->set('show_on_subscription_payment = 1')
				->where('id < 13');
			$db->setQuery($query)
				->execute();
		}

		if (!in_array('taxable', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `taxable` TINYINT NOT NULL DEFAULT 1;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('newsletter_field_mapping', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `newsletter_field_mapping` VARCHAR( 255 ) NULL DEFAULT  NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('populate_from_previous_subscription', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `populate_from_previous_subscription` TINYINT NOT NULL DEFAULT 1";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('prompt_text', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `prompt_text` VARCHAR( 255 ) NULL DEFAULT  NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('filterable', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `filterable` TINYINT NOT NULL DEFAULT 0";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('pattern', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `pattern` VARCHAR( 255 ) NULL DEFAULT  NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('min', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `min` DECIMAL(10,2) DEFAULT NULL";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('max', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `max` DECIMAL(10,2) DEFAULT NULL";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('step', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `step` DECIMAL(10,2) DEFAULT 0";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('show_on_subscription_form', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `show_on_subscription_form` TINYINT NOT NULL DEFAULT 1;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('show_on_subscriptions', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `show_on_subscriptions` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SELECT COUNT(*) FROM #__osmembership_fields WHERE show_on_subscriptions = 1';
		$db->setQuery($sql);

		if (!$db->loadResult())
		{
			// We should make at least first_name and last_name fields shown
			$sql = 'UPDATE #__osmembership_fields SET show_on_subscriptions = 1 WHERE id IN (1, 2)';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('hide_on_email', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `hide_on_email` TINYINT NOT NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('hide_on_export', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `hide_on_export` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('show_on_group_member_form', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `show_on_group_member_form` TINYINT NOT NULL DEFAULT  1;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('is_searchable', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `is_searchable` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('show_on_profile', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `show_on_profile` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE `#__osmembership_fields` SET show_on_profile = show_on_members_list';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('show_on_user_profile', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `show_on_user_profile` TINYINT NOT NULL DEFAULT  1;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('server_validation_rules', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `server_validation_rules` VARCHAR( 255 ) NULL DEFAULT '';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('can_edit_on_profile', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `can_edit_on_profile` TINYINT NOT NULL DEFAULT  1;";
			$db->setQuery($sql)
				->execute();

			// Mark on fee fields not editable
			$sql = 'UPDATE `#__osmembership_fields` SET can_edit_on_profile = 0 WHERE fee_field = 1';
			$db->setQuery($sql);
			$db->execute();
		}

		if (!in_array('populate_from_group_admin', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `populate_from_group_admin` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (version_compare($installedVersion, '4.1.2', '<='))
		{
			$sql = "ALTER TABLE  `#__osmembership_fields` MODIFY  `min` decimal(10,2) DEFAULT NULL;";
			$db->setQuery($sql)
				->execute();
			$sql = "ALTER TABLE  `#__osmembership_fields` MODIFY  `max` decimal(10,2) DEFAULT NULL;";
			$db->setQuery($sql)
				->execute();
			$sql = "ALTER TABLE  `#__osmembership_fields` MODIFY  `step` decimal(10,2) DEFAULT 0.00;";
			$db->setQuery($sql)
				->execute();

			$query = $db->getQuery(true)
				->update('#__osmembership_fields')
				->set($db->quoteName('min') . ' = NULL')
				->set($db->quoteName('max') . ' = NULL')
				->where($db->quoteName('min') . ' = 0')
				->where($db->quoteName('max') . ' = 0');
			$db->setQuery($query)
				->execute();
		}

		$fields = array_keys($db->getTableColumns('#__osmembership_categories'));

		$textFields = [
			'subscription_form_message',
			'user_email_body',
			'user_email_body_offline',
			'admin_email_body',
			'thanks_message',
			'thanks_message_offline',
			'user_renew_email_body',
			'user_renew_email_body_offline',
			'subscription_renew_form_msg',
			'admin_renew_email_body',
			'renew_thanks_message',
			'renew_thanks_message_offline',
			'subscription_upgrade_form_msg',
			'user_upgrade_email_body',
			'user_upgrade_email_body_offline',
			'admin_upgrade_email_body',
			'upgrade_thanks_message',
			'upgrade_thanks_message_offline',
			'subscription_approved_email_body',
			'first_reminder_email_body',
			'second_reminder_email_body',
			'third_reminder_email_body',
		];

		foreach ($textFields as $textField)
		{
			if (!in_array($textField, $fields))
			{
				$sql = "ALTER TABLE  `#__osmembership_categories` ADD `$textField` text;";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (!in_array('exclusive_plans', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_categories` ADD  `exclusive_plans` TINYINT NOT NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('grouping_plans', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_categories` ADD  `grouping_plans` TINYINT NOT NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		#Subscription plans table
		$fields = array_keys($db->getTableColumns('#__osmembership_plans'));

		if (!in_array('number_members_field', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `number_members_field` INT NOT NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('require_coupon', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD `require_coupon` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('enable_sms_reminder', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD `enable_sms_reminder` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('payment_day', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD `payment_day` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('created_by', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD `created_by` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('admin_email_body', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `admin_email_body` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('admin_renew_email_body', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `admin_renew_email_body` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('admin_upgrade_email_body', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `admin_upgrade_email_body` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('subscriptions_manage_user_id', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD `subscriptions_manage_user_id` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('grace_period', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD `grace_period` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('invoice_layout', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `invoice_layout` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('activate_member_card_feature', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `activate_member_card_feature` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();

			if ($config->activate_member_card_feature)
			{
				$sql = 'UPDATE `#__osmembership_plans` SET activate_member_card_feature = 1';
				$db->setQuery($sql)
					->execute();
			}
		}

		if (!in_array('card_bg_image', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `card_bg_image` varchar(255) NOT NULL DEFAULT '';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('card_layout', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `card_layout` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('subscription_renew_form_msg', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `subscription_renew_form_msg` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('renew_thanks_message', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `renew_thanks_message` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('renew_thanks_message_offline', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `renew_thanks_message_offline` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('upgrade_thanks_message', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `upgrade_thanks_message` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('upgrade_thanks_message_offline', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `upgrade_thanks_message_offline` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('subscription_end_email_subject', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `subscription_end_email_subject` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('subscription_end_email_body', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `subscription_end_email_body` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('free_plan_subscription_status', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `free_plan_subscription_status` TINYINT NOT NULL DEFAULT  1 ;";
			$db->setQuery($sql)
				->execute();

			$freePlanSubscriptionStatus = $config->get('free_plans_subscription_status', 1);
			$query                      = $db->getQuery(true)
				->update('#__osmembership_plans')
				->set('free_plan_subscription_status = ' . (int) $freePlanSubscriptionStatus);
			$db->setQuery($query)
				->execute();
		}

		if (!in_array('page_title', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `page_title` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('page_heading', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `page_heading` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('meta_keywords', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `meta_keywords` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('meta_description', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `meta_description` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('publish_up', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('publish_down', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('subscribe_access', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD `subscribe_access` INT NOT NULL DEFAULT  1;";
			$db->setQuery($sql)
				->execute();

			$query = $db->getQuery(true)
				->update('#__osmembership_plans')
				->set('subscribe_access = `access`');
			$db->setQuery($query)
				->execute();
		}

		if (!in_array('last_payment_action', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `last_payment_action` TINYINT NOT NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();

			if (in_array('set_lifetime_subscription', $fields))
			{
				$sql = 'UPDATE #__osmembership_plans SET last_payment_action = 1 WHERE set_lifetime_subscription = 1';
				$db->setQuery($sql)
					->execute();
			}
		}

		if (!in_array('extend_duration', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `extend_duration` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('extend_duration_unit', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `extend_duration_unit` CHAR(1) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('offline_payment_subscription_complete_url', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `offline_payment_subscription_complete_url` TEXT NULL ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('send_subscription_end', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `send_subscription_end` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('conversion_tracking_code', $fields))
		{
			$sql = "ALTER TABLE `#__osmembership_plans` ADD `conversion_tracking_code` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		// Reminder email messages
		if (!in_array('user_renew_email_body_offline', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `user_renew_email_body_offline` TEXT NULL ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('user_upgrade_email_body', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `user_upgrade_email_body` TEXT NULL ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('user_upgrade_email_body_offline', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `user_upgrade_email_body_offline` TEXT NULL ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('custom_fields', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `custom_fields` TEXT NULL ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('hidden', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_plans` ADD `hidden` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		#Subscription plans table
		$fields = array_keys($db->getTableColumns('#__osmembership_documents'));

		if (!in_array('update_package', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_documents` ADD  `update_package` varchar(255) NOT NULL DEFAULT '';";
			$db->setQuery($sql)
				->execute();
		}

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__osmembership_plan_documents');
		$db->setQuery($query);
		$total = $db->loadResult();

		if ($total == 0)
		{
			$sql = 'INSERT INTO #__osmembership_plan_documents(plan_id, document_id) SELECT plan_id, id FROM #__osmembership_documents';
			$db->setQuery($sql)
				->execute();
		}

		// Renewal Discount
		$fields = array_keys($db->getTableColumns('#__osmembership_renewaldiscounts'));

		if (!in_array('title', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_renewaldiscounts` ADD  `title` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('published', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_renewaldiscounts` ADD  `published` TINYINT NOT NULL DEFAULT 1;";
			$db->setQuery($sql)
				->execute();
		}

		// Subscribers table
		$fields = array_keys($db->getTableColumns('#__osmembership_subscribers'));

		$sql = "ALTER TABLE  `#__osmembership_subscribers` CHANGE `email` `email` VARCHAR( 255 ) NULL;";
		$db->setQuery($sql)
			->execute();

		if (!in_array('eb_coupon_id', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `eb_coupon_id` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('offline_payment_reminder_email_sent', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `offline_payment_reminder_email_sent` tinyint NOT NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('icps_notified', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `icps_notified` tinyint NOT NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('active_event_triggered', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `active_event_triggered` tinyint NOT NULL DEFAULT 1;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ip_address', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `ip_address` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('subscription_code', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `subscription_code` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('first_sms_reminder_sent', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `first_sms_reminder_sent` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('second_sms_reminder_sent', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `second_sms_reminder_sent` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('third_sms_reminder_sent', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `third_sms_reminder_sent` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('formatted_invoice_number', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `formatted_invoice_number` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('formatted_membership_id', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `formatted_membership_id` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('process_payment_for_subscription', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `process_payment_for_subscription` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('vies_registered', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `vies_registered` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('offline_recurring_email_sent', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `offline_recurring_email_sent` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('show_on_members_list', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `show_on_members_list` TINYINT NOT NULL DEFAULT  1;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('refunded', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `refunded` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('parent_id', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `parent_id` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('auto_subscribe_processed', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `auto_subscribe_processed` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('is_free_trial', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `is_free_trial` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('subscribe_newsletter', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `subscribe_newsletter` TINYINT NOT NULL DEFAULT  1;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('agree_privacy_policy', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `agree_privacy_policy` TINYINT NOT NULL DEFAULT  1;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('mollie_customer_id', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `mollie_customer_id` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('mollie_recurring_start_date', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `mollie_recurring_start_date` DATETIME NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('tax_rate', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `tax_rate` DECIMAL( 10, 6 ) NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('trial_payment_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `trial_payment_amount` DECIMAL( 10, 6 ) NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('payment_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `payment_amount` DECIMAL( 10, 6 ) NULL DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('payment_currency', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `payment_currency` VARCHAR( 15 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('receiver_email', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `receiver_email` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('subscription_end_sent', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `subscription_end_sent` TINYINT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('subscription_end_sent_at', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `subscription_end_sent_at` DATETIME NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('gateway_customer_id', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_subscribers` ADD  `gateway_customer_id` VARCHAR( 100 ) NULL;";
			$db->setQuery($sql)
				->execute();

			$query = $db->getQuery(true);
			$query->update('#__osmembership_subscribers')
				->set('gateway_customer_id = transaction_id')
				->where('payment_method = "os_stripe"')
				->where('transaction_id LIKE "cus_%"');
			$db->setQuery($query)
				->execute();

			$query->clear()
				->update('#__osmembership_subscribers')
				->set('gateway_customer_id = mollie_customer_id')
				->where('payment_method = "os_mollie"');
			$db->setQuery($query)
				->execute();
		}

		#Payment Plugins table
		$fields = array_keys($db->getTableColumns('#__osmembership_coupons'));

		// Field assignment
		if (!in_array('assignment', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_coupons` ADD `assignment` TINYINT NOT NULL DEFAULT 0 ;";
			$db->setQuery($sql)
				->execute();

			$query->clear()
				->update('#__osmembership_coupons')
				->set('assignment = 1')
				->where('plan_id = 1');
			$db->setQuery($query)
				->execute();
		}

		if (!in_array('note', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_coupons` ADD  `note` VARCHAR(255) DEFAULT  '';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('subscription_type', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_coupons` ADD  `subscription_type` VARCHAR(50) DEFAULT  '';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('user_id', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_coupons` ADD  `user_id` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('max_usage_per_user', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_coupons` ADD  `max_usage_per_user` INT NOT NULL DEFAULT  0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('access', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_coupons` ADD  `access` INT NOT NULL DEFAULT  1;";
			$db->setQuery($sql)
				->execute();
		}

		$fields = array_keys($db->getTableColumns('#__osmembership_urls'));

		if (!in_array('title', $fields))
		{
			$sql = "ALTER TABLE  `#__osmembership_urls` ADD  `title` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (version_compare($installedVersion, '2.26.0', '<'))
		{
			// Update data for user_id and plan_id fields in #__osmembership_subscribers to avoid error
			$sql = 'ALTER TABLE `#__osmembership_subscribers` CHANGE `plan_id` `plan_id` INT NOT NULL DEFAULT 0;';
			$db->setQuery($sql)
				->execute();

			$sql = 'ALTER TABLE `#__osmembership_subscribers` CHANGE `user_id` `user_id` INT NOT NULL DEFAULT 0;';
			$db->setQuery($sql)
				->execute();
		}

		$fields = array_keys($db->getTableColumns('#__osmembership_renewrates'));

		if (!in_array('ordering', $fields))
		{
			$sql = "ALTER TABLE `#__osmembership_renewrates` ADD `ordering` INT UNSIGNED NOT NULL DEFAULT 0";
			$db->setQuery($sql)
				->execute();

			$query->clear()
				->select('id, plan_id')
				->from('#__osmembership_renewrates')
				->order('id');
			$db->setQuery($query);

			$renewOptions = [];

			foreach ($db->loadObjectList() as $row)
			{
				$renewOptions[$row->plan_id][] = $row;
			}

			foreach ($renewOptions as $rows)
			{
				$ordering = 0;

				foreach ($rows as $row)
				{
					$query->clear()
						->update('#__osmembership_renewrates')
						->set('ordering = ' . $ordering)
						->where('id = ' . $row->id);
					$db->setQuery($query)
						->execute();
					$ordering++;
				}
			}
		}

		$fields = array_keys($db->getTableColumns('#__osmembership_upgraderules'));

		if (!in_array('ordering', $fields))
		{
			$sql = "ALTER TABLE `#__osmembership_upgraderules` ADD `ordering` INT UNSIGNED NOT NULL DEFAULT 0";
			$db->setQuery($sql)
				->execute();

			$query->clear()
				->select('id, from_plan_id')
				->from('#__osmembership_upgraderules')
				->order('id');
			$db->setQuery($query);

			$upgradeOptions = [];

			foreach ($db->loadObjectList() as $row)
			{
				$upgradeOptions[$row->from_plan_id][] = $row;
			}

			foreach ($upgradeOptions as $rows)
			{
				$ordering = 0;

				foreach ($rows as $row)
				{
					$query->clear()
						->update('#__osmembership_upgraderules')
						->set('ordering = ' . $ordering)
						->where('id = ' . $row->id);
					$db->setQuery($query)
						->execute();
					$ordering++;
				}
			}
		}

		// Remove .git hidden folders
		if (version_compare($installedVersion, '3.4.2', '<='))
		{
			self::removeHiddenFolders();
		}
	}

	/**
	 * Setup initialize data for the extension
	 *
	 * @return void
	 */
	public static function setupDefaultData()
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->clear()
			->select('COUNT(*)')
			->from('#__osmembership_configs');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$sqlFile = JPATH_ADMINISTRATOR . '/components/com_osmembership/sql/config.osmembership.sql';
			OSMembershipHelper::executeSqlFile($sqlFile);
		}

		// Fields
		$query->clear()
			->select('COUNT(*)')
			->from('#__osmembership_fields');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$sqlFile = JPATH_ADMINISTRATOR . '/components/com_osmembership/sql/fields.osmembership.sql';
			OSMembershipHelper::executeSqlFile($sqlFile);
		}

		// Messages
		$query->clear()
			->select('COUNT(*)')
			->from('#__osmembership_messages');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$sqlFile = JPATH_ADMINISTRATOR . '/components/com_osmembership/sql/install.messages.sql';
			OSMembershipHelper::executeSqlFile($sqlFile);
		}

		// Payment Plugins
		$query->clear()
			->select('COUNT(*)')
			->from('#__osmembership_plugins');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$sqlFile = JPATH_ADMINISTRATOR . '/components/com_osmembership/sql/plugins.osmembership.sql';
			OSMembershipHelper::executeSqlFile($sqlFile);
		}

		// Countries/states
		$query->clear()
			->select('COUNT(*)')
			->from('#__osmembership_countries');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$sqlFile = JPATH_ADMINISTRATOR . '/components/com_osmembership/sql/countries_states.sql';
			OSMembershipHelper::executeSqlFile($sqlFile);
		}

		// Menus
		$sqlFile = JPATH_ADMINISTRATOR . '/components/com_osmembership/sql/menus.osmembership.sql';
		OSMembershipHelper::executeSqlFile($sqlFile);

		$sqlFile = JPATH_ADMINISTRATOR . '/components/com_osmembership/sql/custommenus.osmembership.sql';

		if (file_exists($sqlFile))
		{
			OSMembershipHelper::executeSqlFile($sqlFile);
		}

		$config             = OSMembershipHelper::getConfig();
		$possibleNewConfigs = require JPATH_ADMINISTRATOR . '/components/com_osmembership/updates/configs.php';

		foreach ($possibleNewConfigs as $key => $value)
		{
			if (isset($config->{$key}))
			{
				continue;
			}

			$query->clear()
				->insert('#__osmembership_configs')
				->columns($db->quoteName(['config_key', 'config_value']))
				->values(implode(',', $db->quote([$key, $value])));
			$db->setQuery($query)
				->execute();
		}

		$message             = OSMembershipHelper::getMessages();
		$possibleNewMessages = require JPATH_ADMINISTRATOR . '/components/com_osmembership/updates/messages.php';
		$query               = $db->getQuery(true);

		foreach ($possibleNewMessages as $key => $value)
		{
			if (isset($message->{$key}))
			{
				continue;
			}

			$query->clear()
				->insert('#__osmembership_messages')
				->columns($db->quoteName(['id', 'message_key', 'message']))
				->values(implode(',', $db->quote([0, $key, $value])));
			$db->setQuery($query)
				->execute();
		}

		// Message items
		$query->clear()
			->select('COUNT(*)')
			->from('#__osmembership_mitems');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$sqlFile = JPATH_ADMINISTRATOR . '/components/com_osmembership/sql/mitems.osmembership.sql';
			OSMembershipHelper::executeSqlFile($sqlFile);
		}

		// Insert new message items
		$newItems = require JPATH_ADMINISTRATOR . '/components/com_osmembership/updates/mitems.php';

		self::insertMessageItems($newItems);

		// Insert custom message items if available
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_osmembership/updates/custom.mitems.php'))
		{
			$customItems = require JPATH_ADMINISTRATOR . '/components/com_osmembership/updates/custom.mitems.php';

			self::insertMessageItems($customItems);
		}

		// Make the two message items translatable
		$query->clear()
			->update('#__osmembership_mitems')
			->set('translatable = 1')
			->whereIn(
				'name',
				['offline_payment_reminder_email_subject', 'offline_payment_reminder_email_body'],
				ParameterType::STRING
			);
		$db->setQuery($query)
			->execute();

		$query->clear()
			->select('COUNT(*)')
			->from('#__osmembership_taxes');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$query->clear()
				->select('id, tax_rate')
				->from('#__osmembership_plans')
				->where('tax_rate > 0');
			$db->setQuery($query);

			foreach ($db->loadObjectList() as $taxRate)
			{
				$query->clear()
					->insert('#__osmembership_taxes')
					->columns($db->quoteName(['plan_id', 'country', 'rate', 'vies', 'published']))
					->values(implode($db->quote([$taxRate->id, '', $taxRate->tax_rate, 0, 1])));
				$db->setQuery($query)
					->execute();
			}

			$query->clear()
				->update('#__osmembership_plans')
				->set('tax_rate = 0');
			$db->setQuery($query)
				->execute();
		}

		// Move coupons data to new structure
		$query->clear()
			->select('COUNT(*)')
			->from('#__osmembership_coupon_plans');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			//Need to migrate data here
			$sql = 'INSERT INTO #__osmembership_coupon_plans(coupon_id, plan_id)
                SELECT id, plan_id FROM #__osmembership_coupons WHERE plan_id > 0
                ';
			$db->setQuery($sql)
				->execute();

			$query->clear()
				->update('#__osmembership_coupons')
				->set('plan_id = 1')
				->where('plan_id > 0');
			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Enable the plugins which are needed for the extension to work properly
	 *
	 * @return void
	 */
	public static function enableRequiredPlugin($type = '')
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		$publishedItems = [
			'system'    => [
				'osmembershipupdatestatus',
				'membershippro',
			],
			'installer' => [
				'membershippro',
			],
		];

		// Only enable osmembershipreminder on new installation
		if ($type !== 'update')
		{
			$publishedItems['system'][] = 'osmembershipreminder';
		}

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
	 * Create necessary indexes
	 *
	 * @return void
	 */
	public static function createIndexes()
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db  = Factory::getContainer()->get('db');
		$sql = 'SHOW INDEX FROM #__osmembership_subscribers';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		foreach ($rows as $row)
		{
			$fields[] = $row->Column_name;
		}

		$indexFields = [
			'plan_id',
			'user_id',
			'is_profile',
			'created_date',
			'from_date',
			'to_date',
			'email',
			'published',
			'first_name',
			'last_name',
			'transaction_id',
			'payment_method',
			'act',
			'active_event_triggered',
		];

		foreach ($indexFields as $indexField)
		{
			if (in_array($indexField, $fields))
			{
				continue;
			}

			$sql = "CREATE INDEX `idx_{$indexField}` ON `#__osmembership_subscribers` (`{$indexField}`);";
			$db->setQuery($sql)
				->execute();
		}
	}

	/**
	 * Get installed version of the component
	 *
	 * @return void
	 */
	private function getInstalledVersion()
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('manifest_cache')
			->from('#__extensions')
			->where($db->quoteName('element') . ' = ' . $db->quote('com_osmembership'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'));
		$db->setQuery($query);
		$manifestCache = $db->loadResult();

		if ($manifestCache)
		{
			$manifest               = json_decode($manifestCache);
			$this->installedVersion = $manifest->version;
		}
	}

	private function convertDBTablesToUtf8mb4()
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');

		$tablesToConvert = [
			'#__osmembership_categories',
			'#__osmembership_plans',
			'#__osmembership_messages',
			'#__osmembership_fields',
			'#__osmembership_subscribers',
			'#__osmembership_field_value',
			'#__osmembership_mmtemplates',
			'#__osmembership_emails',
		];

		foreach ($tablesToConvert as $table)
		{
			// Convert table
			$sql = "ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
			$db->setQuery($sql)
				->execute();
		}
	}

	/**
	 * Insert message items
	 *
	 * @param   array  $items
	 */
	public static function insertMessageItems($items)
	{
		if (!count($items))
		{
			return;
		}

		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('name')
			->from('#__osmembership_mitems');
		$db->setQuery($query);
		$existingItems = $db->loadColumn();

		$query->clear()
			->insert('#__osmembership_mitems')
			->columns($db->quoteName(['name', 'title', 'title_en', 'type', 'group', 'translatable', 'featured']));

		$count = 0;

		foreach ($items as $item)
		{
			if (in_array($item['name'], $existingItems))
			{
				continue;
			}

			$count++;

			$name         = $item['name'];
			$title        = $item['title'];
			$titleEn      = $item['title_en'] ?? '';
			$type         = $item['type'];
			$group        = $item['group'] ?? '';
			$translatable = $item['translatable'] ?? 0;
			$featured     = $item['featured'] ?? 0;

			$query->values(
				implode(',', $db->quote([$name, $title, $titleEn, $type, $group, $translatable, $featured]))
			);
		}

		if ($count)
		{
			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Update wrong message items type
	 *
	 * @return void
	 */
	private function correctMessageItemsType()
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		// Update some messages to use editor during wrong installation data
		$editorMessages = [
			'subscription_end_email_body',
			'mass_mail_template',
			'offline_recurring_email_body',
			'user_upgrade_email_body_offline',
			'upgrade_thanks_message_offline',
		];

		$query->clear()
			->update('#__osmembership_mitems')
			->set($db->quoteName('type') . '=' . $db->quote('editor'))
			->whereIn('name', $editorMessages, ParameterType::STRING);
		$db->setQuery($query)
			->execute();

		// Update some messages to use textarea during wrong installation data
		$editorMessages = [
			'new_subscription_admin_sms',
			'new_subscription_renewal_admin_sms',
			'new_subscription_upgrade_admin_sms',
			'first_reminder_sms',
			'second_reminder_sms',
			'third_reminder_sms',
		];

		$query->clear()
			->update('#__osmembership_mitems')
			->set($db->quoteName('type') . '=' . $db->quote('textarea'))
			->whereIn('name', $editorMessages, ParameterType::STRING);
		$db->setQuery($query)
			->execute();
	}

	/**
	 * Remove .git hidden folders from omnipay library
	 *
	 * @return void
	 */
	public static function removeHiddenFolders(): void
	{
		$folders = [
			'libraries/omnipay3/vendor/dioscouri/omnipay-cybersource/.git',
			'libraries/omnipay3/vendor/omnipay/worldpay/.git',
			'libraries/omnipay3/vendor/swisnl/omnipay-redsys/.git',
			'libraries/omnipay3/vendor/zburke/omnipay-bluepay/.git',
		];

		foreach ($folders as $hiddenFolder)
		{
			if (is_dir(JPATH_ROOT . '/' . $hiddenFolder))
			{
				Folder::delete(JPATH_ROOT . '/' . $hiddenFolder);
			}
		}
	}
}
