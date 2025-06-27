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
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewReports extends HtmlView
{
	/**
	 *
	 * @var $items
	 */
	protected $items;

	/**
	 *
	 * @var $pagination
	 */
	protected $pagination;

	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	/**
	 *
	 * @var $lists
	 */
	protected $lists;

	/**
	 *
	 * @var $totalHits
	 */
	protected $totalHits;

	/**
	 *
	 * @var $tax
	 */
	protected $tax;

	/**
	 *
	 * Display function
	 *
	 */
	public function display($tpl = null)
	{
		// Check access first
		$mainframe = Factory::getApplication();
		if (!Factory::getUser()->authorise('eshop.reports', 'com_eshop'))
		{
			$mainframe->enqueueMessage(Text::_('ESHOP_ACCESS_NOT_ALLOW'), 'error');
			$mainframe->redirect('index.php?option=com_eshop&view=dashboard');
		}
		switch ($this->getLayout())
		{
			case 'orders':
				$this->_displayOrders($tpl);
				break;
			case 'viewedproducts':
				$this->_displayViewedProducts($tpl);
				break;
			case 'purchasedproducts':
				$this->_displayPurchasedProducts($tpl);
				break;
			default:
				break;
		}
	}

	/**
	 *
	 * Function to display orders report
	 *
	 * @param   string  $tpl
	 */
	public function _displayOrders($tpl)
	{
		$input             = Factory::getApplication()->input;
		$currency          = EShopCurrency::getInstance();
		$lists             = [];
		$options           = [];
		$options[]         = HTMLHelper::_('select.option', '', Text::_('ESHOP_GROUP_BY'), 'value', 'text');
		$options[]         = HTMLHelper::_('select.option', 'year', Text::_('ESHOP_YEARS'), 'value', 'text');
		$options[]         = HTMLHelper::_('select.option', 'month', Text::_('ESHOP_MONTHS'), 'value', 'text');
		$options[]         = HTMLHelper::_('select.option', 'week', Text::_('ESHOP_WEEKS'), 'value', 'text');
		$options[]         = HTMLHelper::_('select.option', 'day', Text::_('ESHOP_DAYS'), 'value', 'text');
		$groupBy           = $input->getString('group_by', '');
		$lists['group_by'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'group_by',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.select'        => $groupBy,
				'list.attr'          => 'class="input-medium form-select"',
			]
		);
		$db                = Factory::getDbo();
		$query             = $db->getQuery(true);
		$query->select('a.id AS value, b.orderstatus_name AS text')
			->from('#__eshop_orderstatuses AS a')
			->innerJoin('#__eshop_orderstatusdetails AS b ON (a.id = b.orderstatus_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', 0, Text::_('ESHOP_ORDERSSTATUS_ALL'));
		$options                  = array_merge($options, $db->loadObjectList());
		$lists['order_status_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'order_status_id',
			'class="input-large form-select"',
			'value',
			'text',
			$input->getInt('order_status_id')
		);
		$orders                   = $this->get('OrdersData');
		$pagination               = $this->get('OrdersPagination');
		$this->items              = $orders;
		$this->pagination         = $pagination;
		$this->currency           = $currency;
		$this->lists              = $lists;
		parent::display($tpl);
	}

	/**
	 *
	 * Function to display viewed products report
	 *
	 * @param   unknown  $tpl
	 */
	public function _displayViewedProducts($tpl)
	{
		$products  = $this->get('ViewedProductsData');
		$totalHits = 0;
		foreach ($products as $product)
		{
			$totalHits += (int) $product->hits;
		}
		$pagination       = $this->get('ViewedProductsPagination');
		$this->items      = $products;
		$this->pagination = $pagination;
		$this->totalHits  = $totalHits;
		parent::display($tpl);
	}

	/**
	 *
	 * Function to display purchased products report
	 *
	 * @param   unknown  $tpl
	 */
	public function _displayPurchasedProducts($tpl)
	{
		$input = Factory::getApplication()->input;
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, b.category_name AS title, a.category_parent_id AS parent_id')
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_categorydetails AS b ON (a.id = b.category_id)')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		
		$categoryDefaultSorting = EShopHelper::getConfigValue('category_default_sorting', 'name-asc');
		
		switch ($categoryDefaultSorting)
		{
			case 'name-desc':
				$query->order('b.category_name DESC');
				break;
			case 'ordering-asc':
				$query->order('a.ordering ASC');
				break;
			case 'ordering-desc':
				$query->order('a.ordering DESC');
				break;
			case 'id-asc':
				$query->order('a.id ASC');
				break;
			case 'id-desc':
				$query->order('a.id DESC');
				break;
			case 'random':
				$query->order('RAND()');
				break;
			case 'name-asc':
			default:
				$query->order('b.category_name ASC');
				break;
		}
		
		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$children = [];
		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : [];
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}
		$list      = HTMLHelper::_('menu.treerecurse', 0, '', [], $children, 9999, 0, 0);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '0', Text::_('ESHOP_SELECT_A_CATEGORY'));
		foreach ($list as $listItem)
		{
			$options[] = HTMLHelper::_('select.option', $listItem->id, $listItem->treename);
		}
		
		$lists = [];
		$lists['category_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'category_id',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => ' class="input-xlarge form-select" onchange="this.form.submit();"',
				'list.select'        => $input->getInt('category_id', 0),
			]
		);
		
		$query->clear()
			->select('a.id AS value, b.orderstatus_name AS text')
			->from('#__eshop_orderstatuses AS a')
			->innerJoin('#__eshop_orderstatusdetails AS b ON (a.id = b.orderstatus_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', 0, Text::_('ESHOP_ORDERSSTATUS_ALL'));
		$options                  = array_merge($options, $db->loadObjectList());
		
		$lists['order_status_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'order_status_id',
			' class="input-xlarge form-select" style="width: 150px;" ',
			'value',
			'text',
			$input->getInt('order_status_id')
		);
		$currency                 = EShopCurrency::getInstance();
		$tax                      = new EShopTax(EShopHelper::getConfig());
		$products                 = $this->get('PurchasedProductsData');

		$dateStart = $input->getString('date_start', '');

		if ($dateStart == '')
		{
			$dateStart = date('Y-m-d', strtotime(date('Y') . '-' . date('m') . '-01'));
		}

		if ($dateStart != '')
		{
			// In case use only select date, we will set time of From Date to 00:00:00
			if (strpos($dateStart, ' ') === false && strlen($dateStart) <= 10)
			{
				$dateStart = $dateStart . ' 00:00:00';
			}

			try
			{
				$date = DateTime::createFromFormat('Y-m-d H:i:s', $dateStart, new DateTimeZone(Factory::getApplication()->get('offset')));

				if ($date !== false)
				{
					$date->setTime(0, 0, 0);
					$date->setTimezone(new DateTimeZone("UTC"));
					$dateStart = $date->format('Y-m-d H:i:s');
				}
			}
			catch (Exception $e)
			{
				// Do-nothing
			}
		}

		$dateEnd = $input->getString('date_end', '');

		if ($dateEnd == '')
		{
			$dateEnd = date('Y-m-d');
		}

		if ($dateEnd != '')
		{
			// In case use only select date, we will set time of To Date to 23:59:59
			if (strpos($dateEnd, ' ') === false && strlen($dateEnd) <= 10)
			{
				$dateEnd = $dateEnd . ' 23:59:59';
			}

			try
			{
				$date = DateTime::createFromFormat('Y-m-d H:i:s', $dateEnd, new DateTimeZone(Factory::getApplication()->get('offset')));

				if ($date !== false)
				{
					$date->setTime(23, 59, 59);
					$date->setTimezone(new DateTimeZone("UTC"));
					$dateEnd = $date->format('Y-m-d H:i:s');
				}
			}
			catch (Exception $e)
			{
				// Do-nothing
			}
		}

		$orderStatusId = $input->getInt('order_status_id', 0);

		for ($i = 0; $n = count($products), $i < $n; $i++)
		{
			$products[$i]->orderOptions = EShopHelper::getProductOrderOptions($products[$i]->product_id, $dateStart, $dateEnd, $orderStatusId);
		}

		$pagination       = $this->get('PurchasedProductsPagination');
		$this->items      = $products;
		$this->pagination = $pagination;
		$this->currency   = $currency;
		$this->tax        = $tax;
		$this->lists      = $lists;
		parent::display($tpl);
	}
}