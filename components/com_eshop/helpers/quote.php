<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2013 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Uri\Uri;

class EShopQuote
{
	/**
	 * Session quote data
	 *
	 * @var array
	 */
	protected $quote;

	/**
	 *
	 * Entity quote data
	 * @var array
	 */
	protected $quoteData;

	/**
	 * Constructor function
	 */
	public function __construct()
	{
		$session         = Factory::getApplication()->getSession();
		$this->quoteData = [];
		$this->quote     = $session->get('quote');
	}

	/**
	 *
	 * Function to get data in the quote
	 */
	public function getQuoteData()
	{
		$session     = Factory::getApplication()->getSession();
		$quote       = $session->get('quote');
		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(EShopHelper::getServerTimeFromGMTTime());

		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		if (!$this->quoteData && !empty($quote))
		{
			$baseUri = Uri::base(true);

			foreach ($quote as $key => $quantity)
			{
				$keyArr    = explode(':', $key);
				$productId = $keyArr[0];

				if (isset($keyArr[1]))
				{
					$options = unserialize(base64_decode($keyArr[1]));
				}
				else
				{
					$options = [];
				}

				//Get product information
				$query->clear()
					->select('a.*, b.product_name, b.product_alias, b.product_desc, b.product_short_desc, b.meta_key, b.meta_desc')
					->from('#__eshop_products AS a')
					->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
					->where('a.id = ' . intval($productId))
					->where('b.language = "' . Factory::getLanguage()->getTag() . '"');
				$db->setQuery($query);
				$row = $db->loadObject();

				$price = $row->product_price;

				if (is_object($row))
				{
					// Image
					$imageSizeFunction = EShopHelper::getConfigValue('quote_image_size_function', 'resizeImage');

					if ($row->product_image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $row->product_image))
					{
						if (EShopHelper::getConfigValue('product_use_image_watermarks'))
						{
							$watermarkImage = EShopHelper::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/products/' . $row->product_image);
							$productImage   = $watermarkImage;
						}
						else
						{
							$productImage = $row->product_image;
						}

						$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
							[
								$productImage,
								JPATH_ROOT . '/media/com_eshop/products/',
								EShopHelper::getConfigValue('image_cart_width'),
								EShopHelper::getConfigValue('image_cart_height'),
							]);
					}
					else
					{
						$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
							[
								EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
								JPATH_ROOT . '/media/com_eshop/products/',
								EShopHelper::getConfigValue('image_cart_width'),
								EShopHelper::getConfigValue('image_cart_height'),
							]);
					}

					if ($imageSizeFunction == 'notResizeImage')
					{
						$image = $baseUri . '/media/com_eshop/products/' . $image;
					}
					else
					{
						$image = $baseUri . '/media/com_eshop/products/resized/' . $image;
					}

					//Prepare option data here
					$optionData  = [];
					$optionPrice = 0;

					foreach ($options as $productOptionId => $optionValue)
					{
						$query->clear()
							->select('po.id, po.option_id, o.option_type, od.option_name')
							->from('#__eshop_productoptions AS po')
							->innerJoin('#__eshop_options AS o ON (po.option_id = o.id)')
							->innerJoin('#__eshop_optiondetails AS od ON (o.id = od.option_id)')
							->where('po.id = ' . intval($productOptionId))
							->where('po.product_id = ' . intval($row->id))
							->where('od.language = "' . Factory::getLanguage()->getTag() . '"');
						$db->setQuery($query);
						$optionRow = $db->loadObject();

						if (is_object($optionRow))
						{
							if ($optionRow->option_type == 'Select' || $optionRow->option_type == 'Radio')
							{
								$query->clear()
									->select(
										'pov.option_value_id, pov.sku, pov.quantity, pov.price, pov.price_sign, pov.price_type, pov.weight, pov.weight_sign, pov.shipping, ovd.value'
									)
									->from('#__eshop_productoptionvalues AS pov')
									->innerJoin('#__eshop_optionvalues AS ov ON (pov.option_value_id = ov.id)')
									->innerJoin('#__eshop_optionvaluedetails AS ovd ON (ov.id = ovd.optionvalue_id)')
									->where('pov.product_option_id = ' . intval($productOptionId))
									->where('pov.id = ' . intval($optionValue))
									->where('ovd.language = "' . Factory::getLanguage()->getTag() . '"');
								$db->setQuery($query);
								$optionValueRow = $db->loadObject();

								if (is_object($optionValueRow))
								{
									//Calculate option price
									if ($optionValueRow->price_sign == '+')
									{
										if ($optionValueRow->price_type == 'P')
										{
											$optionPrice += $price * $optionValueRow->price / 100;
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
											$optionPrice -= $price * $optionValueRow->price / 100;
										}
										else
										{
											$optionPrice -= $optionValueRow->price;
										}
									}

									$optionData[] = [
										'product_option_id'       => $productOptionId,
										'product_option_value_id' => $optionValue,
										'option_id'               => $optionRow->option_id,
										'option_name'             => $optionRow->option_name,
										'option_type'             => $optionRow->option_type,
										'option_value_id'         => $optionValueRow->option_value_id,
										'option_value'            => $optionValueRow->value,
										'sku'                     => $optionValueRow->sku,
										'quantity'                => $optionValueRow->quantity,
										'price'                   => $optionValueRow->price,
										'price_sign'              => $optionValueRow->price_sign,
									];
								}
							}
							elseif ($optionRow->option_type == 'Checkbox')
							{
								foreach ($optionValue as $productOptionValueId)
								{
									$query->clear()
										->select(
											'pov.option_value_id, pov.sku, pov.quantity, pov.price, pov.price_sign, pov.price_type, pov.weight, pov.weight_sign, pov.shipping, ovd.value'
										)
										->from('#__eshop_productoptionvalues AS pov')
										->innerJoin('#__eshop_optionvalues AS ov ON (pov.option_value_id = ov.id)')
										->innerJoin('#__eshop_optionvaluedetails AS ovd ON (ov.id = ovd.optionvalue_id)')
										->where('pov.product_option_id = ' . intval($productOptionId))
										->where('pov.id = ' . intval($productOptionValueId))
										->where('ovd.language = "' . Factory::getLanguage()->getTag() . '"');
									$db->setQuery($query);
									$optionValueRow = $db->loadObject();

									if (is_object($optionValueRow))
									{
										//Calculate option price
										if ($optionValueRow->price_sign == '+')
										{
											if ($optionValueRow->price_type == 'P')
											{
												$optionPrice += $price * $optionValueRow->price / 100;
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
												$optionPrice -= $price * $optionValueRow->price / 100;
											}
											else
											{
												$optionPrice -= $optionValueRow->price;
											}
										}

										$optionData[] = [
											'product_option_id'       => $productOptionId,
											'product_option_value_id' => $productOptionValueId,
											'option_id'               => $optionRow->option_id,
											'option_name'             => $optionRow->option_name,
											'option_type'             => $optionRow->option_type,
											'option_value_id'         => $optionValueRow->option_value_id,
											'option_value'            => $optionValueRow->value,
											'sku'                     => $optionValueRow->sku,
											'quantity'                => $optionValueRow->quantity,
											'price'                   => $optionValueRow->price,
											'price_sign'              => $optionValueRow->price_sign,
										];
									}
								}
							}
							elseif ($optionRow->option_type == 'Text' || $optionRow->option_type == 'Textarea')
							{
								$query->clear()
									->select('*')
									->from('#__eshop_productoptionvalues')
									->where('product_option_id = ' . intval($productOptionId))
									->where('product_id = ' . intval($row->id))
									->where('option_id = ' . $optionRow->option_id);
								$db->setQuery($query);
								$optionValueRow = $db->loadObject();

								//Calculate option price
								if ($optionValueRow->price_sign == '+')
								{
									if ($optionValueRow->price_type == 'P')
									{
										$optionPrice += ($price * $optionValueRow->price / 100) * strlen($optionValue);
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
										$optionPrice -= ($price * $optionValueRow->price / 100) * strlen($optionValue);
									}
									else
									{
										$optionPrice -= $optionValueRow->price * strlen($optionValue);
									}
								}

								$optionData[] = [
									'product_option_id'       => $productOptionId,
									'product_option_value_id' => $optionValueRow->id,
									'option_id'               => $optionRow->option_id,
									'option_name'             => $optionRow->option_name,
									'option_type'             => $optionRow->option_type,
									'option_value_id'         => $optionValueRow->option_value_id,
									'option_value'            => $optionValue,
									'quantity'                => $optionValueRow->quantity,
									'price'                   => $optionValueRow->price,
									'price_sign'              => $optionValueRow->price_sign,
									'weight'                  => '',
									'weight_sign'             => '',
								];
							}
							elseif ($optionRow->option_type == 'File' || $optionRow->option_type == 'Date' || $optionRow->option_type == 'Datetime')
							{
								$optionData[] = [
									'product_option_id'       => $productOptionId,
									'product_option_value_id' => '',
									'option_id'               => $optionRow->option_id,
									'option_name'             => $optionRow->option_name,
									'option_type'             => $optionRow->option_type,
									'option_value_id'         => '',
									'option_value'            => $optionValue,
									'quantity'                => '',
									'price'                   => '',
									'price_sign'              => '',
									'weight'                  => '',
									'weight_sign'             => '',
								];
							}
						}
					}

					$price = $row->product_price;

					//Check discount price
					$discountQuantity = 0;

					foreach ($quote as $key2 => $quantity2)
					{
						$product2 = explode(':', $key2);

						if ($product2[0] == $productId)
						{
							$discountQuantity += $quantity2;
						}
					}

					$query->clear()
						->select('price')
						->from('#__eshop_productdiscounts')
						->where('product_id = ' . intval($productId))
						->where('customergroup_id = ' . intval($customerGroupId))
						->where('quantity <= ' . intval($discountQuantity))
						->where('(date_start = ' . $nullDate . ' OR date_start IS NULL OR date_start <= ' . $currentDate . ')')
						->where('(date_end = ' . $nullDate . ' OR date_end IS NULL OR date_end >= ' . $currentDate . ')')
						->where('published = 1')
						->order('quantity DESC, priority ASC, price ASC LIMIT 1');
					$db->setQuery($query);

					if ($db->loadResult() > 0)
					{
						$price = $db->loadResult();
					}

					//Check special price
					$query->clear()
						->select('price')
						->from('#__eshop_productspecials')
						->where('product_id = ' . intval($productId))
						->where('customergroup_id = ' . intval($customerGroupId))
						->where('(date_start = ' . $nullDate . ' OR date_start IS NULL OR date_start <= ' . $currentDate . ')')
						->where('(date_end = ' . $nullDate . ' OR date_end IS NULL OR date_end >= ' . $currentDate . ')')
						->where('published = 1')
						->order('priority ASC, price ASC LIMIT 1');
					$db->setQuery($query);

					if ($db->loadResult() > 0)
					{
						$price = $db->loadResult();
					}

					$this->quoteData[$key] = [
						'key'                    => $key,
						'product_id'             => $row->id,
						'product_name'           => $row->product_name,
						'product_sku'            => $row->product_sku,
						'image'                  => $image,
						'product_price'          => $price,
						'option_price'           => $optionPrice,
						'price'                  => $price + $optionPrice,
						'total_price'            => ($price + $optionPrice) * $quantity,
						'product_call_for_price' => $row->product_call_for_price,
						'product_taxclass_id'	 => $row->product_taxclass_id,
						'quantity'               => $quantity,
						'option_data'            => $optionData,
						'params'                 => $row->params,
					];
				}
				else
				{
					$this->remove($key);
				}
			}
		}

		return $this->quoteData;
	}

	/**
	 *
	 * Function to add a product to the quote
	 *
	 * @param   int    $productId
	 * @param   int    $quantity
	 * @param   array  $options
	 */
	public function add($productId, $quantity = 1, $options = [])
	{
		if (!count($options))
		{
			$key = $productId;
		}
		else
		{
			$key = $productId . ':' . base64_encode(serialize($options));
		}

		if ($quantity > 0)
		{
			if (!isset($this->quote[$key]))
			{
				$this->quote[$key] = $quantity;
			}
			else
			{
				$this->quote[$key] += $quantity;
			}
		}

		Factory::getApplication()->getSession()->set('quote', $this->quote);
	}

	/**
	 *
	 * Function to update a product in the quote
	 *
	 * @param   string  $key
	 * @param   int     $quantity
	 */
	public function update($key, $quantity)
	{
		if ($quantity > 0)
		{
			$this->quote[$key] = $quantity;
			Factory::getApplication()->getSession()->set('quote', $this->quote);
		}
		else
		{
			$this->remove($key);
		}
	}

	/**
	 *
	 * Function to update all products in the quote
	 *
	 * @param   array  $key
	 * @param   array  $quantity
	 */
	public function updates($key, $quantity)
	{
		$session = Factory::getApplication()->getSession();

		for ($i = 0; $n = count($key), $i < $n; $i++)
		{
			if ($quantity[$i] > 0)
			{
				$this->quote[$key[$i]] = $quantity[$i];
				$session->set('quote', $this->quote);
			}
			else
			{
				$this->remove($key[$i]);
			}
		}
	}

	/**
	 *
	 * Function to remove a quote element based on key
	 *
	 * @param   string  $key
	 */
	public function remove($key)
	{
		if (isset($this->quote[$key]))
		{
			unset($this->quote[$key]);
		}

		Factory::getApplication()->getSession()->set('quote', $this->quote);
	}

	/**
	 *
	 * Function to clear the quote
	 */
	public function clear()
	{
		$this->quote = [];
		Factory::getApplication()->getSession()->set('quote', $this->quote);
	}

	/**
	 *
	 * Function to count products in the quote
	 * @return int
	 */
	public function countProducts()
	{
		$countProducts = 0;

		foreach ($this->getQuoteData() as $product)
		{
			$countProducts += $product['quantity'];
		}

		return $countProducts;
	}

	/**
	 *
	 * Function to check if the quote has products or not
	 */
	public function hasProducts()
	{
		return count(Factory::getApplication()->getSession()->get('quote'));
	}
	
	/**
	 *
	 * Function to get sub total from the quote
	 */
	public function getSubTotal()
	{
		$subTotal = 0;
	
		foreach ($this->getQuoteData() as $product)
		{
			$subTotal += $product['total_price'];
		}
	
		return $subTotal;
	}
	
	/**
	 *
	 * Function to get taxes of current quote data
	 */
	public function getTaxes()
	{
		$tax       = new EShopTax(EShopHelper::getConfig());
		$taxesData = [];
	
		foreach ($this->getQuoteData() as $product)
		{
			if ($product['product_taxclass_id'])
			{
				$taxRates = $tax->getTaxRates($product['price'], $product['product_taxclass_id']);
	
				foreach ($taxRates as $taxRate)
				{
					if (!isset($taxesData[$taxRate['tax_rate_id']]))
					{
						$taxesData[$taxRate['tax_rate_id']] = ($taxRate['amount'] * $product['quantity']);
					}
					else
					{
						$taxesData[$taxRate['tax_rate_id']] += ($taxRate['amount'] * $product['quantity']);
					}
				}
			}
		}
	
		return $taxesData;
	}
}