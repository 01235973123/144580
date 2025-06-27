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
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewOrders extends EShopViewList
{
	/**
	 *
	 * @var $nullDate
	 */
	protected $nullDate;

	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	public function _buildListArray(&$lists, $state)
	{
		$input = Factory::getApplication()->input;
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		//Products list
		$query->clear();
		$query->select('a.id AS value, b.product_name AS text')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.product_name');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '0', Text::_('ESHOP_PRODUCT_ALL'));

		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}

		$lists['product_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-large form-select" onchange="this.form.submit();" ',
				'list.select'        => $input->get('product_id'),
			]
		);

		//Manufacturers list
		$query->clear()
			->select('a.id AS value, b.manufacturer_name AS text')
			->from('#__eshop_manufacturers AS a')
			->innerJoin('#__eshop_manufacturerdetails AS b ON (a.id = b.manufacturer_id)')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.manufacturer_name');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '0', Text::_('ESHOP_MANUFACTURER_ALL'));

		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}

		$lists['manufacturer_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'manufacturer_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-large form-select" onchange="this.form.submit();" ',
				'list.select'        => $input->get('manufacturer_id'),
			]
		);

		//Payment methods list
		$query->clear()
			->select('name AS value, title AS text')
			->from('#__eshop_payments')
			->where('published = 1')
			->order('title');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (count($rows))
		{
			foreach ($rows as $key => $row)
			{
				$rows[$key]->text = Text::_($row->text);
			}
		}

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('ESHOP_PAYMENT_METHOD_ALL'));

		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}

		$lists['payment_method'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'payment_method',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-large form-select" onchange="this.form.submit();" ',
				'list.select'        => $input->getString('payment_method'),
			]
		);

		//Order Statuses list
		$query->clear()
			->select('a.id AS value, b.orderstatus_name AS text')
			->from('#__eshop_orderstatuses AS a')
			->innerJoin('#__eshop_orderstatusdetails AS b ON (a.id = b.orderstatus_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.orderstatus_name');
		$db->setQuery($query);
		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', 0, Text::_('ESHOP_ORDERSSTATUS_ALL'));
		$options                  = array_merge($options, $db->loadObjectList());
		$lists['order_status_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'order_status_id',
			' class="input-large form-select" style="width: 150px;" onchange="this.form.submit();"',
			'value',
			'text',
			$input->getInt('order_status_id', 0)
		);

		//Shipping methods list
		$query->clear()
			->select('DISTINCT(title) AS value, title AS text')
			->from('#__eshop_ordertotals')
			->where('name = "shipping"')
			->order('title');
		$db->setQuery($query);
		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', '', Text::_('ESHOP_SHIPPING_METHOD_ALL'));
		$options                  = array_merge($options, $db->loadObjectList());
		$lists['shipping_method'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'shipping_method',
			' class="input-large form-select" style="width: 200px;" onchange="this.form.submit();"',
			'value',
			'text',
			$input->getstring('shipping_method', '')
		);

		$query->clear();
		$query->select('a.id, b.orderstatus_name')
			->from('#__eshop_orderstatuses AS a')
			->innerJoin('#__eshop_orderstatusdetails AS b ON (a.id = b.orderstatus_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$lists['order_status_ids'] = HTMLHelper::_(
			'select.genericlist',
			$db->loadObjectList(),
			'order_status_ids',
			'class="input-xlarge form-select"',
			'id',
			'orderstatus_name',
			''
		);

		$nullDate       = $db->getNullDate();
		$this->nullDate = $nullDate;
		$currency       = EShopCurrency::getInstance();
		$this->currency = $currency;
	}

	/**
	 * Override Build Toolbar function, only need Delete, Edit and Download Invoice
	 */
	public function _buildToolbar()
	{
		$viewName   = $this->getName();
		$controller = EShopInflector::singularize($this->getName());
		ToolbarHelper::title(Text::_($this->lang_prefix . '_' . strtoupper($viewName)));
		ToolbarHelper::deleteList(Text::_($this->lang_prefix . '_DELETE_' . strtoupper($this->getName()) . '_CONFIRM'), $controller . '.remove');
		ToolbarHelper::editList($controller . '.edit');

		if (EShopHelper::getConfigValue('invoice_enable'))
		{
			ToolbarHelper::custom($controller . '.downloadInvoice', 'print', 'print', Text::_('ESHOP_DOWNLOAD_INVOICE'), true);
		}

		ToolbarHelper::custom($controller . '.export', 'download', 'download', Text::_('ESHOP_EXPORTS'), true);
		ToolbarHelper::custom($controller . '.downloadXML', 'download', 'download', Text::_('ESHOP_DOWNLOAD_XML'), true);

		$toolbar = Toolbar::getInstance('toolbar');

		// Instantiate a new FileLayout instance and render the batch button
		$title  = Text::_('ESHOP_BATCH');
		$layout = new FileLayout('joomla.toolbar.batch');
		$dhtml  = $layout->render(['title' => $title]);
		$toolbar->appendButton('Custom', $dhtml, 'batch');
	}
}