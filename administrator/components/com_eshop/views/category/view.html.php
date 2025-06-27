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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewCategory extends EShopViewForm
{

	public function _buildListArray(&$lists, $item)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, b.category_name AS title, a.category_parent_id AS parent_id')
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_categorydetails AS b ON (a.id = b.category_id)')
			->where('a.published = 1')
			->where('a.id !=' . (int) $item->id)
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
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
		$options[] = HTMLHelper::_('select.option', '0', Text::_('ESHOP_TOP'));
		foreach ($list as $listItem)
		{
			$options[] = HTMLHelper::_('select.option', $listItem->id, $listItem->treename);
		}
		$lists['category_parent_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'category_parent_id',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => ' class="input-xlarge form-select chosen" ',
				'list.select'        => $item->category_parent_id,
			]
		);
		//Build customer groups list
		$query->clear();
		$query->select('a.id AS value, b.customergroup_name AS text')
			->from('#__eshop_customergroups AS a')
			->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.customergroup_name');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		if ($item->category_customergroups != '')
		{
			$selectedItems = explode(',', $item->category_customergroups);
		}
		else
		{
			$selectedItems = [];
		}
		$lists['category_customergroups'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'category_customergroups[]',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => ' class="input-xlarge form-select chosen" multiple ',
				'list.select'        => $selectedItems,
			]
		);

		if ($item->category_cart_mode_customergroups != '')
		{
			$selectedItems = explode(',', $item->category_cart_mode_customergroups);
		}
		else
		{
			$selectedItems = [];
		}
		$lists['category_cart_mode_customergroups'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'category_cart_mode_customergroups[]',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => ' class="input-xlarge form-select chosen" multiple ',
				'list.select'        => $selectedItems,
			]
		);

		//Build category layout list
		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', 'default', Text::_('ESHOP_CATEGORY_LAYOUT_DEFAULT'));
		$options[]                = HTMLHelper::_('select.option', 'table', Text::_('ESHOP_CATEGORY_LAYOUT_TABLE'));
		$lists['category_layout'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'category_layout',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$item->category_layout
		);

		//Product sort options list
		$sortValues = [
			'',
			'a.ordering-ASC',
			'a.ordering-DESC',
			'b.product_name-ASC',
			'b.product_name-DESC',
			'a.product_sku-ASC',
			'a.product_sku-DESC',
			'a.product_price-ASC',
			'a.product_price-DESC',
			'a.product_length-ASC',
			'a.product_length-DESC',
			'a.product_width-ASC',
			'a.product_width-DESC',
			'a.product_height-ASC',
			'a.product_height-DESC',
			'a.product_weight-ASC',
			'a.product_weight-DESC',
			'a.product_quantity-ASC',
			'a.product_quantity-DESC',
			'b.product_short_desc-ASC',
			'b.product_short_desc-DESC',
			'b.product_desc-ASC',
			'b.product_desc-DESC',
			'product_rates-ASC',
			'product_rates-DESC',
			'product_reviews-ASC',
			'product_reviews-DESC',
			'a.id-DESC',
			'a.id-ASC',
			'product_best_sellers-DESC',
			'RAND()',
		];

		$sortTexts = [
			Text::_('ESHOP_CONFIG_USE_GLOBAL'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_ORDERING_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_ORDERING_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_NAME_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_NAME_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_SKU_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_SKU_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_PRICE_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_PRICE_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_LENGTH_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_LENGTH_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_WIDTH_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_WIDTH_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_HEIGHT_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_HEIGHT_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_WEIGHT_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_WEIGHT_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_QUANTITY_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_QUANTITY_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_SHORT_DESC_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_SHORT_DESC_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_DESC_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_DESC_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_RATES_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_RATES_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_REVIEWS_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_REVIEWS_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_LATEST'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_OLDEST'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_BEST_SELLERS'),
			Text::_('ESHOP_CONFIG_SORTING_RANDOMLY'),
		];

		$options = [];

		for ($i = 0; $n = count($sortValues), $i < $n; $i++)
		{
			$options[] = HTMLHelper::_('select.option', $sortValues[$i], $sortTexts[$i]);
		}

		$params = new Registry($item->params);

		$lists['default_sorting'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'default_sorting',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select"',
				'list.select'        => $params->get('default_sorting'),
			]
		);

		// Build quantity box option
		$options               = [];
		$options[]             = HTMLHelper::_('select.option', '', Text::_('ESHOP_CONFIG_USE_GLOBAL'));
		$options[]             = HTMLHelper::_('select.option', '1', Text::_('ESHOP_YES'));
		$options[]             = HTMLHelper::_('select.option', '0', Text::_('ESHOP_NO'));
		$lists['quantity_box'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'quantity_box',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$params->get('quantity_box', '')
		);
	}

	/**
	 * Build the toolbar for view list
	 */
	public function _buildToolbar()
	{
		$input    = Factory::getApplication()->input;
		$viewName = $this->getName();
		$canDo    = EShopHelper::getActions($viewName);
		$edit     = $input->get('edit');
		$text     = $edit ? Text::_($this->lang_prefix . '_EDIT') : Text::_($this->lang_prefix . '_NEW');
		if (Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
		{
			$categoryName = $this->item->{'category_name_' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB')};
		}
		else
		{
			$categoryName = $this->item->category_name;
		}
		if ($edit)
		{
			$categoryInfo = ' [ ' . $categoryName . ' ]';
		}
		else
		{
			$categoryInfo = '';
		}
		ToolbarHelper::title(
			Text::_($this->lang_prefix . '_' . $viewName) . ': <small><small>[ ' . $text . ' ]' . $categoryInfo . '</small></small>'
		);
		ToolbarHelper::apply($viewName . '.apply');
		ToolbarHelper::save($viewName . '.save');
		ToolbarHelper::save2new($viewName . '.save2new');

		if ($edit)
		{
			ToolbarHelper::cancel($viewName . '.cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			ToolbarHelper::cancel($viewName . '.cancel');
		}
	}
}