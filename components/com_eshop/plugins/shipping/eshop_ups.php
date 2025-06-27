<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop - UPS Shipping
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class eshop_ups extends eshop_shipping
{

	/**
	 *
	 * Constructor function
	 */
	public function __construct()
	{
		parent::setName('eshop_ups');
		parent::__construct();
	}

	/**
	 *
	 * Function tet get quote for ups shipping
	 *
	 * @param   array   $addressData
	 * @param   object  $params
	 */
	public function getQuote($addressData, $params)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$cart  = new EShopCart();

		if (!$params->get('ups_geozone_id'))
		{
			$status = true;
		}
		else
		{
			$query->select('COUNT(*)')
				->from('#__eshop_geozonezones')
				->where('geozone_id = ' . intval($params->get('ups_geozone_id')))
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
		//Check customer groups
		$customerGroups = $params->get('customer_groups');

		if (!empty($customerGroups))
		{
			$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

			if (!in_array($customerGroupId, $customerGroups))
			{
				$status = false;
			}
		}

		//Check disabled products
		$disabledProducts = $params->get('disabled_products');

		if (!empty($disabledProducts))
		{
			foreach ($cart->getCartData() as $product)
			{
				if (in_array($product['product_id'], $disabledProducts))
				{
					$status = false;
					break;
				}
			}
		}

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

		$methodData = [];
		if ($status)
		{
			$currency    = EShopCurrency::getInstance();
			$tax         = new EShopTax(EShopHelper::getConfig());
			$eshopWeight = EShopWeight::getInstance();
			$eshopLength = EShopLength::getInstance();
			$cartWeight  = $cart->getWeight();

			// Get weight and weight code
			$weight     = $eshopWeight->convert($cartWeight, EShopHelper::getConfigValue('weight_id'), $params->get('ups_weight_id'));
			$weight     = ($weight < 0.1 ? 0.1 : $weight);
			$weightCode = strtoupper($eshopWeight->getUnit($params->get('ups_weight_id')));
			if ($weightCode == 'KG')
			{
				$weightCode = 'KGS';
			}
			elseif ($weightCode == 'LB')
			{
				$weightCode = 'LBS';
			}

			// Get length and length code
			$length     = $eshopLength->convert($params->get('ups_length'), EShopHelper::getConfigValue('length_id'), $params->get('ups_length_id'));
			$width      = $eshopLength->convert($params->get('ups_width'), EShopHelper::getConfigValue('length_id'), $params->get('ups_length_id'));
			$height     = $eshopLength->convert($params->get('ups_height'), EShopHelper::getConfigValue('length_id'), $params->get('ups_length_id'));
			$lengthCode = strtoupper($eshopLength->getUnit($params->get('ups_length_id')));

			// Service code
			$serviceCode = [
				// US Origin
				'US'    => [
					'01' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_01'),
					'02' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_02'),
					'03' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_03'),
					'07' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_07'),
					'08' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_08'),
					'11' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_11'),
					'12' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_12'),
					'13' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_13'),
					'14' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_14'),
					'54' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_54'),
					'59' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_59'),
					'65' => Text::_('PLG_ESHOP_UPS_US_ORIGIN_65'),
				],
				// Canada Origin
				'CA'    => [
					'01' => Text::_('PLG_ESHOP_UPS_CA_ORIGIN_01'),
					'02' => Text::_('PLG_ESHOP_UPS_CA_ORIGIN_02'),
					'07' => Text::_('PLG_ESHOP_UPS_CA_ORIGIN_07'),
					'08' => Text::_('PLG_ESHOP_UPS_CA_ORIGIN_08'),
					'11' => Text::_('PLG_ESHOP_UPS_CA_ORIGIN_11'),
					'12' => Text::_('PLG_ESHOP_UPS_CA_ORIGIN_12'),
					'13' => Text::_('PLG_ESHOP_UPS_CA_ORIGIN_13'),
					'14' => Text::_('PLG_ESHOP_UPS_CA_ORIGIN_14'),
					'54' => Text::_('PLG_ESHOP_UPS_CA_ORIGIN_54'),
					'65' => Text::_('PLG_ESHOP_UPS_CA_ORIGIN_65'),
				],
				// European Union Origin
				'EU'    => [
					'07' => Text::_('PLG_ESHOP_UPS_EU_ORIGIN_07'),
					'08' => Text::_('PLG_ESHOP_UPS_EU_ORIGIN_08'),
					'11' => Text::_('PLG_ESHOP_UPS_EU_ORIGIN_11'),
					'54' => Text::_('PLG_ESHOP_UPS_EU_ORIGIN_54'),
					'65' => Text::_('PLG_ESHOP_UPS_EU_ORIGIN_65'),
					// next five services Poland domestic only
					'82' => Text::_('PLG_ESHOP_UPS_EU_ORIGIN_82'),
					'83' => Text::_('PLG_ESHOP_UPS_EU_ORIGIN_83'),
					'84' => Text::_('PLG_ESHOP_UPS_EU_ORIGIN_84'),
					'85' => Text::_('PLG_ESHOP_UPS_EU_ORIGIN_85'),
					'86' => Text::_('PLG_ESHOP_UPS_EU_ORIGIN_86'),
				],
				// Puerto Rico Origin
				'PR'    => [
					'01' => Text::_('PLG_ESHOP_UPS_PR_ORIGIN_01'),
					'02' => Text::_('PLG_ESHOP_UPS_PR_ORIGIN_02'),
					'03' => Text::_('PLG_ESHOP_UPS_PR_ORIGIN_03'),
					'07' => Text::_('PLG_ESHOP_UPS_PR_ORIGIN_07'),
					'08' => Text::_('PLG_ESHOP_UPS_PR_ORIGIN_08'),
					'14' => Text::_('PLG_ESHOP_UPS_PR_ORIGIN_14'),
					'54' => Text::_('PLG_ESHOP_UPS_PR_ORIGIN_54'),
					'65' => Text::_('PLG_ESHOP_UPS_PR_ORIGIN_65'),
				],
				// Mexico Origin
				'MX'    => [
					'07' => Text::_('PLG_ESHOP_UPS_MX_ORIGIN_07'),
					'08' => Text::_('PLG_ESHOP_UPS_MX_ORIGIN_08'),
					'54' => Text::_('PLG_ESHOP_UPS_MX_ORIGIN_54'),
					'65' => Text::_('PLG_ESHOP_UPS_MX_ORIGIN_65'),
				],
				// All other origins
				'other' => [
					// service code 7 seems to be gone after January 2, 2007
					'07' => Text::_('PLG_ESHOP_UPS_OTHER_ORIGIN_07'),
					'08' => Text::_('PLG_ESHOP_UPS_OTHER_ORIGIN_08'),
					'11' => Text::_('PLG_ESHOP_UPS_OTHER_ORIGIN_11'),
					'54' => Text::_('PLG_ESHOP_UPS_OTHER_ORIGIN_54'),
					'65' => Text::_('PLG_ESHOP_UPS_OTHER_ORIGIN_65'),
				],
			];

			$xml = '<?xml version="1.0"?>';
			$xml .= '<AccessRequest xml:lang="en-US">';
			$xml .= '	<AccessLicenseNumber>' . $params->get('ups_key') . '</AccessLicenseNumber>';
			$xml .= '	<UserId>' . $params->get('ups_username') . '</UserId>';
			$xml .= '	<Password>' . $params->get('ups_password') . '</Password>';
			$xml .= '</AccessRequest>';
			$xml .= '<?xml version="1.0"?>';
			$xml .= '<RatingServiceSelectionRequest xml:lang="en-US">';
			$xml .= '	<Request>';
			$xml .= '		<TransactionReference>';
			$xml .= '			<CustomerContext>Bare Bones Rate Request</CustomerContext>';
			$xml .= '			<XpciVersion>1.0001</XpciVersion>';
			$xml .= '		</TransactionReference>';
			$xml .= '		<RequestAction>Rate</RequestAction>';
			$xml .= '		<RequestOption>shop</RequestOption>';
			$xml .= '	</Request>';
			$xml .= '   <PickupType>';
			$xml .= '       <Code>' . $params->get('ups_pickup') . '</Code>';
			$xml .= '   </PickupType>';

			if ($params->get('ups_country') == 'US' && $params->get('ups_pickup') == '11')
			{
				$xml .= '   <CustomerClassification>';
				$xml .= '       <Code>' . $params->get('ups_classification') . '</Code>';
				$xml .= '   </CustomerClassification>';
			}

			$xml .= '	<Shipment>';
			$xml .= '		<Shipper>';
			$xml .= '			<Address>';
			$xml .= '				<City>' . $params->get('ups_city') . '</City>';
			$xml .= '				<StateProvinceCode>' . $params->get('ups_state') . '</StateProvinceCode>';
			$xml .= '				<CountryCode>' . $params->get('ups_country') . '</CountryCode>';
			$xml .= '				<PostalCode>' . $params->get('ups_postcode') . '</PostalCode>';
			$xml .= '			</Address>';
			$xml .= '		</Shipper>';
			$xml .= '		<ShipTo>';
			$xml .= '			<Address>';
			$xml .= ' 				<City>' . $addressData['city'] . '</City>';
			$xml .= '				<StateProvinceCode>' . $addressData['zone_code'] . '</StateProvinceCode>';
			$xml .= '				<CountryCode>' . $addressData['iso_code_2'] . '</CountryCode>';
			$xml .= '				<PostalCode>' . $addressData['postcode'] . '</PostalCode>';

			if ($params->get('ups_quote_type') == 'residential')
			{
				$xml .= '				<ResidentialAddressIndicator />';
			}

			$xml .= '			</Address>';
			$xml .= '		</ShipTo>';
			$xml .= '		<ShipFrom>';
			$xml .= '			<Address>';
			$xml .= '				<City>' . $params->get('ups_city') . '</City>';
			$xml .= '				<StateProvinceCode>' . $params->get('ups_state') . '</StateProvinceCode>';
			$xml .= '				<CountryCode>' . $params->get('ups_country') . '</CountryCode>';
			$xml .= '				<PostalCode>' . $params->get('ups_postcode') . '</PostalCode>';
			$xml .= '			</Address>';
			$xml .= '		</ShipFrom>';

			$xml .= '		<Package>';
			$xml .= '			<PackagingType>';
			$xml .= '				<Code>' . $params->get('ups_packaging') . '</Code>';
			$xml .= '			</PackagingType>';

			$xml .= '		    <Dimensions>';
			$xml .= '				<UnitOfMeasurement>';
			$xml .= '					<Code>' . $lengthCode . '</Code>';
			$xml .= '				</UnitOfMeasurement>';
			$xml .= '				<Length>' . $length . '</Length>';
			$xml .= '				<Width>' . $width . '</Width>';
			$xml .= '				<Height>' . $height . '</Height>';
			$xml .= '			</Dimensions>';

			$xml .= '			<PackageWeight>';
			$xml .= '				<UnitOfMeasurement>';
			$xml .= '					<Code>' . $weightCode . '</Code>';
			$xml .= '				</UnitOfMeasurement>';
			$xml .= '				<Weight>' . $weight . '</Weight>';
			$xml .= '			</PackageWeight>';

			if ($params->get('ups_insurance'))
			{
				$xml .= '           <PackageServiceOptions>';
				$xml .= '               <InsuredValue>';
				$xml .= '                   <CurrencyCode>' . $currency->getCurrencyCode() . '</CurrencyCode>';
				$xml .= '                   <MonetaryValue>' . $currency->format($cart->getSubTotal(), '', '', false) . '</MonetaryValue>';
				$xml .= '               </InsuredValue>';
				$xml .= '           </PackageServiceOptions>';
			}

			$xml .= '		</Package>';

			$xml .= '	</Shipment>';
			$xml .= '</RatingServiceSelectionRequest>';

			if (!$params->get('ups_test'))
			{
				$url = 'https://onlinetools.ups.com/ups.app/xml/Rate';
			}
			else
			{
				$url = 'https://wwwcie.ups.com/ups.app/xml/Rate';
			}

			$curl = curl_init($url);

			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_TIMEOUT, 60);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);

			$result = curl_exec($curl);

			curl_close($curl);

			$error = '';

			$quoteData  = [];
			$packageFee = $params->get('package_fee', 0);

			if ($result)
			{
				$dom = new DOMDocument('1.0', 'UTF-8');
				$dom->loadXml($result);

				$ratingServiceSelectionResponse = $dom->getElementsByTagName('RatingServiceSelectionResponse')->item(0);

				$response = $ratingServiceSelectionResponse->getElementsByTagName('Response')->item(0);

				$responseStatusCode = $response->getElementsByTagName('ResponseStatusCode');

				if ($responseStatusCode->item(0)->nodeValue != '1')
				{
					$error = $response->getElementsByTagName('Error')->item(0)->getElementsByTagName('ErrorCode')->item(
							0
						)->nodeValue . ': ' . $response->getElementsByTagName('Error')->item(0)->getElementsByTagName('ErrorDescription')->item(
							0
						)->nodeValue;
				}
				else
				{
					$ratedShipments = $ratingServiceSelectionResponse->getElementsByTagName('RatedShipment');

					foreach ($ratedShipments as $ratedShipment)
					{
						$service = $ratedShipment->getElementsByTagName('Service')->item(0);

						$code = $service->getElementsByTagName('Code')->item(0)->nodeValue;

						$totalCharges = $ratedShipment->getElementsByTagName('TotalCharges')->item(0);

						$cost = $totalCharges->getElementsByTagName('MonetaryValue')->item(0)->nodeValue;

						$currencyCode = $totalCharges->getElementsByTagName('CurrencyCode')->item(0)->nodeValue;

						if (!($code && $cost))
						{
							continue;
						}
						$upsCode = $params->get('ups_' . strtolower($params->get('ups_origin')));
						if (in_array($code, $upsCode))
						{
							$cost = $cost + $packageFee;

							if ($params->get('show_shipping_cost_with_tax', 1))
							{
								$text = $currency->format(
									$tax->calculate(
										$currency->convert($cost, $currencyCode, $currency->getCurrencyCode()),
										$params->get('ups_taxclass_id'),
										EShopHelper::getConfigValue('tax')
									),
									$currency->getCurrencyCode(),
									1.0000000
								);
							}
							else
							{
								$text = $currency->format($cost, $currency->getCurrencyCode(), 1.0000000);
							}

							$quoteData[$code] = [
								'name'        => 'eshop_ups.' . $code,
								'title'       => $serviceCode[$params->get('ups_origin')][$code],
								'desc'        => $serviceCode[$params->get('ups_origin')][$code],
								'cost'        => $currency->convert($cost, $currencyCode, EShopHelper::getConfigValue('default_currency_code')),
								'taxclass_id' => $params->get('ups_taxclass_id'),
								'text'        => $text,
							];
						}
					}
				}
			}

			$title = Text::_('PLG_ESHOP_UPS_TITLE');

			if ($params->get('ups_display_weight'))
			{
				$title .= ' (' . Text::_('PLG_ESHOP_UPS_WEIGHT') . ' ' . $eshopWeight->format($weight, $params->get('ups_weight_id')) . ')';
			}

			$query->clear();
			$query->select('*')
				->from('#__eshop_shippings')
				->where('name = "eshop_ups"');
			$db->setQuery($query);
			$row = $db->loadObject();

			$methodData = [
				'name'     => 'eshop_ups',
				'title'    => $title,
				'quote'    => $quoteData,
				'ordering' => $row->ordering,
				'error'    => $error,
			];
		}

		return $methodData;
	}
}