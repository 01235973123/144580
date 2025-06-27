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

/**
 * HTML View class for EShop component
 *
 * @static
 *
 * @package    Joomla
 * @subpackage EShop
 * @since      1.5
 */
class EShopViewProduct extends EShopView
{
	/**
	 *
	 * @var $tax
	 */
	protected $tax;

	/**
	 *
	 * @var $item
	 */
	protected $item;

	/**
	 *
	 * @var $option_sku
	 */
	protected $option_sku;

	/**
	 *
	 * @var $option_quantity
	 */
	protected $option_quantity;

	/**
	 *
	 * @var $option_weight
	 */
	protected $option_weight;

	/**
	 *
	 * @var $option_price
	 */
	protected $option_price;
	
	/**
	 *
	 * @var $option_value
	 */
	protected $option_value;

	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	public function display($tpl = null)
	{
		$input = Factory::getApplication()->input;
		$item  = $this->get('Data');
		$tax   = new EShopTax(EShopHelper::getConfig());
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($input->get('options', [], 'array'))
		{
			$options = array_filter($input->get('options', [], 'array'));
		}
		else
		{
			$options = [];
		}

		$optionSku      = '';
		$optionQuantity = '';
		$optionWeight   = 0;
		$optionPrice    = 0;

		foreach ($options as $productOptionId => $optionValue)
		{
			$query->clear();
			$query->select('po.id, po.option_id, o.option_type')
				->from('#__eshop_productoptions AS po')
				->innerJoin('#__eshop_options AS o ON (po.option_id = o.id)')
				->where('po.id = ' . intval($productOptionId))
				->where('po.product_id = ' . intval($item->id));
			$db->setQuery($query);
			$optionRow = $db->loadObject();
			if (is_object($optionRow))
			{
				if ($optionRow->option_type == 'Select' || $optionRow->option_type == 'Radio')
				{
					$query->clear();
					$query->select('sku, quantity, price, price_sign, price_type, weight, weight_sign')
						->from('#__eshop_productoptionvalues')
						->where('id = ' . intval($optionValue))
						->where('product_option_id = ' . intval($productOptionId));
					$db->setQuery($query);
					$optionValueRow = $db->loadObject();

					if (is_object($optionValueRow))
					{
						$optionSku      = $optionValueRow->sku;
						$optionQuantity = $optionValueRow->quantity;


						//Calculate option weight
						if ($optionValueRow->weight_sign == '+')
						{
							$optionWeight += $optionValueRow->weight;
						}
						else
						{
							$optionWeight -= $optionValueRow->weight;
						}

						//Calculate option price
						if ($optionValueRow->price_sign == '+')
						{
							if ($optionValueRow->price_type == 'P')
							{
								$optionPrice += $item->product_price * $optionValueRow->price / 100;
							}
							else
							{
								$optionPrice += $optionValueRow->price;
							}
						}
						elseif ($optionValueRow->price_sign == '-')
						{
							if ($optionValueRow->price_type == 'P')
							{
								$optionPrice -= $item->product_price * $optionValueRow->price / 100;
							}
							else
							{
								$optionPrice -= $optionValueRow->price;
							}
						}
					}
				}
				elseif ($optionRow->option_type == 'Checkbox')
				{
					foreach ($optionValue as $productOptionValueId)
					{
						$query->clear();
						$query->select('sku, quantity, price, price_sign, price_type, weight, weight_sign')
							->from('#__eshop_productoptionvalues')
							->where('product_option_id = ' . intval($productOptionId))
							->where('id = ' . intval($productOptionValueId));
						$db->setQuery($query);
						$optionValueRow = $db->loadObject();

						if (is_object($optionValueRow))
						{
							$optionSku      = $optionValueRow->sku;
							$optionQuantity = $optionValueRow->quantity;

							//Calculate option weight
							if ($optionValueRow->weight_sign == '+')
							{
								$optionWeight += $optionValueRow->weight;
							}
							else
							{
								$optionWeight -= $optionValueRow->weight;
							}

							//Calculate option price
							if ($optionValueRow->price_sign == '+')
							{
								if ($optionValueRow->price_type == 'P')
								{
									$optionPrice += $item->product_price * $optionValueRow->price / 100;
								}
								else
								{
									$optionPrice += $optionValueRow->price;
								}
							}
							elseif ($optionValueRow->price_sign == '-')
							{
								if ($optionValueRow->price_type == 'P')
								{
									$optionPrice -= $item->product_price * $optionValueRow->price / 100;
								}
								else
								{
									$optionPrice -= $optionValueRow->price;
								}
							}
						}
					}
				}
				elseif ($optionRow->option_type == 'Text' || $optionRow->option_type == 'Textarea')
				{
					$query->clear()
						->select('price, price_sign, price_type')
						->from('#__eshop_productoptionvalues')
						->where('option_id = ' . intval($optionRow->option_id))
						->where('product_option_id = ' . intval($productOptionId))
						->where('product_id = ' . intval($item->id));
					$db->setQuery($query);
					$optionValueRow = $db->loadObject();
					if (is_object($optionValueRow))
					{
						//Calculate option price
						if ($optionValueRow->price_sign == '+')
						{
							if ($optionValueRow->price_type == 'P')
							{
								$optionPrice += ($item->product_price * $optionValueRow->price / 100) * strlen($optionValue);
							}
							else
							{
								$optionPrice += $optionValueRow->price * strlen($optionValue);
							}
						}
						elseif ($optionValueRow->price_sign == '-')
						{
							if ($optionValueRow->price_type == 'P')
							{
								$optionPrice -= ($item->product_price * $optionValueRow->price / 100) * strlen($optionValue);
							}
							else
							{
								$optionPrice -= $optionValueRow->price * strlen($optionValue);
							}
						}
					}
				}
			}
		}

		// Product availability
		$productInventory = EShopHelper::getProductInventory($item->id);

		if ($item->product_quantity <= 0)
		{
			$availability = EShopHelper::getStockStatusName($productInventory['product_stock_status_id'], Factory::getLanguage()->getTag());
		}
		elseif ($productInventory['product_stock_display'])
		{
			$availability = $item->product_quantity;
		}
		else
		{
			$availability = Text::_('ESHOP_IN_STOCK');
		}

		$item->availability = $availability;

		// Product availability
		if ($optionQuantity != '' && $optionQuantity <= 0)
		{
			$optionQuantity = EShopHelper::getStockStatusName($productInventory['product_stock_status_id'], Factory::getLanguage()->getTag());
		}
		elseif (!$productInventory['product_stock_display'])
		{
			$optionQuantity = Text::_('ESHOP_IN_STOCK');
		}

		$this->tax             = $tax;
		$this->item            = $item;
		$this->option_sku      = $optionSku;
		$this->option_value	   = $optionValue;
		$this->option_quantity = $optionQuantity;
		$this->option_weight   = $optionWeight;
		$this->option_price    = $optionPrice;
		$this->currency        = EShopCurrency::getInstance();
		$this->bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

		parent::display($tpl);
	}
}