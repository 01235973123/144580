<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop - Auspost Shipping
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class eshop_auspostpac extends eshop_shipping
{
	const MIN_LENGTH = 5;
	const MIN_GIRTH = 15;
	const MIN_WEIGHT = 0.1;

	const MAX_PACKAGE_DOM_WEIGHT = 22;
	const MAX_PACKAGE_INT_WEIGHT = 20;
	const MAX_PACKAGE_LENGTH = 105;
	const MAX_PACKAGE_GIRTH = 140;
	const MAX_CUBIC_METRES = 0.25;

	const MAX_EXTRA_COVER = 5000;

	private $is_domestic;
	private $region;
	private $address;

	private $errors = [];
	private $packages = [];
	private $quote_data = [];

	/**
	 *
	 * Constructor function
	 */
	public function __construct()
	{
		parent::setName('eshop_auspostpac');
		parent::__construct();
	}

	/**
	 *
	 * Function tet get quote for auspost shipping
	 *
	 * @param   array   $addressData
	 * @param   object  $params
	 */
	public function getQuote($addressData, $params)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		if (!$params->get('geozone_id'))
		{
			$status = true;
		}
		else
		{
			$query->select('COUNT(*)')
				->from('#__eshop_geozonezones')
				->where('geozone_id = ' . intval($params->get('geozone_id')))
				->where('country_id = ' . intval($addressData['country_id']))
				->where('(zone_id = 0 OR zone_id = ' . intval($addressData['zone_id']) . ')');
			$db->setQuery($query);
			if ($db->loadResult())
			{
				$status = true;
			}
			else
			{
				$status = false;
			}

			//Check geozone postcode status
			if ($status)
			{
				$gzpStatus = EShopHelper::getGzpStatus($params->get('geozone_id'), $addressData['postcode']);

				if (!$gzpStatus)
				{
					$status = false;
				}
			}
		}
		if ($status)
		{
			$total_weight = $this->getTotalWeight();
			$min_weight   = ($params->get('auspost_pac_min_weight') > 0 ? $params->get('auspost_pac_min_weight') : false);
			$max_weight   = ($params->get('auspost_pac_max_weight') > 0 ? $params->get('auspost_pac_max_weight') : false);

			if (($min_weight !== false && $total_weight < $min_weight)
				|| ($max_weight !== false && $total_weight > $max_weight))
			{
				$status = false;
			}
		}

		$cart     = new EShopCart();
		$total    = $cart->getTotal();
		$minTotal = $params->get('min_total', 0);

		if ($minTotal > 0 && $total >= $minTotal)
		{
			$status = false;
		}

		$quantity    = $cart->countProducts(true);
		$minQuantity = $params->get('min_quantity', 0);

		if ($minQuantity > 0 && $quantity >= $minQuantity)
		{
			$status = false;
		}
		
		$weight = $cart->getWeight();
		$minWeight = $params->get('min_weight', 0);
			
		if ($minWeight > 0 && $weight >= $minWeight)
		{
			$status = false;
		}

		$this->is_domestic = ($addressData['iso_code_2'] == 'AU');

		$this->generatePackages($params);
		//print_r($this->packages);
		$method_data = [];
		if ($status && empty($this->errors))
		{
			$this->region = ($this->is_domestic ? 'domestic' : 'international');

			$this->getParcels($addressData, $params);

			if (count($this->packages) == 1 && $this->hasSatchels($params))
			{
				$this->getSatchels($addressData, $params);
			}

			$this->sortShippingQuotes();
		}

		if ($status || !empty($this->errors))
		{
			$query->clear();
			$query->select('*')
				->from('#__eshop_shippings')
				->where('name = "eshop_auspostpac"');
			$db->setQuery($query);
			$row         = $db->loadObject();
			$method_data = [
				'name'     => 'eshop_auspostpac',
				'title'    => Text::_('PLG_ESHOP_AUSPOSTPAC_TITLE'),
				'quote'    => $this->quote_data,
				'ordering' => $row->ordering,
				'error'    => implode("<br />\n", $this->errors),
			];
		}

		return $method_data;
	}

	/**
	 *
	 * Function to get parcels
	 */
	public function getParcels($addressData, $params)
	{
		$services = [];
		foreach ($this->packages as $package)
		{
			$info = [];
			if ($this->is_domestic)
			{
				$info = [
					'from_postcode' => $params->get('auspost_pac_origin_postcode'),
					'to_postcode'   => $addressData['postcode'],
					'length'        => $package->length,
					'width'         => $package->width,
					'height'        => $package->height,
					'weight'        => $package->weight,
					'cost'          => $package->cost,
				];
			}
			else
			{
				$info = [
					'country_code' => $addressData['iso_code_2'],
					'weight'       => $package->weight,
					'cost'         => $package->cost,
				];
			}
			$data = $this->call('service', ['parcel', $this->region], $info, 'json', $params);
			if (isset($data->error))
			{
				$this->errors[] = ucfirst(strtolower($data->error->errorMessage));
			}
			elseif (isset($data->services->service))
			{
				$service_costs = $this->getServiceCosts($data, $info, 'isNotSatchel', $params);
				foreach ($service_costs as $service)
				{
					if (!isset($services[$service->id]))
					{
						$services[$service->id] = (object) [
							'id'        => $service->id,
							'count'     => 0,
							'code'      => $service->code,
							'option'    => ($service->option ?? ''),
							'suboption' => ($service->suboption ?? ''),
							'name'      => $service->name,
							'price'     => 0,
							'time'      => ($service->time ?? ''),
							'info'      => $info,
							'packages'  => [],
						];
					}
					$services[$service->id]->count++;
					$services[$service->id]->price      += $service->price;
					$services[$service->id]->packages[] =& $package;
				}
			}
		}
		// Add shipping services that are available for all packages.
		foreach ($services as $service)
		{
			if (count($this->packages) == $service->count)
			{
				$this->addShippingQuote($service, $service->info, $params);
			}
		}
	}

	/**
	 *
	 * Function to get satchels
	 */
	public function getSatchels($addressData, $params)
	{
		if (!$this->is_domestic)
		{
			return;
		}
		$length = 5;
		$width  = 5;
		$height = 5;
		if ($this->packages[0]->length > $length)
		{
			$length = $this->packages[0]->length;
		}
		if ($this->packages[0]->width > $width)
		{
			$width = $this->packages[0]->width;
		}
		if ($this->packages[0]->height > $height)
		{
			$height = $this->packages[0]->height;
		}
		$info = [
			'from_postcode' => $params->get('auspost_pac_origin_postcode'),
			'to_postcode'   => $addressData['postcode'],
			'length'        => $length,
			'width'         => $width,
			'height'        => $height,
			'weight'        => $this->packages[0]->actual_weight,
			'cost'          => $this->packages[0]->cost,
		];
		$data = $this->call('service', ['parcel', $this->region], $info, 'json', $params);
		if (isset($data->error))
		{
			$this->errors[] = ucfirst(strtolower($data->error->errorMessage));
		}
		elseif (isset($data->services->service))
		{
			$services = $this->getServiceCosts($data, $info, 'isSatchel', $params);
			foreach ($services as $service)
			{
				$this->addShippingQuote($service, $info, $params);
			}
		}
	}

	public function sortByHeight($a, $b)
	{
		return $a['product_height'] - $b['product_height'];
	}

	/**
	 *
	 * Function to generate packages
	 */
	public function generatePackages($params)
	{
		$this->packages = [];
		$cart           = new EShopCart();
		$eshopLength    = EShopLength::getInstance();
		$eshopWeight    = EShopWeight::getInstance();
		$products       = $cart->getCartData();
		$items          = [];
		foreach ($products as $key => $product)
		{
			for ($i = 0; $i < $product['quantity']; $i++)
			{
				$items[] =& $products[$key];
			}
		}
		usort($items, function ($a, $b) {
			return $a['product_height'] - $b['product_height'];
		});
		if ($this->is_domestic)
		{
			$max_weight = self::MAX_PACKAGE_DOM_WEIGHT;
		}
		else
		{
			$max_weight = self::MAX_PACKAGE_INT_WEIGHT;
		}
		$count = count($items);
		for ($i = 0; $i < $count; $i++)
		{
			$width         = $eshopLength->convert($items[$i]['product_width'], $items[$i]['product_length_id'], 1);
			$height        = $eshopLength->convert($items[$i]['product_height'], $items[$i]['product_length_id'], 1);
			$length        = $eshopLength->convert($items[$i]['product_length'], $items[$i]['product_length_id'], 1);
			$actual_weight = $eshopWeight->convert($items[$i]['product_weight'], $items[$i]['product_weight_id'], 1);
			$cubic_metres  = ($length / 100) * ($width / 100) * ($height / 100);
			if ($actual_weight > $max_weight)
			{
				$this->errors[] = 'Unable to send using Australia Post. A product in your cart is over the maximum size of <strong>' . $max_weight . 'kg</strong>.';

				return;
			}

			if ($length > self::MAX_PACKAGE_LENGTH)
			{
				$this->errors[] = 'Unable to send using Australia Post. A product in your cart is over the maximum length of <strong>' . self::MAX_PACKAGE_LENGTH . 'cm</strong>.';

				return;
			}

			if ($this->is_domestic)
			{
				if ($cubic_metres > self::MAX_CUBIC_METRES)
				{
					$this->errors[] = 'Unable to send using Australia Post. A product in your cart is over the maximum size of <strong>' . self::MAX_CUBIC_METRES . ' cubic metres</strong>.';

					return;
				}
			}
			else
			{
				if (($width + $height) * 2 > self::MAX_PACKAGE_GIRTH)
				{
					$this->errors[] = 'Unable to send using Australia Post. A product in your cart is over the maximum girth of <strong>' . self::MAX_PACKAGE_GIRTH . 'cm</strong>.';

					return;
				}
			}
			//$cost = $items[$i]['total_price'];
			$cost = $items[$i]['price'];
			// Parcel volume in cubic metres (Cubic weight) = (L * W * H) * 250
			$cubic_weight = $cubic_metres * 250;
			for ($j = 0; $j < $count; $j++)
			{
				//If current package is not existed, then add the current item to the package as the first item immediately
				if (!isset($this->packages[$j]))
				{
					$this->packages[$j] = (object) [
						'max_weight'    => $max_weight,
						'weight'        => 0,
						'actual_weight' => $actual_weight,
						'cubic_weight'  => $cubic_weight,
						'width'         => $width,
						'length'        => $length,
						'height'        => $height,
						'cost'          => $cost,
					];
					break;
					// Continue to next item
				}
				//Else, we must check the conditions before adding current item to the package
				else
				{
					//Identify the minimum dimension
					$minValue = min($height, $width, $length);
					if ($minValue == $length)
					{
						$new_cubic_metres = (($this->packages[$j]->length + $length) / 100) * ($this->packages[$j]->width / 100) * ($this->packages[$j]->height / 100);
						$maxValue         = max($this->packages[$j]->length + $length, $this->packages[$j]->width, $this->packages[$j]->height);
						$minDimenstion    = 'length';
					}
					elseif ($minValue == $width)
					{
						$new_cubic_metres = ($this->packages[$j]->length / 100) * (($this->packages[$j]->width + $width) / 100) * ($this->packages[$j]->height / 100);
						$maxValue         = max($this->packages[$j]->length, $this->packages[$j]->width + $width, $this->packages[$j]->height);
						$minDimenstion    = 'width';
					}
					else
					{
						$new_cubic_metres = ($this->packages[$j]->length / 100) * ($this->packages[$j]->width / 100) * (($this->packages[$j]->height + $height) / 100);
						$maxValue         = max($this->packages[$j]->length, $this->packages[$j]->width, $this->packages[$j]->height + $height);
						$minDimenstion    = 'height';
					}
					if (($this->packages[$j]->actual_weight + $actual_weight <= $this->packages[$j]->max_weight) && ($maxValue <= 105) && ($new_cubic_metres <= self::MAX_CUBIC_METRES))
					{
						$this->packages[$j]->actual_weight += $actual_weight;
						$this->packages[$j]->cubic_weight  += $cubic_weight;
						if ($minValue == $length)
						{
							$this->packages[$j]->length += $length;
							$this->packages[$j]->height = $this->packages[$j]->height > $height ? $this->packages[$j]->height : $height;
							$this->packages[$j]->width  = $this->packages[$j]->width > $width ? $this->packages[$j]->width : $width;
						}
						elseif ($minValue == $width)
						{
							$this->packages[$j]->length = $this->packages[$j]->length > $length ? $this->packages[$j]->length : $length;
							$this->packages[$j]->height = $this->packages[$j]->height > $height ? $this->packages[$j]->height : $height;
							$this->packages[$j]->width  += $width;
						}
						else
						{
							$this->packages[$j]->length = $this->packages[$j]->length > $length ? $this->packages[$j]->length : $length;
							$this->packages[$j]->height += $height;
							$this->packages[$j]->width  = $this->packages[$j]->width > $width ? $this->packages[$j]->width : $width;
						}
						$this->packages[$j]->cost += $cost;
						break;
						// Continue to next item
					}
				}
			}
		}
		foreach ($this->packages as $key => $package)
		{
			if ($package->length < self::MIN_LENGTH)
			{
				$this->packages[$key]->length = self::MIN_LENGTH;
			}
			if ($package->height < self::MIN_LENGTH)
			{
				$this->packages[$key]->height = self::MIN_LENGTH;
			}
			if ($package->width < self::MIN_LENGTH)
			{
				$this->packages[$key]->width = self::MIN_LENGTH;
			}
			// Make sure the height & width are larger than 15cm in girth (width * height * 2 >= 15).
			if (2 * ($package->height + $package->width) < self::MIN_GIRTH)
			{
				$this->packages[$key]->length = self::MIN_LENGTH;
				$this->packages[$key]->height = self::MIN_LENGTH;
				$this->packages[$key]->width  = self::MIN_LENGTH;
			}
			$this->packages[$key]->weight = $package->actual_weight;
			if ($package->actual_weight < self::MIN_WEIGHT)
			{
				$this->packages[$key]->weight = self::MIN_WEIGHT;
			}
		}
		if (!$params->get('auspost_pac_multiple_packages') && count($this->packages) > 1)
		{
			$this->errors[] = 'Unable to send using Australia Post. The contents of your cart is too large to send in a single parcel.';

			return;
		}

		return;
	}

	public function getServiceCosts($data, $info, $check = false, $params)
	{
		$available = $this->getAvailableServices($data, $info, $check, $params);
		$costs     = [];
		foreach ($available as $service)
		{
			if (empty($service->price))
			{
				$query                 = $info;
				$query['service_code'] = $service->code;
				if (isset($service->option))
				{
					$query['option_code'] = $service->option;
				}
				if (isset($service->suboption))
				{
					$query['suboption_code'] = $service->suboption;
				}
				if (isset($service->extra_cover))
				{
					$query['extra_cover'] = $service->extra_cover;
				}
				$result = $this->call('calculate', ['parcel', $this->region], $query, 'json', $params);
				if (!isset($result->postage_result))
				{
					continue;
				}
				$name_extras = [];
				if (isset($result->postage_result->costs->cost) && is_array($result->postage_result->costs->cost))
				{
					$i = 0;
					foreach ($result->postage_result->costs->cost as $cost)
					{
						if ($i++ == 0)
						{
							continue;
						}
						$name_extras[] = $cost->item;
					}
				}
				$name_extra = '';
				if (!empty($name_extras))
				{
					$name_extra = ' (' . implode(', ', $name_extras) . ')';
				}
				$cost = (object) [
					'id'        => $service->code . (isset($service->option) ? '_' . $service->option : ''),
					'name'      => $result->postage_result->service . $name_extra,
					'code'      => $service->code,
					'option'    => ($service->option ?? ''),
					'suboption' => ($service->suboption ?? ''),
					'price'     => $result->postage_result->total_cost,
				];
				$time = '';
				if (isset($result->postage_result->delivery_time))
				{
					$cost->time = $this->parseShippingTime($result->postage_result->delivery_time);
				}
				$costs[] = $cost;
			}
			else
			{
				$service->id = $service->code;
				$costs[]     = $service;
			}
		}

		return $costs;
	}

	public function getAvailableServices($data, $info, $check = false, $params)
	{
		$active = $params->get('auspost_pac_services_' . $this->region);
		if (!is_array($active) || empty($active))
		{
			return [];
		}
		$services = [];
		foreach ($data->services->service as $service_node)
		{
			if (is_callable([$this, $check]) && !$this->{$check}($service_node->code))
			{
				continue;
			}
			if (in_array($service_node->code, $active))
			{
				if ($this->is_domestic)
				{
					if (isset($service_node->options->option))
					{
						$options = $service_node->options->option;
						$options = (is_array($options) ? $options : [$options]);
						foreach ($options as $option_node)
						{
							$item       = (object) [
								'name'      => $option_node->name,
								'code'      => $service_node->code,
								'option'    => $option_node->code,
								'suboption' => [],
							];
							$services[] = $item;
						}
					}
					else
					{
						$services[] = (object) [
							'name'  => $service_node->name,
							'code'  => $service_node->code,
							'price' => ($service_node->price ?? ''),
						];
					}
				}
				else
				{
					$item = (object) [
						'name'   => $service_node->name,
						'code'   => $service_node->code,
						'option' => [],
					];

					if (isset($service_node->options->option))
					{
						$options = $service_node->options->option;
						$options = (is_array($options) ? $options : [$options]);
						foreach ($options as $option_node)
						{
							$item->option[] = $option_node->code;
						}
					}
					$item->option = implode('_', $item->option);
					$services[]   = $item;
				}
			}
		}

		return $services;
	}

	public function getShippingTime($service, $args, $params)
	{
		$args['service_code'] = $service->code;
		if (!empty($service->option))
		{
			$args['option_code'] = $service->option;
		}
		if (!empty($service->suboption))
		{
			$args['suboption_code'] = $service->suboption;
		}
		$data = $this->call('calculate', ['parcel', $this->region], $args, 'json', $params);
		$time = '';
		if (isset($data->postage_result->delivery_time))
		{
			$time = $this->parseShippingTime($data->postage_result->delivery_time);
		}

		return $time;
	}

	public function parseShippingTime($str)
	{
		$time = '';
		if (preg_match('@time:[\s]*(.*)$@i', $str, $matches))
		{
			$time = strtolower($matches[1]);
		}
		else
		{
			if (strpos(strtolower($str), 'next business day') !== false)
			{
				$time = 'Next business day';
			}
			else
			{
				if (preg_match('@([\S]+ (to|in) [\d]+ business days)@i', $str, $matches))
				{
					$time = strtolower($matches[1]);
				}
				else
				{
					if (preg_match('@(Same business day delivery if lodged over the counter before [0-9a-zA-z]+)@i', $str, $matches))
					{
						$time = strtolower($matches[1]);
					}
				}
			}
		}
		if (!empty($time))
		{
			$time = ' (' . $time . ')';
		}

		return $time;
	}

	public function isSatchel($code)
	{
		return (strpos($code, '_SATCHEL_') !== false);
	}

	public function isNotSatchel($code)
	{
		return (!$this->isSatchel($code));
	}

	public function hasSatchels($params)
	{
		$active = $params->get('auspost_pac_services_' . $this->region);

		if (!is_array($active) || empty($active))
		{
			return false;
		}
		foreach ($active as $code)
		{
			if ($this->isSatchel($code))
			{
				return true;
			}
		}

		return false;
	}

	public function addShippingQuote($service, $info, $params)
	{
		$currency = EShopCurrency::getInstance();
		$tax      = new EShopTax(EShopHelper::getConfig());
		$code     = strtolower($service->code);
		if (!isset($service->price))
		{
			return;
		}
		$time = '';
		if ($params->get('auspost_pac_show_delivery_time'))
		{
			if (isset($service->time) && !empty($service->time))
			{
				$time = $service->time;
			}
			else
			{
				$time = $this->getShippingTime($service, $info, $params);
			}
		}
		$handling_fee = (float) $params->get('auspost_pac_handling_fee');

		if ($handling_fee > 0)
		{
			$service->price += $handling_fee;
		}

		if ($params->get('show_shipping_cost_with_tax', 1))
		{
			$text = $currency->format(
				$tax->calculate(
					$currency->convert($service->price - 2.95, 'AUD', $currency->getCurrencyCode()),
					$params->get('taxclass_id'),
					EShopHelper::getConfigValue('tax')
				),
				$currency->getCurrencyCode(),
				1.0000000
			);
		}
		else
		{
			$text = $currency->format(
				$currency->convert($service->price - 2.95, 'AUD', EShopHelper::getConfigValue('default_currency_code')),
				$currency->getCurrencyCode(),
				1.0000000
			);
		}

		$this->quote_data[$code] = [
			'name'        => 'eshop_auspostpac.' . $code,
			'title'       => htmlentities(str_replace(' (Signature on Delivery)', '', $service->name)) . $time,
			'desc'        => htmlentities(str_replace(' (Signature on Delivery)', '', $service->name)) . $time,
			'cost'        => $currency->convert($service->price - 2.95, 'AUD', EShopHelper::getConfigValue('default_currency_code')),
			'taxclass_id' => $params->get('taxclass_id'),
			'text'        => $text,
		];
	}

	public function sort_shipping_quotes($a, $b)
	{
		if ($a["cost"] == $b["cost"])
		{
			return 0;
		}

		return ($a["cost"] > $b["cost"] ? 1 : -1);
	}

	public function sortShippingQuotes()
	{
		uasort($this->quote_data, [$this, 'sort_shipping_quotes']);
	}

	public function getTotalWeight()
	{
		$eshopWeight = EShopWeight::getInstance();
		$cart        = new EShopCart();
		$weight      = 0;
		foreach ($cart->getCartData() as $product)
		{
			$weight += $eshopWeight->convert($product['product_weight'], $product['product_weight_id'], 1);
		}

		return $weight;
	}

	public function buildQuery($query)
	{
		$parts = [];
		foreach ($query as $key => $value)
		{
			if (!is_array($value))
			{
				$value = [$value];
			}
			foreach ($value as $v)
			{
				$parts[] = urlencode($key) . '=' . urlencode($v);
			}
		}

		return implode('&', $parts);
	}

	public function call($call, $args = [], $query = [], $format = 'json', $params)
	{
		$headers   = [
			'AUTH-KEY: ' . $params->get('auspost_pac_api_key'),
		];
		$query_str = '';
		if (!empty($query))
		{
			$query_str = '?' . $this->buildQuery($query);
		}
		$path = '';
		if (!empty($args))
		{
			$path = implode('/', $args) . '/';
		}
		$protocol = 'https';
		if ($params->get('auspost_pac_use_http'))
		{
			$protocol = 'http';
		}
		$url = $protocol . '://digitalapi.auspost.com.au/postage/' . $path . $call . '.' . $format . $query_str;
		//$url = $protocol.'://test.npe.auspost.com.au/api/postage/'.$path.$call.'.'.$format.$query_str;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$result = json_decode(curl_exec($ch));

		return (
		!empty($result)
			? $result
			: (object) $result
		);
	}
}