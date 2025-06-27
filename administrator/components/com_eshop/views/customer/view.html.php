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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewCustomer extends EShopViewForm
{
	/**
	 *
	 * @var $addresses
	 */
	protected $addresses;

	/**
	 *
	 * @var $forms
	 */
	protected $forms;

	public function _buildListArray(&$lists, $item)
	{
		Factory::getApplication()->getDocument()->addScript(Uri::root(true) . '/administrator/components/com_eshop/assets/js/eshop.js')
			->addScriptDeclaration(EShopHtmlHelper::getZonesArrayJs())
			->addScriptDeclaration(EShopHtmlHelper::getCountriesOptionsJs());
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, b.customergroup_name AS text')
			->from('#__eshop_customergroups AS a')
			->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.customergroup_name');
		$db->setQuery($query);

		$lists['customergroup_id'] = HTMLHelper::_(
			'select.genericlist',
			$db->loadObjectList(),
			'customergroup_id',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => ' class="input-medium form-select chosen" ',
				'list.select'        => $item->customergroup_id,
			]
		);

		// Prepare addresses data
		$query->clear();
		$query->select('*')
			->from('#__eshop_addresses')
			->where('customer_id = ' . intval($item->customer_id))
			->order('id');
		$db->setQuery($query);
		$addresses = $db->loadObjectList();

		$this->addresses = $addresses;

		if (count($addresses) > 0)
		{
			$forms = [];
			$i     = 1;

			foreach ($addresses as $address)
			{
				$fields = EShopHelper::getFormFields();
				$data   = [];

				foreach ($fields as $field)
				{
					$fieldName   = $field->name;
					$field->name = 'address_' . $field->name . '[]';

					if (property_exists($address, $fieldName))
					{
						$data[$field->name] = $address->{$fieldName};
					}

					if ($field->name == 'address_country_id[]')
					{
						$field->name             = 'address_country_id_' . $i;
						$field->extra_attributes = 'class="form-select" onchange="Eshop.updateStateList(this.value, \'address_zone_id_' . $i . '\')"';
					}

					if ($field->name == 'address_zone_id[]')
					{
						$field->name = 'address_zone_id_' . $i;
					}
				}

				$form         = new EshopRADForm($fields);
				$countryField = $form->getField('address_country_id_' . $i);
				$zoneField    = $form->getField('address_zone_id_' . $i);

				$form->bind($data);

				if ($countryField instanceof EshopRADFormFieldCountries)
				{
					$countryField->setValue($address->country_id);
				}

				if ($zoneField instanceof EshopRADFormFieldZone)
				{
					$zoneField->setValue($address->zone_id);
					$zoneField->setCountryId($address->country_id);
				}

				$forms[] = $form;
				$i++;
			}

			$this->forms = $forms;
		}

		$fields = EShopHelper::getFormFields();

		foreach ($fields as $field)
		{
			$fieldName   = $field->name;
			$field->name = 'new_address_' . $fieldName;

			if ($field->name == 'new_address_country_id')
			{
				$field->extra_attributes = 'class="form-select" onchange="Eshop.updateStateList(this.value, \'new_address_zone_id\')"';
			}
		}

		$newAddressForm = new EshopRADForm($fields);
		$zoneField      = $newAddressForm->getField('new_address_zone_id');

		if ($zoneField instanceof EshopRADFormFieldZone)
		{
			$zoneField->setCountryId(EShopHelper::getConfigValue('country_id'));
		}

		$this->newAddressForm = $newAddressForm;

		return true;
	}

	/**
	 *
	 * Private method to get Country Options
	 *
	 * @param   array  $lists
	 */
	public function _getCountryOptions()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id, country_name AS name')
			->from('#__eshop_countries')
			->where('published = 1')
			->order('country_name');
		$db->setQuery($query);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_PLEASE_SELECT'), 'id', 'name');
		$options   = array_merge($options, $db->loadObjectList());

		return $options;
	}

	/**
	 *
	 * Private method to get Zone Options
	 *
	 * @param   array  $lists
	 */
	public function _getZoneList($countryId = '')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id, zone_name')
			->from('#__eshop_zones')
			->where('country_id = ' . (int) $countryId)
			->where('published = 1')
			->order('zone_name');
		$db->setQuery($query);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_PLEASE_SELECT'), 'id', 'zone_name');
		$options   = array_merge($options, $db->loadObjectList());

		return $options;
	}
}