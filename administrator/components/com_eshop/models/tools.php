<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;

/**
 * EShop Component Tools Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelTools extends BaseDatabaseModel
{

	/**
	 *
	 * Migrate subscribers from Membership Pro into Eshop Customers
	 */
	public function migrateFromMembershipPro()
	{
		require_once JPATH_ROOT . '/components/com_osmembership/helper/helper.php';
		require_once JPATH_ROOT . '/components/com_eshop/helpers/helper.php';
		require_once JPATH_ROOT . '/components/com_eshop/helpers/api.php';
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_subscribers')
			->where('is_profile=1')
			->where('user_id > 0');
		$db->setQuery($query);
		$rows                   = $db->loadObjectList();
		$fieldsMapping          = [
			'first_name'   => 'firstname',
			'last_name'    => 'lastname',
			'organization' => 'company',
			'address'      => 'address_1',
			'address2'     => 'address_2',
			'phone'        => 'telephone',
			'zip'          => 'postcode',
			'fax'          => 'fax',
			'city'         => 'city',
			'email'        => 'email',
		];
		$defaultCountry         = OSMembershipHelper::getConfigValue('default_country');
		$defaultCustomerGroupId = (int) EShopHelper::getConfigValue('customergroup_id');
		$countryCodes           = [];
		if (count($rows))
		{
			foreach ($rows as $row)
			{
				if (EShopAPI::customerExist($row->user_id))
				{
					continue;
				}
				$data    = [];
				$country = $row->country ?: $defaultCountry;
				if (!isset($countryCodes[$country]))
				{
					$query->clear();
					$query->select('iso_code_3')
						->from('#__eshop_countries')
						->where('country_name=' . $db->quote($country));
					$db->setQuery($query);
					$countryCodes[$country] = $db->loadResult();
				}
				$data['country_code'] = $countryCodes[$country];
				foreach ($fieldsMapping as $membershipProField => $eshopField)
				{
					if ($row->{$membershipProField})
					{
						$data[$eshopField] = $row->{$membershipProField};
					}
				}
				if ($row->state)
				{
					$query->clear();
					$query->select('state_3_code')
						->from('#__osmembership_states AS a')
						->innerJoin('#__osmembership_countries AS b ON a.country_id=b.country_id')
						->where('a.state_name=' . $db->quote($row->state))
						->where('b.name=' . $db->quote($country));
					$db->setQuery($query);
					$data['zone_code'] = $db->loadResult();
				}
				$customerGroupId = $defaultCustomerGroupId;
				//Customer groups based on active plans
				$activePlans = OSMembershipHelper::getActiveMembershipPlans($row->user_id);
				if (count($activePlans) > 1)
				{
					$query->clear();
					$query->select('params')
						->from('#__osmembership_plans')
						->where('id IN  (' . implode(',', $activePlans) . ')')
						->order('price DESC');
					$db->setQuery($query);
					$rowPlans = $db->loadObjectList();
					if (count($rowPlans))
					{
						foreach ($rowPlans as $rowPlan)
						{
							$planParams          = new Registry($rowPlan->params);
							$planCustomerGroupId = (int) $planParams->get('eshop_customer_group_id');
							if ($planCustomerGroupId)
							{
								$customerGroupId = $planCustomerGroupId;
								break;
							}
						}
					}
				}
				$data['customergroup_id'] = $customerGroupId;
				EShopAPI::addCustomer($row->user_id, $data);
			}
		}
	}

	/**
	 *
	 * Migrate users from Joomla into Eshop Customers
	 */
	public function migrateFromJoomla()
	{
		require_once JPATH_ROOT . '/components/com_eshop/helpers/helper.php';
		require_once JPATH_ROOT . '/components/com_eshop/helpers/api.php';
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__users');
		$db->setQuery($query);
		$rows           = $db->loadObjectList();
		$profileEnabled = PluginHelper::isEnabled('user', 'profile');
		foreach ($rows as $row)
		{
			if (EShopAPI::customerExist($row->id))
			{
				continue;
			}
			$data = [];
			$name = $row->name;
			$pos  = strpos($name, ' ');
			if ($pos !== false)
			{
				$data['firstname'] = substr($name, 0, $pos);
				$data['lastname']  = substr($name, $pos + 1);
			}
			else
			{
				$data['firstname'] = $name;
				$data['lastname']  = '';
			}
			$data['email'] = $row->email;
			if ($profileEnabled)
			{
				$profile           = UserHelper::getProfile($row->id);
				$data['address_1'] = $profile->profile['address1'];
				$data['address_2'] = $profile->profile['address2'];
				$data['city']      = $profile->profile['city'];
				$country           = $profile->profile['country'];
				if ($country)
				{
					$query = $db->getQuery(true);
					$query->select('iso_code_3')
						->from('#__eshop_countries')
						->where('country_name=' . $db->quote($country));
					$db->setQuery($query);
					$data['country_code'] = $db->loadResult();

					if ($data['country_code'] != '')
					{
						$region = $profile->profile['region'];
						if ($region)
						{
							$query->clear();
							$query->select('z.zone_code')
								->from('#__eshop_zones AS z')
								->innerJoin('#__eshop_countries AS c ON (z.country_id = c.id)')
								->where('c.iso_code_3 = ' . $db->quote($data['country_code']))
								->where('z.zone_name = ' . $db->quote($region));
							$db->setQuery($query);
							$data['zone_code'] = $db->loadResult();
						}
					}
				}

				$data['postcode']  = $profile->profile['postal_code'];
				$data['telephone'] = $profile->profile['phone'];
			}
			EShopAPI::addCustomer($row->id, $data);
		}
	}

	/**
	 *
	 * Function to clean data
	 */
	public function cleanData()
	{
		$cleanSql = JPATH_ADMINISTRATOR . '/components/com_eshop/sql/clean.eshop.sql';
		EShopHelper::executeSqlFile($cleanSql);
	}

	/**
	 *
	 * Function to add sample data
	 */
	public function addSampleData()
	{
		// Clean data first
		$cleanSql = JPATH_ADMINISTRATOR . '/components/com_eshop/sql/clean.eshop.sql';
		EShopHelper::executeSqlFile($cleanSql);

		// Then add sample data
		$sampleSql = JPATH_ADMINISTRATOR . '/components/com_eshop/sql/sample.eshop.sql';
		EShopHelper::executeSqlFile($sampleSql);
	}

	/**
	 *
	 * Function to synchronize data
	 */
	public function synchronizeData()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('element')
			->from('#__extensions')
			->where('type = "language"')
			->where('client_id = 0');
		$db->setQuery($query);
		$langCodes = $db->loadColumn();
		if (count($langCodes))
		{
			foreach ($langCodes as $currentLangCode)
			{
				foreach ($langCodes as $langCode)
				{
					if ($langCode != $currentLangCode)
					{
						$sql = 'INSERT INTO #__eshop_attributedetails (attribute_id, attribute_name, language)' .
							' SELECT attribute_id, attribute_name, "' . $langCode . '"' .
							' FROM #__eshop_attributedetails WHERE (language = "' . $currentLangCode . '") AND attribute_id NOT IN (select attribute_id FROM #__eshop_attributedetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_attributegroupdetails (attributegroup_id, attributegroup_name, language)' .
							' SELECT attributegroup_id, attributegroup_name, "' . $langCode . '"' .
							' FROM #__eshop_attributegroupdetails WHERE (language = "' . $currentLangCode . '") AND attributegroup_id NOT IN (select attributegroup_id FROM #__eshop_attributegroupdetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_categorydetails (category_id, category_name, category_alias, category_desc, meta_key, meta_desc, language)' .
							' SELECT category_id, category_name, category_alias, category_desc, meta_key, meta_desc, "' . $langCode . '"' .
							' FROM #__eshop_categorydetails WHERE (language = "' . $currentLangCode . '") AND category_id NOT IN (select category_id FROM #__eshop_categorydetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_customergroupdetails (customergroup_id, customergroup_name, language)' .
							' SELECT customergroup_id, customergroup_name, "' . $langCode . '"' .
							' FROM #__eshop_customergroupdetails WHERE (language = "' . $currentLangCode . '") AND customergroup_id NOT IN (select customergroup_id FROM #__eshop_customergroupdetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_downloaddetails (download_id, download_name, language)' .
							' SELECT download_id, download_name, "' . $langCode . '"' .
							' FROM #__eshop_downloaddetails WHERE (language = "' . $currentLangCode . '") AND download_id NOT IN  (select download_id FROM #__eshop_downloaddetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_fielddetails (field_id, title, description, place_holder, language, default_values, `values`, validation_error_message)' .
							' SELECT field_id, title, description, place_holder, "' . $langCode . '", default_values, `values`, validation_error_message' .
							' FROM #__eshop_fielddetails WHERE (language = "' . $currentLangCode . '") AND field_id NOT IN (select field_id FROM #__eshop_fielddetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_labeldetails (label_id, label_name, language)' .
							' SELECT label_id, label_name, "' . $langCode . '"' .
							' FROM #__eshop_labeldetails WHERE (language = "' . $currentLangCode . '") AND label_id NOT IN  (select label_id FROM #__eshop_labeldetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_lengthdetails (length_id, length_name, length_unit, language)' .
							' SELECT length_id, length_name, length_unit, "' . $langCode . '"' .
							' FROM #__eshop_lengthdetails WHERE (language = "' . $currentLangCode . '") AND length_id NOT IN (select length_id FROM #__eshop_lengthdetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_manufacturerdetails (manufacturer_id, manufacturer_name, manufacturer_alias, manufacturer_desc, language)' .
							' SELECT manufacturer_id, manufacturer_name, manufacturer_alias, manufacturer_desc, "' . $langCode . '"' .
							' FROM #__eshop_manufacturerdetails WHERE (language = "' . $currentLangCode . '") AND manufacturer_id NOT IN (select manufacturer_id FROM #__eshop_manufacturerdetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_messagedetails (message_id, message_value, language)' .
							' SELECT message_id, message_value, "' . $langCode . '"' .
							' FROM #__eshop_messagedetails WHERE (language = "' . $currentLangCode . '") AND message_id NOT IN (select message_id FROM #__eshop_messagedetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_optiondetails (option_id, option_name, option_desc, language)' .
							' SELECT option_id, option_name, option_desc, "' . $langCode . '"' .
							' FROM #__eshop_optiondetails WHERE (language = "' . $currentLangCode . '") AND option_id NOT IN (select option_id FROM #__eshop_optiondetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_optionvaluedetails (optionvalue_id, option_id, value, language)' .
							' SELECT optionvalue_id, option_id, value, "' . $langCode . '"' .
							' FROM #__eshop_optionvaluedetails WHERE (language = "' . $currentLangCode . '") AND optionvalue_id NOT IN (select optionvalue_id FROM #__eshop_optionvaluedetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_orderstatusdetails (orderstatus_id, orderstatus_name, language)' .
							' SELECT orderstatus_id, orderstatus_name, "' . $langCode . '"' .
							' FROM #__eshop_orderstatusdetails WHERE (language = "' . $currentLangCode . '") AND orderstatus_id NOT IN (select orderstatus_id FROM #__eshop_orderstatusdetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_productattributedetails (productattribute_id, product_id, value, language)' .
							' SELECT productattribute_id, product_id, value, "' . $langCode . '"' .
							' FROM #__eshop_productattributedetails WHERE (language = "' . $currentLangCode . '") AND productattribute_id NOT IN (select productattribute_id FROM #__eshop_productattributedetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_productdetails (product_id, product_name, product_alias, product_desc, product_short_desc, meta_key, meta_desc, language)' .
							' SELECT product_id, product_name, product_alias, product_desc, product_short_desc, meta_key, meta_desc, "' . $langCode . '"' .
							' FROM #__eshop_productdetails WHERE (language = "' . $currentLangCode . '") AND product_id NOT IN (select product_id FROM #__eshop_productdetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_stockstatusdetails (stockstatus_id, stockstatus_name, language)' .
							' SELECT stockstatus_id, stockstatus_name, "' . $langCode . '"' .
							' FROM #__eshop_stockstatusdetails WHERE (language = "' . $currentLangCode . '") AND stockstatus_id NOT IN (select stockstatus_id FROM #__eshop_stockstatusdetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_weightdetails (weight_id, weight_name, weight_unit, language)' .
							' SELECT weight_id, weight_name, weight_unit, "' . $langCode . '"' .
							' FROM #__eshop_weightdetails WHERE (language = "' . $currentLangCode . '") AND weight_id NOT IN (select weight_id FROM #__eshop_weightdetails WHERE language = "' . $langCode . '")';
						$db->setQuery($sql);
						$db->execute();
					}
				}
			}
		}
	}

	/**
	 *
	 * Function to migrate data from Virtuemart to EShop
	 */
	public function migrateVirtuemart()
	{
		if (!is_dir(JPATH_ROOT . '/components/com_virtuemart'))
		{
			$mainframe = Factory::getApplication();
			$mainframe->enqueueMessage(Text::_('ESHOP_MIGRATE_VIRTUEMART_NOT_EXISTED'), 'error');
			$mainframe->redirect('index.php?option=com_eshop&view=dashboard');
		}
		else
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__languages')
				->where('published = 1');
			$db->setQuery($query);
			$languages = $db->loadObjectList();

			// VM categories
			$query->clear()
				->select('*')
				->from('#__virtuemart_categories');
			$db->setQuery($query);
			$categories = $db->loadObjectList('virtuemart_category_id');

			// VM parent categories
			$query->clear()
				->select('id, category_parent_id')
				->from('#__virtuemart_category_categories');
			$db->setQuery($query);
			$parentCategories = $db->loadAssocList('id', 'category_parent_id');

			// VM image categories
			$query->clear()
				->select('a.virtuemart_category_id')
				->from('#__virtuemart_category_medias AS a')
				->select('b.file_url')
				->leftJoin('#__virtuemart_medias AS b ON a.virtuemart_media_id = b.virtuemart_media_id');
			$db->setQuery($query);
			$categoryImages = $db->loadAssocList('virtuemart_category_id', 'file_url');

			// Migrate categories
			$mappingCategories  = [];
			$categoryImagesPath = JPATH_ROOT . '/media/com_eshop/categories/';
			foreach ($categories as $category)
			{
				$row = new EShopTable('#__eshop_categories', 'id', $db);
				// Upload image category
				if (isset($categoryImages[$category->virtuemart_category_id]) && $categoryImages[$category->virtuemart_category_id] != '')
				{
					$categoryImage = pathinfo($categoryImages[$category->virtuemart_category_id]);
					$imageFileName = File::makeSafe($categoryImage['basename']);
					if (is_file($categoryImagesPath . $categoryImage['basename']))
					{
						$imageFileName = uniqid('image_') . '_' . File::makeSafe($categoryImage['basename']);
					}
					if (is_file(JPATH_ROOT . '/' . $categoryImages[$category->virtuemart_category_id]))
					{
						$rel = File::copy(
							JPATH_ROOT . '/' . $categoryImages[$category->virtuemart_category_id],
							$categoryImagesPath . $imageFileName
						);
						if ($rel)
						{
							$row->category_image = $imageFileName;
						}
					}
				}
				// Assign data
				$row->category_parent_id = 0;
				$row->products_per_page  = 15;
				$row->products_per_row   = $category->products_per_row > 0 ? $category->products_per_row : 3;
				$row->published          = $category->published;
				$row->ordering           = $category->ordering;
				$row->hits               = $category->hits;
				$row->created_date       = $category->created_on;
				$row->created_by         = $category->created_by;
				$row->modified_date      = $category->modified_on;
				$row->modified_by        = $category->modified_by;
				$row->checked_out        = $category->locked_by;
				$row->checked_out_time   = $category->locked_on;
				if ($row->store())
				{
					$mappingCategories[$category->virtuemart_category_id] = $row->id;
				}
			}

			// Update parent catogory
			foreach ($mappingCategories as $virtuemart_category_id => $eshopCatId)
			{
				if (!$parentCategories[$virtuemart_category_id])
				{
					continue;
				}
				$row = new EShopTable('#__eshop_categories', 'id', $db);
				$row->load($eshopCatId);
				$row->category_parent_id = $mappingCategories[$parentCategories[$virtuemart_category_id]];
				$row->store();
			}

			// Eshop category details
			foreach ($languages as $language)
			{
				$search = 'virtuemart_categories_' . strtolower(str_replace('-', '_', $language->lang_code));
				$search = $db->quote('%' . trim($search) . '%');
				$db->setQuery("SHOW TABLES LIKE $search");
				$categoryDetailsTables = $db->loadResult();
				if ($categoryDetailsTables != '')
				{
					$query->clear()
						->select('*')
						->from($categoryDetailsTables);
					$db->setQuery($query);
					$categoriesData = $db->loadObjectList('virtuemart_category_id');
					foreach ($categoriesData as $categoryData)
					{
						if ($mappingCategories[$categoryData->virtuemart_category_id])
						{
							$row                = new EShopTable('#__eshop_categorydetails', 'id', $db);
							$row->category_id   = $mappingCategories[$categoryData->virtuemart_category_id];
							$row->category_name = $categoryData->category_name;
							if (empty($categoryData->slug))
							{
								$row->category_alias = ApplicationHelper::stringUrlSafe($row->category_name);
							}
							else
							{
								$row->category_alias = $categoryData->slug;
							}
							$row->category_desc = $categoryData->category_description;
							$row->meta_key      = $categoryData->metakey;
							$row->meta_desc     = $categoryData->metadesc;
							$row->language      = trim($language->lang_code);
							$row->store();
						}
					}
				}
			}

			// VM manufacturers
			$query->clear()
				->select('*')
				->from('#__virtuemart_manufacturers');
			$db->setQuery($query);
			$manufactures = $db->loadObjectList('virtuemart_manufacturer_id');

			// VM image manufacturers
			$query->clear()
				->select('a.virtuemart_manufacturer_id')
				->from('#__virtuemart_manufacturer_medias AS a')
				->select('b.file_url')
				->leftJoin('#__virtuemart_medias AS b ON a.virtuemart_media_id=b.virtuemart_media_id');
			$db->setQuery($query);
			$manufacturerImages = $db->loadAssocList('virtuemart_manufacturer_id', 'file_url');

			// Migrate manufacturers
			$mappingManufactures    = [];
			$manufacturerImagesPath = JPATH_ROOT . '/media/com_eshop/manufacturers/';
			foreach ($manufactures as $manufacture)
			{
				$row = new EShopTable('#__eshop_manufacturers', 'id', $db);
				if (isset($manufacturerImages[$manufacture->virtuemart_manufacturer_id]) && $manufacturerImages[$manufacture->virtuemart_manufacturer_id] != '')
				{
					$manufactureImage = pathinfo($manufacturerImages[$manufacture->virtuemart_manufacturer_id]);
					$imageFileName    = File::makeSafe($manufactureImage['basename']);
					if (is_file($manufacturerImagesPath . $manufactureImage['basename']))
					{
						$imageFileName = uniqid('image_') . '_' . File::makeSafe($manufactureImage['basename']);
					}
					if (is_file(JPATH_ROOT . '/' . $manufacturerImages[$manufacture->virtuemart_manufacturer_id]))
					{
						$rel = File::copy(
							JPATH_ROOT . '/' . $manufacturerImages[$manufacture->virtuemart_manufacturer_id],
							$manufacturerImagesPath . $imageFileName
						);
						if ($rel)
						{
							$row->manufacturer_image = $imageFileName;
						}
					}
				}

				// Assign data
				$row->published        = $manufacture->published;
				$row->hits             = $manufacture->hits;
				$row->created_date     = $manufacture->created_on;
				$row->created_by       = $manufacture->created_by;
				$row->modified_date    = $manufacture->modified_on;
				$row->modified_by      = $manufacture->modified_by;
				$row->checked_out      = $manufacture->locked_by;
				$row->checked_out_time = $manufacture->locked_on;
				if ($row->store())
				{
					$mappingManufactures[$manufacture->virtuemart_manufacturer_id] = $row->id;
				}
			}

			// Manufactuer details
			foreach ($languages as $language)
			{
				$search = 'virtuemart_manufacturers_' . strtolower(str_replace('-', '_', $language->lang_code));
				$search = $db->quote('%' . trim($search) . '%');
				$db->setQuery("SHOW TABLES LIKE $search");
				$manufacturerDetailsTables = $db->loadResult();
				if ($manufacturerDetailsTables != '')
				{
					$query->clear()
						->select('*')->from($manufacturerDetailsTables);
					$db->setQuery($query);
					$manufacturersData = $db->loadObjectList('virtuemart_manufacturer_id');
					foreach ($manufacturersData as $manufacturerData)
					{
						if ($mappingManufactures[$manufacturerData->virtuemart_manufacturer_id])
						{
							// Update email and url
							$row = new EShopTable('#__eshop_manufacturers', 'id', $db);
							$row->load($mappingManufactures[$manufacturerData->virtuemart_manufacturer_id]);
							$row->manufacturer_email = $manufacturerData->mf_email;
							$row->manufacturer_url   = $manufacturerData->mf_url;
							$row->store();

							// Manufacturer details
							$row                    = new EShopTable('#__eshop_manufacturerdetails', 'id', $db);
							$row->manufacturer_id   = $mappingManufactures[$manufacturerData->virtuemart_manufacturer_id];
							$row->manufacturer_name = $manufacturerData->mf_name;
							$row->language          = trim($language->lang_code);
							if (empty($manufacturerData->slug))
							{
								$row->manufacturer_alias = ApplicationHelper::stringUrlSafe($row->manufacturer_name);
							}
							else
							{
								$row->manufacturer_alias = $manufacturerData->slug;
							}
							$row->manufacturer_desc = $manufacturerData->mf_desc;
							$row->store();
						}
					}
				}
			}

			// VM products
			$query->clear()
				->select('*')
				->from('#__virtuemart_products');
			$db->setQuery($query);
			$products = $db->loadObjectList('virtuemart_product_id');

			// VM products category
			$query->clear()
				->select('*')
				->from('#__virtuemart_product_categories');
			$db->setQuery($query);
			$productsCategories = $db->loadObjectList();

			// VM product manufacturer
			$query->clear()
				->select('DISTINCT virtuemart_product_id, virtuemart_manufacturer_id')
				->from('#__virtuemart_product_manufacturers');
			$db->setQuery($query);
			$productManufacturer = $db->loadAssocList('virtuemart_product_id', 'virtuemart_manufacturer_id');

			// VM product images
			$query->clear()
				->select('a.virtuemart_product_id')
				->from('#__virtuemart_product_medias AS a')
				->select('b.*')
				->innerJoin('#__virtuemart_medias AS b ON a.virtuemart_media_id=b.virtuemart_media_id');
			$db->setQuery($query);
			$productImages = $db->loadObjectList();

			// upload image
			$mappingProductImages = [];
			$imagesProductPath    = JPATH_ROOT . '/media/com_eshop/products/';
			foreach ($productImages as $image)
			{
				if (!isset($mappingProductImages[$image->virtuemart_product_id]))
				{
					$mappingProductImages[$image->virtuemart_product_id] = [];
				}
				$productImage  = pathinfo($image->file_url);
				$imageFileName = File::makeSafe($productImage['basename']);
				if (is_file($imagesProductPath . $imageFileName))
				{
					$imageFileName = uniqid('image_') . '_' . File::makeSafe($productImage['basename']);
				}
				if (is_file(JPATH_ROOT . '/' . $image->file_url))
				{
					$rel = File::copy(JPATH_ROOT . '/' . $image->file_url, $imagesProductPath . $imageFileName);
					if ($rel)
					{
						$image->image = $imageFileName;
					}
				}
				$mappingProductImages[$image->virtuemart_product_id][] = $image;
			}

			// VM products price
			$query->clear()
				->select('virtuemart_product_id, product_price')
				->from('#__virtuemart_product_prices');
			$db->setQuery($query);
			$productsPrices = $db->loadAssocList('virtuemart_product_id', 'product_price');

			// eshop product, image
			$imagePath       = JPATH_ROOT . '/media/com_eshop/products/';
			$mappingProducts = [];
			foreach ($products as $product)
			{
				// save product and main image
				$row = new EShopTable('#__eshop_products', 'id', $db);
				if (isset($productsPrices[$product->virtuemart_product_id]))
				{
					$product_price          = $productsPrices[$product->virtuemart_product_id];
					$product_call_for_price = 0;
				}
				else
				{
					$product_call_for_price = 1;
					$product_price          = 0;
				}
				$product_minimum_quantity = 0;
				$product_maximum_quantity = 0;
				$product_params           = [];
				if ($product->product_params != '')
				{
					$params = explode('|', $product->product_params);
					foreach ($params as $param)
					{
						if ($param != '')
						{
							[$index, $value] = explode('=', $param);
							$product_params[$index] = substr($value, 1, strlen($value) - 2);
						}
					}
				}
				if (isset($product_params['min_order_level']))
				{
					$product_minimum_quantity = $product_params['min_order_level'];
				}
				if (isset($product_params['max_order_level']))
				{
					$product_maximum_quantity = $product_params['max_order_level'];
				}
				if (isset($mappingManufactures[$productManufacturer[$product->virtuemart_product_id]]))
				{
					$row->manufacturer_id = $mappingManufactures[$productManufacturer[$product->virtuemart_product_id]];
				}
				$row->product_sku              = $product->product_sku;
				$row->product_weight           = $product->product_weight;
				$row->product_weight_id        = 1;
				$row->product_length           = $product->product_length;
				$row->product_width            = $product->product_width;
				$row->product_height           = $product->product_height;
				$row->product_length_id        = 1;
				$row->product_price            = $product_price;
				$row->product_call_for_price   = $product_call_for_price;
				$row->product_taxclass_id      = 0;
				$row->product_quantity         = $product->product_in_stock;
				$row->product_minimum_quantity = $product_minimum_quantity;
				$row->product_maximum_quantity = $product_maximum_quantity;

				if (count($mappingProductImages[$product->virtuemart_product_id]))
				{
					$row->product_image = $mappingProductImages[$product->virtuemart_product_id][0]->image;
				}
				$row->product_available_date = $product->product_available_date;
				$row->product_end_date       = '0000-00-00 00:00:00';
				$row->product_featured       = $product->product_special;
				$row->published              = $product->published;
				$row->ordering               = $product->pordering;
				$row->hits                   = $product->hits;
				$row->created_date           = $product->created_on;
				$row->created_by             = $product->created_by;
				$row->modified_date          = $product->modified_on;
				$row->modified_by            = $product->modified_by;
				$row->checked_out            = $product->locked_by;
				$row->checked_out_time       = $product->locked_on;
				if ($row->store())
				{
					$mappingProducts[$product->virtuemart_product_id] = $row->id;
				}

				if ($row->id)
				{
					unset($mappingProductImages[$product->virtuemart_product_id][0]);
					// save extra image
					foreach ($mappingProductImages[$product->virtuemart_product_id] as $image)
					{
						$row                   = new EShopTable('#__eshop_productimages', 'id', $db);
						$row->id               = 0;
						$row->product_id       = $mappingProducts[$product->virtuemart_product_id];
						$row->image            = $image->image;
						$row->published        = 1;
						$row->ordering         = 1;
						$row->created_date     = $image->created_on;
						$row->created_by       = $image->created_by;
						$row->modified_date    = $image->modified_on;
						$row->modified_by      = $image->modified_by;
						$row->checked_out      = $image->locked_by;
						$row->checked_out_time = $image->locked_on;
						$row->store();
					}
				}
			}

			// Product categories relation
			$main_category = 1;
			foreach ($productsCategories as $products_category)
			{
				$product_id  = $mappingProducts[$products_category->virtuemart_product_id];
				$category_id = $mappingCategories[$products_category->virtuemart_category_id];
				if ($product_id && $category_id)
				{
					$query->clear()
						->insert('#__eshop_productcategories')
						->values("'', $product_id, $category_id, $main_category");
					$db->setQuery($query);
					$db->execute();
					$main_category = 0;
				}
			}

			// Product details
			foreach ($languages as $language)
			{
				$search = 'virtuemart_products_' . strtolower(str_replace('-', '_', $language->lang_code));
				$search = $db->quote('%' . trim($search) . '%');
				$db->setQuery("SHOW TABLES LIKE $search");
				$productTables = $db->loadResult();
				if ($productTables != '')
				{
					$query->clear()
						->select('*')
						->from($productTables);
					$db->setQuery($query);
					$productsData = $db->loadObjectList('virtuemart_product_id');
					foreach ($productsData as $products_data)
					{
						if ($mappingProducts[$products_data->virtuemart_product_id])
						{
							// Save database
							$row               = new EShopTable('#__eshop_productdetails', 'id', $db);
							$row->product_id   = $mappingProducts[$products_data->virtuemart_product_id];
							$row->product_name = $products_data->product_name;
							if (empty($products_data->slug))
							{
								$row->product_alias = ApplicationHelper::stringUrlSafe($row->product_name);
							}
							else
							{
								$row->product_alias = $products_data->slug;
							}
							$row->product_desc       = $products_data->product_desc;
							$row->product_short_desc = $products_data->product_s_desc;
							$row->meta_key           = $products_data->metakey;
							$row->meta_desc          = $products_data->metadesc;
							$row->language           = trim($language->lang_code);
							$row->store();
						}
					}
				}
			}
		}
	}

	/**
	 *
	 * Function to reset hits data
	 */
	public function resetHits()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__eshop_products')
			->set('hits = 0');
		$db->setQuery($query);
		$db->execute();
		$query->update('#__eshop_categories')
			->set('hits = 0');
		$db->setQuery($query);
		$db->execute();
		$query->update('#__eshop_manufacturers')
			->set('hits = 0');
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 *
	 * Function to purge urls
	 */
	public function purgeUrls()
	{
		$db = Factory::getDbo();
		$db->truncateTable('#__eshop_urls');
	}

	/**
	 *
	 * Function to clean orders
	 */
	public function cleanOrders()
	{
		$db = Factory::getDbo();
		$db->truncateTable('#__eshop_orderdownloads');
		$db->truncateTable('#__eshop_orderoptions');
		$db->truncateTable('#__eshop_orderproducts');
		$db->truncateTable('#__eshop_orders');
		$db->truncateTable('#__eshop_ordertotals');
	}
	
	/**
	 *
	 * Function to migrate data from J2Store to EShop
	 */
	public function migrateJ2store()
	{
		if (!is_dir(JPATH_ROOT . '/components/com_j2store'))
		{
			$mainframe = Factory::getApplication();
			$mainframe->enqueueMessage(Text::_('ESHOP_MIGRATE_J2STORE_NOT_EXISTED'), 'error');
			$mainframe->redirect('index.php?option=com_eshop&view=dashboard');
		}
		
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__languages')
			->where('published = 1');
		$db->setQuery($query);
		$languages = $db->loadObjectList();
		$defaultSiteLanguage =  ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		
		//Manufacturer migration
		$query->clear()
			->select('a.j2store_manufacturer_id, b.email, b.company')
			->from('#__j2store_manufacturers AS a')
			->innerJoin('#__j2store_addresses AS b ON a.address_id = b.j2store_address_id');
		$db->setQuery($query);
		$j2StoreManufacturers = $db->loadObjectList();
		
		$mappingManufacturers = [];
		
		foreach ($j2StoreManufacturers as $j2storeManufacturer)
		{
			//Manufacture table
			$row = new EShopTable('#__eshop_manufacturers', 'id', $db);
			
			$row->id					= 0;
			$row->manufacturer_email	= $j2storeManufacturer->email;
			$row->published				= $j2storeManufacturer->enabled;
			$row->ordering				= $j2storeManufacturer->ordering;
			$row->hits					= 0;
			$row->created_date			= gmdate('Y-m-d H:i:s');
			$row->modified_date			= gmdate('Y-m-d H:i:s');
			$row->store();
			
			$eshopManufacturerId = $row->id;
			$mappingManufacturers[$j2storeManufacturer->j2store_manufacturer_id] = $eshopManufacturerId;
			
			foreach ($languages as $language)
			{
				//Manufacturer details table
				$row = new EShopTable('#__eshop_manufacturerdetails', 'id', $db);
				$row->manufacturer_id			= $eshopManufacturerId;
				$row->manufacturer_name			= $j2storeManufacturer->company;
				$row->manufacturer_alias		= ApplicationHelper::stringUrlSafe($row->manufacturer_name);
				$row->manufacturer_page_title	= $j2storeManufacturer->company;
				$row->manufacturer_page_heading	= $j2storeManufacturer->company;
				$row->language					= trim($language->lang_code);
				$row->store();
			}
		}
		
		//Category migration
		$query->clear()
			->select('DISTINCT(a.id), a.*')
			->from('#__categories AS a')
			->innerJoin('#__content AS b ON a.id = b.catid')
			->innerJoin('#__j2store_products AS c ON b.id = c.product_source_id')
			->where('c.product_source = "com_content"');
		$db->setQuery($query);
		$j2storeCategories = $db->loadObjectList();
		
		if (isset($j2storeCategories) && !empty($j2storeCategories))
		{
			$mappingCategories = [];
			
			foreach ($j2storeCategories as $j2storeCategory)
			{
				$params = new Registry($j2storeCategory->params);
				$image = $params->get('image');
				
				$categoryImage = '';
				
				if ($image != '')
				{
					$imagePath = EShopHtmlHelper::getCleanImagePath($image);
					
					if ($imagePath != '')
					{
						$imagePathArr = explode('/', $imagePath);
						
						if (isset($imagePathArr) && !empty($imagePathArr))
						{
							$categoryImage = $imagePathArr[count($imagePathArr) - 1];
							File::copy(JPATH_ROOT . '/' . $imagePath, JPATH_ROOT . '/media/com_eshop/categories/' . $categoryImage);
						}
					}
				}
				
				$row = new EShopTable('#__eshop_categories', 'id', $db);
				$row->id					= 0;
				$row->category_parent_id	= 0;
				$row->category_image 		= $categoryImage;
				$row->category_layout		= 'default';
				$row->published				= $j2storeCategory->published;
				$row->created_date			= gmdate('Y-m-d H:i:s');
				$row->modified_date			= gmdate('Y-m-d H:i:s');
				$row->hits					= $j2storeCategory->hits;
				
				$row->store();
				
				$eshopCategoryId = $row->id;
				$mappingCategories[$j2storeCategory->id] = $eshopCategoryId;
				$hasAssociation = false;
				
				foreach ($languages as $language)
				{
					$langCode = trim($language->lang_code);
					
					if ($j2storeCategory->language == '*' || $j2storeCategory->language == $langCode)
					{
						//Category details table
						$row = new EShopTable('#__eshop_categorydetails', 'id', $db);
						$row->category_id				= $eshopCategoryId;
						$row->category_name				= $j2storeCategory->title;
						$row->category_alias			= $j2storeCategory->alias;
						$row->category_desc				= $j2storeCategory->description;
						$row->category_page_title		= $j2storeCategory->title;
						$row->category_page_heading		= $j2storeCategory->title;
						$row->category_alt_image		= $j2storeCategory->title;
						$row->meta_key					= $j2storeCategory->metakey;
						$row->meta_desc					= $j2storeCategory->metadesc;
						$row->language					= trim($language->lang_code);
						
						$row->store();
						
						if ($j2storeCategory->language == $langCode)
						{
							$hasAssociation = true;
							$mainAssocitionId = $j2storeCategory->id;
							break;
						}
					}
				}
				
				if ($hasAssociation)
				{
					$query->clear()
						->select('`key`')
						->from('#__associations')
						->where('context = "com_categories.item"')
						->where('id = ' . $mainAssocitionId);
					$db->setQuery($query);
					$key = $db->loadResult();
					
					if ($key != '')
					{
						$query->clear()
							->select('a.*')
							->from('#__categories AS a')
							->where('a.id != ' . $mainAssocitionId)
							->where('a.id IN (SELECT id FROM #__associations WHERE `key`="' . $key . '")');
						$db->setQuery($query);
						$associationCategories = $db->loadObjectList();
						
						if (isset($associationCategories) && !empty($associationCategories))
						{
							foreach ($associationCategories AS $associationCategory)
							{
								//Category details table
								$row = new EShopTable('#__eshop_categorydetails', 'id', $db);
								
								$row->category_id				= $eshopCategoryId;
								$row->category_name				= $associationCategory->title;
								$row->category_alias			= $associationCategory->alias;
								$row->category_desc				= $associationCategory->description;
								$row->category_page_title		= $associationCategory->title;
								$row->category_page_heading		= $associationCategory->title;
								$row->category_alt_image		= $associationCategory->title;
								$row->meta_key					= $associationCategory->metakey;
								$row->meta_desc					= $associationCategory->metadesc;
								$row->language					= $associationCategory->language;
								
								$row->store();
							}
						}
					}
				}
			}
			
			//J2Store parent categories
			$query->clear()
				->select('id, parent_id')
				->from('#__categories')
				->where('parent_id NOT IN (0,1)');
			$db->setQuery($query);
			$parentCategories = $db->loadAssocList('id', 'parent_id');
				
			//Update parent catogory
			foreach ($mappingCategories as $j2StoreCategoryId => $eshopCatId)
			{
				if (!$parentCategories[$j2StoreCategoryId])
				{
					continue;
				}
				
				$row = new EShopTable('#__eshop_categories', 'id', $db);
				$row->load($eshopCatId);
				$row->category_parent_id = $mappingCategories[$parentCategories[$j2StoreCategoryId]];
				$row->store();
			}
		}
		
		//Option migration
		$query->clear()
			->select('*')
			->from('#__j2store_options');
		$db->setQuery($query);
		$j2storeOptions = $db->loadObjectList();
		
		if (isset($j2storeOptions) && !empty($j2storeOptions))
		{
			$mappingOptions = [];
			
			foreach ($j2storeOptions as $j2storeOption)
			{
				$row = new EShopTable('#__eshop_options', 'id', $db);
				
				if ($j2storeOption->type == 'time')
				{
					$optionType = 'Datetime';
				}
				else 
				{
					$optionType = ucfirst($j2storeOption->type);
				}
				
				$row->option_type		= $optionType;
				$row->published			= $j2storeOption->enabled;
				$row->ordering			= $j2storeOption->ordering;
				$row->created_date		= gmdate('Y-m-d H:i:s');
				$row->modified_date		= gmdate('Y-m-d H:i:s');
				
				$row->store();
				
				$eshopOptionId = $row->id;
				$mappingOptions[$j2storeOption->j2store_option_id] = $eshopOptionId;
				
				//Option details
				foreach ($languages as $language)
				{
					$langCode = trim($language->lang_code);
					$row = new EShopTable('#__eshop_optiondetails', 'id', $db);
					
					$row->option_id		= $eshopOptionId;
					$row->option_name	= $j2storeOption->option_name;
					$row->language		= trim($language->lang_code);
					
					$row->store();
				}
				
				//Option values
				$query->clear()
					->select('*')
					->from('#__j2store_optionvalues')
					->where('option_id = ' . $j2storeOption->j2store_option_id);
				$db->setQuery($query);
				$j2storeOptionValues = $db->loadObjectList();
				
				if (isset($j2storeOptionValues) && !empty($j2storeOptionValues))
				{
					$mappingOptionValues[$j2storeOption->j2store_option_id] = [];
					
					foreach ($j2storeOptionValues as $j2storeOptionValue)
					{
						$row = new EShopTable('#__eshop_optionvalues', 'id', $db);
						
						$row->option_id = $eshopOptionId;
						$row->published = 1;
						$row->ordering	= $j2storeOptionValue->ordering;
						
						$row->store();
						
						$eshopOptionValueId = $row->id;
						$mappingOptionValues[$j2storeOption->j2store_option_id][$j2storeOptionValue->j2store_optionvalue_id] = $eshopOptionValueId;
						
						//Option value details
						foreach ($languages as $language)
						{
							$langCode = trim($language->lang_code);
							$row = new EShopTable('#__eshop_optionvaluedetails', 'id', $db);
								
							$row->optionvalue_id	= $eshopOptionValueId;
							$row->option_id			= $eshopOptionId;
							$row->value				= $j2storeOptionValue->optionvalue_name;
							$row->language			= trim($language->lang_code);
								
							$row->store();
						}
					}
				}
			}
		}
		
		//Product migration
		$query->clear()
			->select('a.*, b.*, c.*, d.quantity')
			->from('#__j2store_products AS a')
			->innerJoin('#__content AS b ON a.product_source_id = b.id')
			->innerJoin('#__j2store_variants AS c ON a.j2store_product_id = c.product_id')
			->leftJoin('#__j2store_productquantities AS d ON c.j2store_variant_id = d.variant_id')
			->where('product_source = "com_content"');
		$db->setQuery($query);
		$j2storeProducts = $db->loadObjectList();
		
		if (isset($j2storeProducts) && !empty($j2storeProducts))
		{
			$mappingProducts = [];
			
			foreach ($j2storeProducts as $j2storeProduct)
			{
				$j2StoreProduct = [];
				
				//Prepare product images
				$query->clear()
					->select('main_image, additional_images')
					->from('#__j2store_productimages')
					->where('product_id = ' . $j2storeProduct->j2store_product_id);
				$db->setQuery($query);
				$imageRow = $db->loadObject();
				
				$productImage = '';
				$additionalImages = '';
				
				if (is_object($imageRow))
				{
					//Main image first
					$mainImage = $imageRow->main_image;
					
					if ($mainImage != '')
					{
						$imagePath = EShopHtmlHelper::getCleanImagePath($mainImage);
							
						if ($imagePath != '')
						{
							$imagePathArr = explode('/', $imagePath);
					
							if (isset($imagePathArr) && !empty($imagePathArr))
							{
								$productImage = $imagePathArr[count($imagePathArr) - 1];
								File::copy(JPATH_ROOT . '/' . $imagePath, JPATH_ROOT . '/media/com_eshop/products/' . $productImage);
							}
						}
					}
					
					//Then, get additional images data to process after importing product
					if (isset($imageRow->additional_images) && !empty($imageRow->additional_images))
					{
						$additionalImages = json_decode($imageRow->additional_images);
					}
				}
				
				
				$j2StoreProduct['main_category_id']					= $mappingCategories[$j2storeProduct->catid];
				$j2StoreProduct['manufacturer_id']					= $mappingManufacturers[$j2storeProduct->manufacturer_id];
				$j2StoreProduct['product_sku']						= $j2storeProduct->sku;
				$j2StoreProduct['product_weight']					= $j2storeProduct->weight;
				$j2StoreProduct['product_weight_id']				= $j2storeProduct->weight_class_id;
				$j2StoreProduct['product_length']					= $j2storeProduct->length;
				$j2StoreProduct['product_width']					= $j2storeProduct->width;
				$j2StoreProduct['product_height']					= $j2storeProduct->height;
				$j2StoreProduct['product_length_id']				= $j2storeProduct->length_class_id;
				$j2StoreProduct['product_cost']						= 0;
				$j2StoreProduct['product_price']					= $j2storeProduct->price;
				$j2StoreProduct['product_call_for_price']			= 0;
				$j2StoreProduct['product_taxclass_id']				= 0;
				$j2StoreProduct['product_manage_stock']				= $j2storeProduct->manage_stock;
				$j2StoreProduct['product_stock_display']			= 0;
				$j2StoreProduct['product_show_availability']		= 0;
				$j2StoreProduct['product_stock_warning']			= 0;
				$j2StoreProduct['product_inventory_global']			= 0;
				$j2StoreProduct['product_quantity']					= $j2storeProduct->quantity;
				$j2StoreProduct['product_threshold']				= $j2storeProduct->notify_qty;
				$j2StoreProduct['product_threshold_notify']			= 1;
				$j2StoreProduct['product_stock_checkout']			= 0;
				$j2StoreProduct['product_minimum_quantity']			= $j2storeProduct->min_sale_qty;
				$j2StoreProduct['product_maximum_quantity']			= $j2storeProduct->max_sale_qty;
				$j2StoreProduct['product_shipping']					= $j2storeProduct->shipping;
				$j2StoreProduct['product_shipping_cost']			= 0;
				$j2StoreProduct['product_shipping_cost_geozones']	= '';
				$j2StoreProduct['product_image']					= $productImage;
				$j2StoreProduct['product_available_date']			= '';
				$j2StoreProduct['product_end_date']					= '';
				$j2StoreProduct['product_featured']					= 0;
				$j2StoreProduct['product_customergroups']			= '';
				$j2StoreProduct['product_stock_status_id']			= 0;
				$j2StoreProduct['product_cart_mode']				= 'public';
				$j2StoreProduct['product_quote_mode']				= 0;
				$j2StoreProduct['product_languages']				= '';
				$j2StoreProduct['custom_fields']					= '';
				$j2StoreProduct['published']						= $j2storeProduct->enabled;
				$j2StoreProduct['ordering']							= $j2storeProduct->ordering;
				$j2StoreProduct['hits']								= $j2storeProduct->hits;
				$j2StoreProduct['created_date']						= gmdate('Y-m-d H:i:s');
				$j2StoreProduct['modified_date']					= gmdate('Y-m-d H:i:s');
				
				$row = new EShopTable('#__eshop_products', 'id', $db);
				$row->bind($j2StoreProduct);
				$row->store();
				
				$eshopProductId = $row->id;
				$mappingProducts[$j2storeProduct->j2store_product_id] = $eshopProductId;
				
				//Product categories
				$row = new EShopTable('#__eshop_productcategories', 'id', $db);
				
				$row->product_id	= $eshopProductId;
				$row->category_id	= $mappingCategories[$j2storeProduct->catid];
				$row->main_category	= 1;
				
				$row->store();
				
				//Process product additional images
				if (isset($additionalImages) && !empty($additionalImages))
				{
					$additionalImageOrdering = 1;
					
					foreach ($additionalImages as $key => $additionalImage)
					{
						if ($additionalImage != '')
						{
							$additionalImagePath = EShopHtmlHelper::getCleanImagePath($additionalImage);
								
							if ($additionalImagePath != '')
							{
								$additionalImagePathArr = explode('/', $additionalImagePath);
				
								if (isset($additionalImagePathArr) && !empty($additionalImagePathArr))
								{
									$additionalProductImage = $additionalImagePathArr[count($additionalImagePathArr) - 1];
									
									if ($additionalProductImage != '')
									{
										File::copy(JPATH_ROOT . '/' . $additionalImagePath, JPATH_ROOT . '/media/com_eshop/products/' . $additionalProductImage);
											
										$row					= new EShopTable('#__eshop_productimages', 'id', $db);
										$row->id				= 0;
										$row->product_id		= $eshopProductId;
										$row->image				= $additionalProductImage;
										$row->published			= 1;
										$row->ordering			= $additionalImageOrdering;
										$row->created_date		= gmdate('Y-m-d H:i:s');
										$row->modified_date		= gmdate('Y-m-d H:i:s');
											
										$row->store();
										$additionalImageOrdering++;
									}
								}
							}
						}
					}
				}
				
				//Product details
				$hasAssociation = false;
				
				foreach ($languages as $language)
				{
					$langCode = trim($language->lang_code);
						
					if ($j2storeProduct->language == '*' || $j2storeProduct->language == $langCode)
					{
						//Product details table
						$row = new EShopTable('#__eshop_productdetails', 'id', $db);
						
						$row->product_id				= $eshopProductId;
						$row->product_name				= $j2storeProduct->title;
						$row->product_alias				= $j2storeProduct->alias;
						$row->product_desc				= $j2storeProduct->fulltext;
						$row->product_short_desc		= $j2storeProduct->introtext;
						$row->product_page_title		= $j2storeProduct->title;
						$row->product_page_heading		= $j2storeProduct->title;
						$row->product_alt_image			= $j2storeProduct->title;
						$row->meta_key					= $j2storeProduct->metakey;
						$row->meta_desc					= $j2storeProduct->metadesc;
						$row->language					= trim($language->lang_code);
				
						$row->store();
				
						if ($j2storeProduct->language == $langCode)
						{
							$hasAssociation = true;
							$mainAssocitionId = $j2storeProduct->id;
							break;
						}
					}
				}
				
				if ($hasAssociation)
				{
					$query->clear()
						->select('`key`')
						->from('#__associations')
						->where('context = "com_content.item"')
						->where('id = ' . $mainAssocitionId);
					$db->setQuery($query);
					$key = $db->loadResult();
						
					if ($key != '')
					{
						$query->clear()
							->select('a.*')
							->from('#__content AS a')
							->where('a.id != ' . $mainAssocitionId)
							->where('a.id IN (SELECT id FROM #__associations WHERE `key`="' . $key . '")');
						$db->setQuery($query);
						$associationProducts = $db->loadObjectList();
				
						if (isset($associationProducts) && !empty($associationProducts))
						{
							foreach ($associationProducts AS $associationProduct)
							{
								//Category details table
								$row = new EShopTable('#__eshop_categorydetails', 'id', $db);
								
								$row->product_id				= $eshopProductId;
								$row->product_name				= $associationProduct->title;
								$row->product_alias				= $associationProduct->alias;
								$row->product_desc				= $associationProduct->fulltext;
								$row->product_short_desc		= $associationProduct->introtext;
								$row->product_page_title		= $associationProduct->title;
								$row->product_page_heading		= $associationProduct->title;
								$row->product_alt_image			= $associationProduct->title;
								$row->meta_key					= $associationProduct->metakey;
								$row->meta_desc					= $associationProduct->metadesc;
								$row->language					= $associationProduct->language;
				
								$row->store();
							}
						}
					}
				}
				
				//Product files
				$query->clear()
					->select('*')
					->from('#__j2store_productfiles')
					->where('product_id = ' . $j2storeProduct->j2store_product_id);
				$db->setQuery($query);
				$j2storeProductFiles = $db->loadObjectList();
				
				if (isset($j2storeProductFiles) && !empty($j2storeProductFiles))
				{
					$query->clear()
						->select('config_meta_value')
						->from('#__j2store_configurations')
						->where('config_meta_key = "attachmentfolderpath"');
					$db->setQuery($query);
					$folderPath = $db->loadResult();
					
					foreach ($j2storeProductFiles as $j2storeProductFile)
					{
						$filename = $j2storeProductFile->product_file_save_name;
						File::copy(JPATH_ROOT . '/' . $folderPath . $filename, JPATH_ROOT . '/media/com_eshop/downloads' . $filename);
						$filename = substr($filename, 1);
						
						$row = new EShopTable('#__eshop_downloads', 'id', $db);
						
						$row->filename					= $filename;
						$row->total_downloads_allowed	= $j2storeProductFile->download_total;
						$row->created_date				= gmdate('Y-m-d H:i:s');
						
						$row->store();
						$eshopDownloadId = $row->id;
						
						foreach ($languages as $language)
						{
							$langCode = trim($language->lang_code);
							
							$row = new EShopTable('#__eshop_downloaddetails', 'id', $db);
							
							$row->download_id		= $eshopDownloadId;
							$row->download_name		= $j2storeProductFile->product_file_display_name;
							$row->language			= trim($language->lang_code);
							
							$row->store();
						}
						
						$row = new EShopTable('#__eshop_productdownloads', 'id', $db);
						
						$row->product_id	= $eshopProductId;
						$row->download_id	= $eshopDownloadId;
						
						$row->store();
					}
				}
				
				//Product discount
				$query->clear()
					->select('a.*')
					->from('#__j2store_product_prices AS a')
					->innerJoin('#__j2store_variants AS b ON a.variant_id = b.j2store_variant_id')
					->where('b.product_id = ' . $j2storeProduct->j2store_product_id);
				$db->setQuery($query);
				$j2storeProductPrices = $db->loadObjectList();
				
				if (isset($j2storeProductPrices) && !empty($j2storeProductPrices))
				{
					foreach ($j2storeProductPrices as $j2storeProductPrice)
					{
						$row = new EShopTable('#__eshop_productdiscounts', 'id', $db);
						
						$row->id               = 0;
						$row->product_id       = $eshopProductId;
						$row->customergroup_id = EShopHelper::getConfigValue('customergroup_id');
						$row->quantity         = $j2storeProductPrice->quantity_from;
						$row->priority         = 0;
						$row->price            = $j2storeProductPrice->price;
						$row->date_start       = $j2storeProductPrice->date_from;
						$row->date_end         = $j2storeProductPrice->date_to;
						$row->published        = 1;
						
						$row->store();
					}
				}
				
				//Product options
				$query->clear()
					->select('*')
					->from('#__j2store_product_options')
					->where('product_id = ' . $j2storeProduct->j2store_product_id);
				$db->setQuery($query);
				$j2storeProductOptions = $db->loadObjectList();
				
				if (isset($j2storeProductOptions) && !empty($j2storeProductOptions))
				{
					foreach ($j2storeProductOptions as $j2storeProductOption)
					{
						$row = new EShopTable('#__eshop_productoptions', 'id', $db);
						
						$row->product_id	= $eshopProductId;
						$row->option_id		= $mappingOptions[$j2storeProductOption->option_id];
						$row->required		= 0;
						
						$row->store();
						$productOptionId = $row->id;
						
						$query->clear()
							->select('*')
							->from('#__j2store_product_optionvalues')
							->where('productoption_id = ' . $j2storeProductOption->j2store_productoption_id);
						$db->setQuery($query);
						$j2storeProductOptionValues = $db->loadObjectList();
						
						if (isset($j2storeProductOptionValues) && !empty($j2storeProductOptionValues))
						{
							foreach ($j2storeProductOptionValues as $j2storeProductOptionValue)
							{
								$row = new EShopTable('#__eshop_productoptionvalues', 'id', $db);
								
								$row->product_option_id		= $productOptionId;
								$row->product_id			= $eshopProductId;
								$row->option_id 			= $mappingOptions[$j2storeProductOption->option_id];
								$row->option_value_id		= $mappingOptionValues[$j2storeProductOption->option_id][$j2storeProductOptionValue->optionvalue_id];
								$row->sku					= $j2storeProductOptionValue->product_optionvalue_sku;
								$row->quantity				= 0;
								$row->price					= $j2storeProductOptionValue->product_optionvalue_price;
								$row->price_sign			= $j2storeProductOptionValue->product_optionvalue_prefix;
								$row->price_type			= 'F';
								$row->weight				= $j2storeProductOptionValue->product_optionvalue_weight;
								$row->weight_sign			= $j2storeProductOptionValue->product_optionvalue_weight_prefix;
								$row->shipping				= $j2storeProductOptionValue->product_optionvalue_sku;
								$row->image					= '';
								
								$row->store();
							}
						}
					}
				}
				
				//Product relations
				$upSells		= $j2storeProduct->up_sells;
				$crossSells		= $j2storeProduct->cross_sells;
				
				$j2storeRelatedProductsArr[$eshopProductId] = [];
				
				if ($upSells != '')
				{
					$upSellsArr = explode(',', $upSells);
					
					for ($i = 0; $n = count($upSellsArr), $i < $n; $i++)
					{
						if (!in_array($upSellsArr[$i], $j2storeRelatedProductsArr[$eshopProductId]))
						{
							$j2storeRelatedProductsArr[$eshopProductId][] = $upSellsArr[$i];
						}
					}
				}
				
				if ($crossSells != '')
				{
					$crossSellsArr = explode(',', $crossSells);
						
					for ($i = 0; $n = count($crossSellsArr), $i < $n; $i++)
					{
						if (!in_array($crossSellsArr[$i], $j2storeRelatedProductsArr[$eshopProductId]))
						{
							$j2storeRelatedProductsArr[$eshopProductId][] = $crossSellsArr[$i];
						}
					}
				}
			}
		}
		
		//Process to set product relations
		if (isset($j2storeRelatedProductsArr) && !empty($j2storeRelatedProductsArr))
		{
			foreach ($j2storeRelatedProductsArr as $key => $j2storeRelatedProducts)
			{
				if (isset($j2storeRelatedProducts) && !empty($j2storeRelatedProducts))
				{
					foreach ($j2storeRelatedProducts as $j2storeRelatedProduct)
					{
						$row = new EShopTable('#__eshop_productrelations', 'id', $db);
						
						$row->product_id			= $key;
						$row->related_product_id 	= $mappingProducts[$j2storeRelatedProduct];
						
						$row->store();
					}
				}
			}
		}
	}
}
