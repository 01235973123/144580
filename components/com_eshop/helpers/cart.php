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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;

class EShopCart
{
	/**
	 * Cart id
	 *
	 * @var int
	 */
	protected $cart_id;

	/**
	 * Session cart data
	 *
	 * @var array
	 */
	protected $cart;

	/**
	 *
	 * Entity cart data
	 * @var array
	 */
	protected $cartData;

	/**
	 * Constructor function
	 */
	public function __construct()
	{
		$this->cart_id  = 0;
		$this->cart     = Factory::getApplication()->getSession()->get('cart');
		$this->cartData = [];
		$db             = Factory::getDbo();
		$query          = $db->getQuery(true);
		$user           = Factory::getUser();

		if ($user->get('id') > 0)
		{
			$query->select('id')
				->from('#__eshop_carts')
				->where('customer_id = ' . intval($user->get('id')));
			$db->setQuery($query);
			$cartId = $db->loadResult();

			if ($cartId > 0)
			{
				$this->cart_id = $cartId;
			}
		}
	}

	/**
	 *
	 * Function to get data in the cart
	 */
	public function getCartData()
	{
		$cart            = $this->cart;
		$db              = Factory::getDbo();
		$query           = $db->getQuery(true);
		$nullDate        = $db->quote($db->getNullDate());
		$currentDate     = $db->quote(EShopHelper::getServerTimeFromGMTTime());
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		if (!$this->cartData && !empty($cart))
		{
			$baseUri = Uri::base(true);
			foreach ($cart as $key => $quantity)
			{
				$keyArr    = explode(':', $key);
				$productId = $keyArr[0];
				$stock     = true;

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
					->select('a.*, b.product_name, b.product_alias, b.product_desc, b.product_short_desc, b.meta_key, b.meta_desc, b.product_custom_message')
					->from('#__eshop_products AS a')
					->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
					->where('a.id = ' . intval($productId))
					->where('a.published = 1')
					->where('b.language = "' . Factory::getLanguage()->getTag() . '"');
				$db->setQuery($query);
				$row = $db->loadObject();

				if (is_object($row))
				{
					// Image
					$imageSizeFunction = EShopHelper::getConfigValue('cart_image_size_function', 'resizeImage');

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
					$price = $row->product_price;
					//Prepare option data here
					$optionData   = [];
					$optionPrice  = 0;
					$optionWeight = 0;

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

									//Calculate option weight
									if ($optionValueRow->weight_sign == '+')
									{
										$optionWeight += $optionValueRow->weight;
									}
									elseif ($optionValueRow->weight_sign == '-')
									{
										$optionWeight -= $optionValueRow->weight;
									}

									if (!$optionValueRow->quantity || $optionValueRow->quantity < $quantity)
									{
										$stock = false;
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
										'price'                   => ($optionValueRow->price_sign == '+' ? $optionValueRow->price : $optionValueRow->price * -1),
										'weight'                  => $optionValueRow->weight,
										'weight_sign'             => $optionValueRow->weight_sign,
										'shipping'                => $optionValueRow->shipping,
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

										//Calculate option weight
										if ($optionValueRow->weight_sign == '+')
										{
											$optionWeight += $optionValueRow->weight;
										}
										elseif ($optionValueRow->weight_sign == '-')
										{
											$optionWeight -= $optionValueRow->weight;
										}

										if (!$optionValueRow->quantity || $optionValueRow->quantity < $quantity)
										{
											$stock = false;
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
											'weight'                  => $optionValueRow->weight,
											'weight_sign'             => $optionValueRow->weight_sign,
											'shipping'                => $optionValueRow->shipping,
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
									'weight'                  => '',
									'weight_sign'             => '',
									'shipping'                => '1',
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
									'weight'                  => '',
									'weight_sign'             => '',
									'shipping'                => '1',
								];
							}
						}
					}

					$optionPrice = EShopHelper::getOptionDiscountPrice($productId, $optionPrice);

					if (!$row->product_quantity || $row->product_quantity < $quantity)
					{
						$stock = false;
					}

					//Check discount price
					$discountQuantity = 0;

					foreach ($cart as $key2 => $quantity2)
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

					if ($db->loadResult())
					{
						$price = $db->loadResult();
					}

					// First, check if there is a special price for the product or not. Special Price has highest priority
					$specialPrice = EShopHelper::getSpecialPrice($productId, $price);

					if ($specialPrice >= 0)
					{
						$price = $specialPrice;
					}

					//Prepare download data here
					$downloadData = [];
					$downloads    = EShopHelper::getProductDownloads($productId);

					foreach ($downloads as $download)
					{
						$downloadData[] = [
							'id'                      => $download->id,
							'download_name'           => $download->download_name,
							'filename'                => $download->filename,
							'total_downloads_allowed' => $download->total_downloads_allowed,
						];
					}

					$productInventory = EShopHelper::getProductInventory($row->id);

					$this->cartData[$key] = [
						'key'                            => $key,
						'product_id'                     => $row->id,
						'product_name'                   => $row->product_name,
						'product_custom_message'         => $row->product_custom_message,
						'product_sku'                    => $row->product_sku,
						'product_shipping'               => $row->product_shipping,
						'product_shipping_cost'          => $row->product_shipping_cost,
						'product_shipping_cost_geozones' => $row->product_shipping_cost_geozones,
						'image'                          => $image,
						'product_price'                  => $price,
						'option_price'                   => $optionPrice,
						'stock'                          => $stock,
						'product_stock_warning'          => $productInventory['product_stock_warning'],
						'price'                          => $price + $optionPrice,
						'total_price'                    => ($price + $optionPrice) * $quantity,
						'product_weight'                 => $row->product_weight,
						'option_weight'                  => $optionWeight,
						'weight'                         => $row->product_weight + $optionWeight,
						'product_weight_id'              => $row->product_weight_id,
						'total_weight'                   => ($row->product_weight + $optionWeight) * $quantity,
						'product_taxclass_id'            => $row->product_taxclass_id,
						'product_length'                 => $row->product_length,
						'product_width'                  => $row->product_width,
						'product_height'                 => $row->product_height,
						'product_length_id'              => $row->product_length_id,
						'quantity'                       => $quantity,
						'product_stock_checkout'         => $productInventory['product_stock_checkout'],
						'minimum_quantity'               => $row->product_minimum_quantity,
						'maximum_quantity'               => $row->product_maximum_quantity,
						'download_data'                  => $downloadData,
						'option_data'                    => $optionData,
						'params'                         => $row->params,
					];
				}
				else
				{
					$this->remove($key);
				}
			}
		}

		return $this->cartData;
	}

	/**
	 *
	 * Function to add a product to the cart
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
			if (!isset($this->cart[$key]))
			{
				$this->cart[$key] = $quantity;
			}
			else
			{
				$this->cart[$key] += $quantity;
			}
		}

		$this->storeCart();
		Factory::getApplication()->getSession()->set('cart', $this->cart);
	}

	/**
	 *
	 * Function to update a product in the cart
	 *
	 * @param   string  $key
	 * @param   int     $quantity
	 */
	public function update($key, $quantity)
	{
		if ($quantity > 0)
		{
			$this->cart[$key] = $quantity;

			$this->storeCart();
			Factory::getApplication()->getSession()->set('cart', $this->cart);
		}
		else
		{
			$this->remove($key);
		}
	}

	/**
	 *
	 * Function to update quantities of products in the cart
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
				$this->cart[$key[$i]] = $quantity[$i];
				$this->storeCart();
				$session->set('cart', $this->cart);
			}
			else
			{
				$this->remove($key[$i]);
			}
		}
	}

	/**
	 *
	 * Function to remove a cart element based on key
	 *
	 * @param   string  $key
	 */
	public function remove($key)
	{
		if (isset($this->cart[$key]))
		{
			unset($this->cart[$key]);
		}

		$this->storeCart();
		Factory::getApplication()->getSession()->set('cart', $this->cart);
	}

	/**
	 *
	 * Function to clear the cart
	 */
	public function clear()
	{
		$this->cart = [];
		$this->clearCart();
		Factory::getApplication()->getSession()->set('cart', $this->cart);
	}

	/**
	 *
	 * Function to get sub total from the cart
	 */
	public function getSubTotal($requireShipping = 0)
	{
		$subTotal = 0;

		foreach ($this->getCartData() as $product)
		{
			if (!$requireShipping)
			{
				$subTotal += $product['total_price'];
			}
			else
			{
				if ($product['product_shipping'])
				{
					$optionData = $product['option_data'];

					if (count($optionData))
					{
						for ($i = 0; $n = count($optionData), $i < $n; $i++)
						{
							if ($optionData[$i]['shipping'])
							{
								$subTotal += $product['total_price'];
								break;
							}
						}
					}
					else
					{
						$subTotal += $product['total_price'];
					}
				}
			}
		}

		return $subTotal;
	}

	/**
	 *
	 * Function to get taxes of current cart data
	 */
	public function getTaxes()
	{
		$tax       = new EShopTax(EShopHelper::getConfig());
		$taxesData = [];

		foreach ($this->getCartData() as $product)
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

	/**
	 *
	 * Function to get total weight of the products in the cart
	 * @return float
	 */
	public function getWeight()
	{
		$eshopWeight = EShopWeight::getInstance();
		$weight      = 0;
		$weightId    = EShopHelper::getConfigValue('weight_id');

		foreach ($this->getCartData() as $product)
		{
			if ($product['product_shipping'])
			{
				$optionData = $product['option_data'];

				if (count($optionData))
				{
					for ($i = 0; $n = count($optionData), $i < $n; $i++)
					{
						if ($optionData[$i]['shipping'])
						{
							$weight += $eshopWeight->convert($product['total_weight'], $product['product_weight_id'], $weightId);
							break;
						}
					}
				}
				else
				{
					$weight += $eshopWeight->convert($product['total_weight'], $product['product_weight_id'], $weightId);
				}
			}
		}

		return $weight;
	}

	/**
	 *
	 * Function to get Total
	 */
	public function getTotal()
	{
		$total     = 0;
		$tax       = new EShopTax(EShopHelper::getConfig());
		$enableTax = EShopHelper::getConfigValue('tax');

		foreach ($this->getCartData() as $product)
		{
			$total += $tax->calculate($product['total_price'], $product['product_taxclass_id'], $enableTax);
		}

		return $total;
	}

	/**
	 *
	 * Function to count products in the cart
	 * @return int
	 */
	public function countProducts($requireShipping = false)
	{
		$countProducts = 0;

		foreach ($this->getCartData() as $product)
		{
			if (!$requireShipping)
			{
				$countProducts += $product['quantity'];
			}
			else
			{
				if ($product['product_shipping'])
				{
					$optionData = $product['option_data'];

					if (count($optionData))
					{
						for ($i = 0; $n = count($optionData), $i < $n; $i++)
						{
							if ($optionData[$i]['shipping'])
							{
								$countProducts += $product['quantity'];
								break;
							}
						}
					}
					else
					{
						$countProducts += $product['quantity'];
					}
				}
			}
		}

		return $countProducts;
	}

	/**
	 *
	 * Function to check if the cart has products or not
	 */
	public function hasProducts()
	{
		if (empty($this->cart))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 *
	 * Function to get stock warning
	 * @return string
	 */
	public function getStockWarning()
	{
		$warning = '';

		$stock = true;

		foreach ($this->getCartData() as $product)
		{
			if ($product['product_stock_warning'] && !$product['stock'])
			{
				$stock = false;
				break;
			}
		}

		if (!$stock)
		{
			$warning = Text::_('ESHOP_CART_STOCK_WARNING');
		}

		return $warning;
	}

	/**
	 *
	 * Function to check if shopper can go to checkout or not based on stock
	 */
	public function canCheckout()
	{
		$canCheckout = true;

		foreach ($this->getCartData() as $product)
		{
			if (!$product['stock'] && !$product['product_stock_checkout'])
			{
				$canCheckout = false;
				break;
			}
		}

		return $canCheckout;
	}

	/**
	 *
	 * Function to get minimum sub total warning
	 * @return string
	 */
	public function getMinSubTotalWarning()
	{
		$currency = EShopCurrency::getInstance();
		$warning  = '';

		if (EShopHelper::getConfigValue('min_sub_total') > 0 && $this->getSubTotal() < EShopHelper::getConfigValue('min_sub_total'))
		{
			$warning = sprintf(Text::_('ESHOP_MIN_SUB_TOTAL_NOT_REACH'), $currency->format(EShopHelper::getConfigValue('min_sub_total')));
		}

		return $warning;
	}

	/**
	 *
	 * Function to get minimum quantity warning
	 * @return string
	 */
	public function getMinQuantityWarning()
	{
		$warning = '';

		if (EShopHelper::getConfigValue('min_quantity') > 0 && $this->countProducts() < EShopHelper::getConfigValue('min_quantity'))
		{
			$warning = sprintf(Text::_('ESHOP_MIN_QUANTITY_NOT_REACH'), EShopHelper::getConfigValue('min_quantity'));
		}

		return $warning;
	}

	/**
	 *
	 * Function to get minimum product quantity warning
	 * @return string
	 */
	public function getMinProductQuantityWarning()
	{
		$warning = '';
		
		$productData = self::getProductDataInCart();

		foreach ($productData as $product)
		{
			if ($product['minimum_quantity'] > 0 && $product['quantity'] < $product['minimum_quantity'])
			{
				$warning = sprintf(Text::_('ESHOP_MIN_PRODUCT_QUANTITY_NOT_REACH'), $product['product_name'], $product['minimum_quantity']);
				break;
			}
		}

		return $warning;
	}

	/**
	 *
	 * Function to get maximum product quantity warning
	 * @return string
	 */
	public function getMaxProductQuantityWarning()
	{
		$warning = '';
		
		$productData = self::getProductDataInCart();

		foreach ($productData as $product)
		{
			if ($product['maximum_quantity'] > 0 && $product['quantity'] > $product['maximum_quantity'])
			{
				$warning = sprintf(Text::_('ESHOP_MAX_PRODUCT_QUANTITY_EXCEED'), $product['product_name'], $product['maximum_quantity']);
				break;
			}
		}

		return $warning;
	}
	
	/**
	 * 
	 * Function to get real product data in cart based on product sku
	 */
	public function getProductDataInCart()
	{
		$productData = [];
		
		foreach ($this->getCartData() as $product)
		{
			$productData[$product['product_sku']]['product_name'] = $product['product_name'];
			$productData[$product['product_sku']]['minimum_quantity'] = $product['minimum_quantity'];
			$productData[$product['product_sku']]['maximum_quantity'] = $product['maximum_quantity'];
			
			if (isset($productData[$product['product_sku']]))
			{
				$productData[$product['product_sku']]['quantity'] += $product['quantity'];
			}
			else 
			{
				$productData[$product['product_sku']]['quantity'] = $product['quantity'];
			}
		}
		
		return $productData;
	}

	/**
	 *
	 * Function to check if products in the cart has shipping or not
	 */
	public function hasShipping()
	{
		if (!EShopHelper::getConfigValue('require_shipping', '1'))
		{
			return false;
		}

		$shipping = false;

		foreach ($this->getCartData() as $product)
		{
			$optionData = $product['option_data'];

			if ($product['product_shipping'])
			{
				if (count($optionData))
				{
					for ($i = 0; $n = count($optionData), $i < $n; $i++)
					{
						if ($optionData[$i]['shipping'])
						{
							$shipping = true;
							break 2;
						}
					}
				}
				else
				{
					$shipping = true;
				}
			}
		}

		return $shipping;
	}

	/**
	 *
	 * Function to check if product in the cart has download or not
	 * @return boolean
	 */
	public function hasDownload()
	{
		$download = false;

		foreach ($this->getCartData() as $product)
		{
			if ($product['download_data'])
			{
				$download = true;
				break;
			}
		}

		return $download;
	}

	/**
	 *
	 * Function to store cart into database
	 */
	public function storeCart()
	{
		$user = Factory::getUser();

		if (EShopHelper::getConfigValue('store_cart', 0) && $user->get('id') > 0)
		{
			if (empty($this->cart))
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true);

				$query->clear()
					->delete('#__eshop_carts')
					->where('customer_id = ' . intval($user->get('id')));
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_eshop/tables');

				$row                = Table::getInstance('Eshop', 'Cart');
				$row->id            = $this->cart_id;
				$row->customer_id   = $user->get('id');
				$row->cart_data     = json_encode($this->cart);
				$row->is_1st_sent   = 0;
				$row->is_2nd_sent   = 0;
				$row->is_3rd_sent   = 0;
				$row->created_date  = gmdate('Y-m-d H:i:s');
				$row->modified_date = gmdate('Y-m-d H:i:s');
				$row->store();
			}
		}
	}

	/**
	 *
	 * Function to clear cart of customer from the database
	 */
	public function clearCart()
	{
		$user = Factory::getUser();

		if ($user->get('id') > 0)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->delete('#__eshop_carts')
				->where('customer_id = ' . intval($user->get('id')));
			$db->setQuery($query);
			$db->execute();
		}
	}
}