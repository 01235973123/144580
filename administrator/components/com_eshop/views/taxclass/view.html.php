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

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewTaxclass extends EShopViewForm
{
	/**
	 *
	 * @var $baseonOptions
	 */
	protected $baseonOptions;

	/**
	 *
	 * @var $taxrates
	 */
	protected $taxrates;

	/**
	 *
	 * @var $taxrateIds
	 */
	protected $taxrateIds;

	public function _buildListArray(&$lists, $item)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		if ($item->id)
		{
			$query->select("tax_id, based_on, priority")
				->from("#__eshop_taxrules")
				->where("taxclass_id=" . (int) $item->id);
			$db->setQuery($query);
			$taxrateIds = $db->loadObjectList();
			$query->clear();
			$query->select("id, tax_name AS name")
				->from("#__eshop_taxes")
				->where("published = 1");
			$db->setQuery($query);
			$taxrates            = [];
			$taxrates            = array_merge($taxrates, $db->loadObjectList());
			$baseonOptions       = [];
			$baseonOptions[]     = HTMLHelper::_('select.option', 'shipping', Text::_('ESHOP_SHIPPING_ADDRESS'));
			$baseonOptions[]     = HTMLHelper::_('select.option', 'payment', Text::_('ESHOP_PAYMENT_ADDRESS'));
			$baseonOptions[]     = HTMLHelper::_('select.option', 'store', Text::_('ESHOP_STORE_ADDRESS'));
			$this->baseonOptions = $baseonOptions;
			$this->taxrates      = $taxrates;
			$this->taxrateIds    = $taxrateIds;
		}
		//Build assign products to taxclass
		//Products no tax
		$query->clear();
		$query = $db->getQuery(true);
		$query->select('a.id AS value, b.product_name AS text')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->where('product_taxclass_id = 0')
			->order('b.product_name');
		$db->setQuery($query);
		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', '-1', '-- ' . Text::_('ESHOP_ALL_PRODUCTS') . ' --');
		$options                  = array_merge($options, $db->loadObjectList());
		$lists['products_no_tax'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'products_no_tax[]',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
				'list.select'        => '',
			]
		);
		//Products list
		$query->clear();
		$query = $db->getQuery(true);
		$query->select('a.id AS value, b.product_name AS text')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.product_name');
		$db->setQuery($query);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '-1', '-- ' . Text::_('ESHOP_ALL_PRODUCTS') . ' --');
		$options   = array_merge($options, $db->loadObjectList());
		if ($item->id > 0)
		{
			$query->clear();
			$query->select('a.id')
				->from('#__eshop_products AS a')
				->where('product_taxclass_id = ' . (int) $item->id);
			$db->setQuery($query);
			$selectedItems = $db->loadColumn();
		}
		else
		{
			$selectedItems = [];
		}
		$lists['products_list'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'products_list[]',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
				'list.select'        => $selectedItems,
			]
		);

		Factory::getApplication()->getDocument()->addScriptDeclaration(EShopHtmlHelper::getTaxrateOptionsJs())->addScriptDeclaration(
			EShopHtmlHelper::getBaseonOptionsJs()
		);
	}
}