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

use Joomla\CMS\Factory;
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
class EShopViewQuote extends EShopViewForm
{
	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	public function _buildListArray(&$lists, $item)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		
		//Quote products list
		$query->select('a.*, b.product_call_for_price')
			->from('#__eshop_quoteproducts AS a')
			->innerJoin('#__eshop_products AS b ON (a.product_id = b.id)')
			->where('quote_id = ' . intval($item->id));
		$db->setQuery($query);
		$quoteProducts = $db->loadObjectList();
		for ($i = 0; $n = count($quoteProducts), $i < $n; $i++)
		{
			$query->clear();
			$query->select('*')
				->from('#__eshop_quoteoptions')
				->where('quote_product_id = ' . intval($quoteProducts[$i]->id));
			$db->setQuery($query);
			$quoteProducts[$i]->options = $db->loadObjectList();
		}
		$lists['quote_products'] = $quoteProducts;
		
		//Quote totals list
		$query->clear();
		$query->select('*')
			->from('#__eshop_quotetotals')
			->where('quote_id = ' . intval($item->id))
			->order('id');
		$db->setQuery($query);
		$lists['quote_totals'] = $db->loadObjectList();
		
		$currency                = EShopCurrency::getInstance();
		$this->currency          = $currency;
	}

	/**
	 * Override Build Toolbar function, only need Save, Save & Close and Close
	 */
	public function _buildToolbar()
	{
		$viewName = $this->getName();
		ToolbarHelper::title(Text::_('ESHOP_QUOTE_DETAILS'));
		ToolbarHelper::cancel($viewName . '.cancel', 'JTOOLBAR_CLOSE');
	}
}