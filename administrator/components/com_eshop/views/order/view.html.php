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
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewOrder extends EShopViewForm
{
	/**
	 *
	 * @var $billingForm
	 */
	protected $billingForm;

	/**
	 *
	 * @var $shippingForm
	 */
	protected $shippingForm;

	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	public function _buildListArray(&$lists, $item)
	{
		$currency = EShopCurrency::getInstance();
		$db       = Factory::getDbo();
		//Customer list
		$query = $db->getQuery(true);
		$query->select('c.customer_id AS value, u.name AS text')
			->from('#__eshop_customers AS c')
			->innerJoin('#__users AS u ON (c.customer_id = u.id)')
			->where('c.published = 1')
			->where('u.block = 0');
		$db->setQuery($query);
		$lists['customer_id'] = HTMLHelper::_(
			'select.genericlist',
			$db->loadObjectList(),
			'customer_id',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => ' class="input-xlarge form-select" ',
				'list.select'        => $item->customer_id,
			]
		);
		//Customergroup list
		$query->clear();
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
				'list.attr'          => ' class="input-xlarge form-select" ',
				'list.select'        => $item->customergroup_id,
			]
		);
		//Order products list
		$orderProducts           = EShopHelper::getOrderProducts($item->id);
		$lists['order_products'] = $orderProducts;
		//Order totals list
		$query->clear();
		$query->select('*')
			->from('#__eshop_ordertotals')
			->where('order_id = ' . intval($item->id))
			->order('id');
		$db->setQuery($query);
		$lists['order_totals'] = $db->loadObjectList();
		//Order status
		$paymentCountryId  = $item->payment_country_id ?: EShopHelper::getConfigValue('country_id');
		$shippingCountryId = $item->shipping_country_id ?: EShopHelper::getConfigValue('country_id');
		$query->clear();
		$query->select('a.id, b.orderstatus_name')
			->from('#__eshop_orderstatuses AS a')
			->innerJoin('#__eshop_orderstatusdetails AS b ON (a.id = b.orderstatus_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$lists['order_status_id'] = HTMLHelper::_(
			'select.genericlist',
			$db->loadObjectList(),
			'order_status_id',
			'class="input-xlarge form-select"',
			'id',
			'orderstatus_name',
			$item->order_status_id
		);
		//Payment and Shipping country, zone list
		$lists['payment_country_id']  = HTMLHelper::_(
			'select.genericlist',
			$this->_getCountryOptions(),
			'payment_country_id',
			' class="input-xlarge form-select"  onchange="paymentCountry(this, \'' . $item->payment_zone_id . '\')" ',
			'id',
			'name',
			$paymentCountryId
		);
		$lists['payment_zone_id']     = HTMLHelper::_(
			'select.genericlist',
			$this->_getZoneList($paymentCountryId),
			'payment_zone_id',
			'class="input-xlarge form-select"',
			'id',
			'zone_name',
			$item->payment_zone_id
		);
		$lists['shipping_country_id'] = HTMLHelper::_(
			'select.genericlist',
			$this->_getCountryOptions(),
			'shipping_country_id',
			' class="input-xlarge form-select"  onchange="shippingCountry(this, \'' . $item->shipping_zone_id . '\')" ',
			'id',
			'name',
			$shippingCountryId
		);
		$lists['shipping_zone_id']    = HTMLHelper::_(
			'select.genericlist',
			$this->_getZoneList($shippingCountryId),
			'shipping_zone_id',
			'class="input-xlarge form-select"',
			'id',
			'zone_name',
			$item->shipping_zone_id
		);
		$currency                     = EShopCurrency::getInstance();

		//Form for billing and shipping address
		$fields = array_keys($db->getTableColumns('#__eshop_orders'));
		$data   = [];
		foreach ($fields as $field)
		{
			$data[$field] = $item->{$field};
		}
		$billingFields  = EShopHelper::getFormFields('B');
		$shippingFields = EShopHelper::getFormFields('S');
		foreach ($billingFields as $field)
		{
			$field->name = 'payment_' . $field->name;
		}
		$billingForm = new EshopRADForm($billingFields);
		$billingForm->bind($data);
		foreach ($shippingFields as $field)
		{
			$field->name = 'shipping_' . $field->name;
		}
		$shippingForm = new EshopRADForm($shippingFields);
		$shippingForm->bind($data);

		$paymentZoneId = (int) $item->payment_zone_id;
		if (EShopHelper::isFieldPublished('country_id') && $paymentCountryId)
		{
			$countryField = $billingForm->getField('payment_country_id');
			if ($countryField instanceof EshopRADFormFieldCountries)
			{
				$countryField->setValue($paymentCountryId);
			}
		}
		if ($paymentCountryId && EShopHelper::isFieldPublished('zone_id'))
		{
			$zoneField = $billingForm->getField('payment_zone_id');
			if ($zoneField instanceof EshopRADFormFieldZone)
			{
				$zoneField->setCountryId($paymentCountryId);
				if ($paymentZoneId)
				{
					$zoneField->setValue($paymentZoneId);
				}
			}
		}
		$shippingZoneId = (int) $item->shipping_zone_id;
		if (EShopHelper::isFieldPublished('country_id') && $shippingCountryId)
		{
			$countryField = $shippingForm->getField('shipping_country_id');
			if ($countryField instanceof EshopRADFormFieldCountries)
			{
				$countryField->setValue($shippingCountryId);
			}
		}
		if ($shippingCountryId && EShopHelper::isFieldPublished('zone_id'))
		{
			$zoneField = $shippingForm->getField('shipping_zone_id');
			if ($zoneField instanceof EshopRADFormFieldZone)
			{
				$zoneField->setCountryId($shippingCountryId);
				if ($shippingZoneId)
				{
					$zoneField->setValue($shippingZoneId);
				}
			}
		}

		$this->billingForm  = $billingForm;
		$this->shippingForm = $shippingForm;
		$this->currency     = $currency;
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

	/**
	 * Override Build Toolbar function, only need Save, Save & Close and Close
	 */
	public function _buildToolbar()
	{
		$viewName = $this->getName();
		$canDo    = EShopHelper::getActions($viewName);
		$text     = Text::_($this->lang_prefix . '_EDIT');
		ToolbarHelper::title(Text::_($this->lang_prefix . '_' . $viewName) . ': <small><small>[ ' . $text . ' ]</small></small>');
		ToolbarHelper::apply($viewName . '.apply');
		ToolbarHelper::save($viewName . '.save');

		if (EShopHelper::isInvoiceAvailable($this->item, '1', false))
		{
			ToolbarHelper::custom('order.downloadInvoice', 'print', 'print', Text::_('ESHOP_DOWNLOAD_INVOICE'), false);
		}

		ToolbarHelper::cancel($viewName . '.cancel', 'JTOOLBAR_CLOSE');
	}
}