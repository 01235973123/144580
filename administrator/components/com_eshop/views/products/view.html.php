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
use Joomla\Utilities\ArrayHelper;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewProducts extends EShopViewList
{
	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	public function display($tpl = null)
	{
		$this->currency = EShopCurrency::getInstance();
		parent::display($tpl);
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$this->addToolbar();
		}
	}

	/**
	 * Build all the lists items used in the form and store it into the array
	 *
	 * @param  $lists
	 *
	 * @return boolean
	 */
	public function _buildListArray(&$lists, $state)
	{
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
		$lists['category_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'category_id',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => ' class="input-xlarge form-select" onchange="this.form.submit();"',
				'list.select'        => $state->category_id,
			]
		);
		// Stock status list
		$options               = [];
		$options[]             = HTMLHelper::_('select.option', '0', Text::_('ESHOP_SELECT_STOCK_STATUS'));
		$options[]             = HTMLHelper::_('select.option', '1', Text::_('ESHOP_IN_STOCK'));
		$options[]             = HTMLHelper::_('select.option', '2', Text::_('ESHOP_OUT_OF_STOCK'));
		$lists['stock_status'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'stock_status',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => ' class="input-xlarge form-select" onchange="this.form.submit();"',
				'list.select'        => $state->stock_status,
			]
		);

		//Start prepare the data for batch process
		//Build manufacturer list
		$query->clear();
		$query->select('a.id AS value, b.manufacturer_name AS text')
			->from('#__eshop_manufacturers AS a')
			->innerJoin('#__eshop_manufacturerdetails AS b ON (a.id = b.manufacturer_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');

		$manufacturerDefaultSorting = EShopHelper::getConfigValue('manufacturer_default_sorting', 'name-asc');

		switch ($manufacturerDefaultSorting)
		{
			case 'name-desc':
				$query->order('b.manufacturer_name DESC');
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
				$query->order('b.manufacturer_name ASC');
				break;
		}

		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_BATCH_PRODUCTS_KEEP_MANUFACTURER'), 'value', 'text');
		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}
		$lists['manufacturer'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'manufacturer_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select" ',
				'list.select'        => '',
			]
		);
		$options               = [];
		$options[]             = HTMLHelper::_('select.option', '0', Text::_('ESHOP_SELECT_A_MANUFACTURER'));
		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}
		$lists['mf_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'mf_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select" onchange="this.form.submit();" ',
				'list.select'        => $state->mf_id,
			]
		);

		//Build categories list
		$query->clear();
		$query->select('a.id, b.category_name AS title, a.category_parent_id AS parent_id')
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_categorydetails AS b ON (a.id = b.category_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$children = [];
		if ($rows)
		{
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
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_BATCH_PRODUCTS_KEEP_MAIN_CATEGORY'));
		foreach ($list as $listItem)
		{
			$options[] = HTMLHelper::_('select.option', $listItem->id, $listItem->treename);
		}
		$lists['main_category_id'] = HTMLHelper::_('select.genericlist', $options, 'main_category_id', [
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="input-xlarge form-select"',
			'list.select'        => '',
		]);
		array_shift($options);
		$options_first = HTMLHelper::_('select.option', 0, Text::_('ESHOP_BATCH_PRODUCTS_KEEP_ADDITIONAL_CATEGORY'));
		array_unshift($options, $options_first);
		$lists['additional_category_id'] = HTMLHelper::_('select.genericlist', $options, 'additional_category_id[]', [
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="input-xlarge form-select" multiple="multiple"="multiple"',
			'list.select'        => 0,
		]);

		//Build customer groups list
		$query->clear();
		$query->select('a.id AS value, b.customergroup_name AS text')
			->from('#__eshop_customergroups AS a')
			->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.customergroup_name');
		$db->setQuery($query);
		$options       = $db->loadObjectList();
		$options_first = HTMLHelper::_('select.option', 0, Text::_('ESHOP_BATCH_PRODUCTS_KEEP_CUSTOMER_GROUPS'));
		array_unshift($options, $options_first);
		$lists['product_customergroups'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_customergroups[]',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select" multiple="multiple"',
				'list.select'        => 0,
			]
		);
		//Build Lengths list
		$query->clear();
		$query->select('a.id AS value, b.length_name AS text')
			->from('#__eshop_lengths AS a')
			->innerJoin('#__eshop_lengthdetails AS b ON (a.id = b.length_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_BATCH_PRODUCTS_KEEP_LENGTH'), 'value', 'text');
		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}
		$lists['product_length_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_length_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select" ',
				'list.select'        => '',
			]
		);

		//Build Weights list
		$query->clear();
		$query->select('a.id AS value, b.weight_name AS text')
			->from('#__eshop_weights AS a')
			->innerJoin('#__eshop_weightdetails AS b ON (a.id = b.weight_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_BATCH_PRODUCTS_KEEP_WEIGHT'), 'value', 'text');
		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}
		$lists['product_weight_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_weight_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select" ',
				'list.select'        => '',
			]
		);

		//Build taxclasses list
		$query->clear();
		$query->select('id AS value, taxclass_name AS text')
			->from('#__eshop_taxclasses')
			->where('published = 1');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_BATCH_PRODUCTS_KEEP_TAXCLASS'), 'value', 'text');
		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}
		$lists['product_taxclass_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_taxclass_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select" ',
				'list.select'        => '',
			]
		);

		//Stock status list
		$query->clear();
		$query->select('a.id AS value, b.stockstatus_name AS text')
			->from('#__eshop_stockstatuses AS a')
			->innerJoin('#__eshop_stockstatusdetails AS b ON (a.id = b.stockstatus_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_BATCH_PRODUCTS_KEEP_STOCK_STATUS'), 'value', 'text');
		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}
		$lists['product_stock_status_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_stock_status_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select" ',
				'list.select'        => '',
			]
		);

		//Build published list
		$lists['product_published'] = HTMLHelper::_('select.booleanlist', 'product_published', ' class="input-xlarge form-select" disabled ', 1);
		//Build manage stock list
		$lists['product_manage_stock'] = HTMLHelper::_(
			'select.booleanlist',
			'product_manage_stock',
			' class="input-xlarge form-select" disabled ',
			1
		);
		//Build stock display list
		$lists['product_stock_display'] = HTMLHelper::_(
			'select.booleanlist',
			'product_stock_display',
			' class="input-xlarge form-select" disabled ',
			1
		);
		//Build stock warning list
		$lists['product_stock_warning'] = HTMLHelper::_(
			'select.booleanlist',
			'product_stock_warning',
			' class="input-xlarge form-select" disabled ',
			1
		);
		//Build stock checkout list
		$lists['product_stock_checkout'] = HTMLHelper::_(
			'select.booleanlist',
			'product_stock_checkout',
			' class="input-xlarge form-select" disabled ',
			1
		);
		//Build stock checkout list
		$lists['product_inventory_global'] = HTMLHelper::_(
			'select.booleanlist',
			'product_inventory_global',
			' class="input-xlarge form-select" disabled ',
			1
		);
		//Build shipping list
		$lists['product_shipping'] = HTMLHelper::_('select.booleanlist', 'product_shipping', ' class="input-xlarge form-select" disabled ', 1);
		//Build featured  list
		$lists['product_featured'] = HTMLHelper::_('select.booleanlist', 'product_featured', ' class="input-xlarge form-select" disabled ', 1);
		//Build call for price list
		$lists['product_call_for_price'] = HTMLHelper::_(
			'select.booleanlist',
			'product_call_for_price',
			' class="input-xlarge form-select" disabled ',
			1
		);
		//Quote mode
		$lists['product_quote_mode'] = HTMLHelper::_('select.booleanlist', 'product_quote_mode', ' class="input-xlarge form-select" disabled ', 1);
		//End prepare the data for batch process
	}

	/**
	 * Additional grid function for featured toggles
	 *
	 * @return string HTML code to write the toggle button
	 */
	public function toggle($field, $i, $imgY = 'tick.png', $imgX = 'publish_x.png', $prefix = '')
	{
		$img    = $field ? $imgY : $imgX;
		$task   = $field ? 'product.unfeatured' : 'product.featured';
		$alt    = $field ? Text::_('ESHOP_PRODUCT_FEATURED') : Text::_('ESHOP_PRODUCT_UNFEATURED');
		$action = $field ? Text::_('ESHOP_PRODUCT_UNFEATURED') : Text::_('ESHOP_PRODUCT_FEATURED');

		return ('<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $task . '\')" title="' . $action . '">' .
			HTMLHelper::_('image', 'admin/' . $img, $alt, null, true) . '</a>');
	}

	public function featured($value = 0, $i = 0, $canChange = true)
	{
		HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);
		// Array of image, task, title, action
		$states = [
			0 => ['unfeatured', 'product.featured', 'ESHOP_PRODUCT_UNFEATURED', 'ESHOP_PRODUCT_FEATURED'],
			1 => ['featured', 'product.unfeatured', 'ESHOP_PRODUCT_FEATURED', 'ESHOP_PRODUCT_UNFEATURED'],
		];
		$state  = ArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon   = $state[0];
		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="' . HTMLHelper::tooltipText(
					$state[3]
				) . '"><i class="icon-' . $icon . '"></i></a>';
		}
		else
		{
			$html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="' . HTMLHelper::tooltipText(
					$state[2]
				) . '"><i class="icon-' . $icon . '"></i></a>';
		}

		return $html;
	}

	/**
	 *
	 * Function to add tool bar
	 */
	protected function addToolbar()
	{
		$toolbar = Toolbar::getInstance('toolbar');

		// Instantiate a new FileLayout instance and render the batch button
		$title  = Text::_('ESHOP_BATCH');
		$layout = new FileLayout('joomla.toolbar.batch');
		$dhtml  = $layout->render(['title' => $title]);
		$toolbar->appendButton('Custom', $dhtml, 'batch');

		// Instantiate a new FileLayout instance and render the export button
		$layout = new FileLayout('joomla.toolbar.modal');
		$dhtml  = $layout->render(
			[
				'selector' => 'inventoryModal',
				'icon'     => 'edit',
				'text'     => Text::_('ESHOP_MANAGE_INVENTORY'),
			]
		);

		$toolbar->appendButton('Custom', $dhtml, 'inventory');
	}
}