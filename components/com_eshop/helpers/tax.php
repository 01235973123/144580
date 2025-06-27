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
 * Tax helper class
 *
 */
class EShopTax
{

	/**
	 *
	 * Shipping Address data
	 * @var array(countryID, zoneId)
	 */
	protected $_shippingAddress;

	/**
	 *
	 * Payment Address data
	 * @var array(countryID, zoneId)
	 */

	protected $_paymentAddress;

	/**
	 *
	 * Store Address data
	 * @var array(countryID, zoneId)
	 */
	protected $_storeAddress;

	public function __construct($config = [])
	{
		$session = Factory::getApplication()->getSession();

		if ($session->get('shipping_country_id') || $session->get('shipping_zone_id'))
		{
			$this->setShippingAddress($session->get('shipping_country_id'), $session->get('shipping_zone_id'), $session->get('shipping_postcode'));
		}
		elseif ($config->tax_default == 'shipping')
		{
			$this->setShippingAddress($config->country_id, $config->zone_id, $config->postcode ?? '');
		}

		if ($session->get('payment_country_id') || $session->get('payment_zone_id'))
		{
			$this->setPaymentAddress($session->get('payment_country_id'), $session->get('payment_zone_id'), $session->get('payment_postcode'));
		}
		elseif ($config->tax_default == 'payment')
		{
			$this->setPaymentAddress($config->country_id, $config->zone_id, $config->postcode ?? '');
		}

		$this->setStoreAddress($config->country_id, $config->zone_id, $config->postcode ?? '');
	}

	/**
	 *
	 * Function to set Shipping Address
	 *
	 * @param   int  $countryId
	 * @param   int  $zoneId
	 */
	public function setShippingAddress($countryId, $zoneId, $postcode)
	{
		$this->_shippingAddress = ['countryId' => $countryId, 'zoneId' => $zoneId, 'postcode' => $postcode];
	}

	/**
	 *
	 * Function to set Payment Address
	 *
	 * @param   int  $countryId
	 * @param   int  $zoneId
	 */
	public function setPaymentAddress($countryId, $zoneId, $postcode)
	{
		$this->_paymentAddress = ['countryId' => $countryId, 'zoneId' => $zoneId, 'postcode' => $postcode];
	}

	/**
	 *
	 * Function to set Store Address
	 *
	 * @param   int  $countryId
	 * @param   int  $zoneId
	 */
	public function setStoreAddress($countryId, $zoneId, $postcode)
	{
		$this->_storeAddress = ['countryId' => $countryId, 'zoneId' => $zoneId, 'postcode' => $postcode];
	}

	/**
	 *
	 * Function to get Costs, passed by reference to update
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getCosts(&$totalData, &$total, &$taxes)
	{
		$currency = EShopCurrency::getInstance();

		foreach ($taxes as $key => $value)
		{
			if ($value > 0)
			{
				$totalData[] = [
					'name'  => 'tax',
					'title' => Text::_($this->getTaxRateName($key)),
					'text'  => $currency->format($value),
					'value' => $value,
				];
				$total       += $value;
			}
		}
	}

	/**
	 *
	 * Function to calculate value after tax for a specific value and tax class
	 *
	 * @param   float    $value
	 * @param   int      $taxClassId
	 * @param   boolean  $calculate
	 *
	 * @return float
	 */
	public function calculate($value, $taxClassId, $calculate = true)
	{
		if ($taxClassId && $calculate)
		{
			$amount = $this->getTax($value, $taxClassId);

			return $value + $amount;
		}

		return $value;
	}

	/**
	 *
	 * Private method to get tax for a specific value and tax class
	 *
	 * @param   float  $value
	 * @param   int    $taxClassId
	 *
	 * @return float
	 */
	public function getTax($value, $taxClassId)
	{
		$amount   = 0;
		$taxRates = $this->getTaxRates($value, $taxClassId);

		foreach ($taxRates as $taxRate)
		{
			$amount += $taxRate['amount'];
		}

		return $amount;
	}

	/**
	 *
	 * Function to get tax rates for a specific value and tax class
	 *
	 * @param   float  $value
	 * @param   int    $taxClassId
	 *
	 * @return array
	 */
	public function getTaxRates($value, $taxClassId)
	{
		static $taxRatesStore = [];

		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		if (!isset($taxRatesStore[$taxClassId]))
		{
			$taxRates = [];

			//Based on Shipping Address
			if ($this->_shippingAddress)
			{
				$this->getTaxRatesForGivenAddress(
					$taxRates,
					$taxClassId,
					$customerGroupId,
					'shipping',
					$this->_shippingAddress['countryId'],
					$this->_shippingAddress['zoneId'],
					$this->_shippingAddress['postcode']
				);
			}

			//Based on Payment Address
			if ($this->_paymentAddress)
			{
				$this->getTaxRatesForGivenAddress(
					$taxRates,
					$taxClassId,
					$customerGroupId,
					'payment',
					$this->_paymentAddress['countryId'],
					$this->_paymentAddress['zoneId'],
					$this->_paymentAddress['postcode']
				);
			}

			//Based on Store Address
			if ($this->_storeAddress)
			{
				$this->getTaxRatesForGivenAddress(
					$taxRates,
					$taxClassId,
					$customerGroupId,
					'store',
					$this->_storeAddress['countryId'],
					$this->_storeAddress['zoneId'],
					$this->_storeAddress['postcode']
				);
			}

			if (count($taxRates))
			{
				$taxRatesStore[$taxClassId] = $taxRates;
			}
		}

		$taxRates = $taxRatesStore[$taxClassId] ?? [];

		$taxRatesData = [];

		foreach ($taxRates as $taxRate)
		{
			if (isset($taxRatesData[$taxRate['tax_rate_id']]))
			{
				$amount = $taxRatesData[$taxRate['tax_rate_id']]['amount'];
			}
			else
			{
				$amount = 0;
			}

			if ($taxRate['tax_type'] == 'F')
			{
				$amount += $taxRate['tax_rate'];
			}
			elseif ($taxRate['tax_type'] == 'P')
			{
				$amount += ($value / 100 * $taxRate['tax_rate']);
			}

			//Check EU VAT Rules here
			if (EShopHelper::getConfigValue('enable_eu_vat_rules'))
			{
				$euVatNumber     = Factory::getApplication()->getSession()->get('eu_vat_number');
				$homeCountryId   = EShopHelper::getConfigValue('country_id');
				$homeCountryInfo = EShopHelper::getCountry($homeCountryId);

				if (EShopHelper::getConfigValue('eu_vat_rules_based_on', 'shipping') == 'shipping')
				{
					if (isset($this->_shippingAddress))
					{
						$currentCountryId = $this->_shippingAddress['countryId'];
					}
					else
					{
						$currentCountryId = EShopHelper::getConfigValue('country_id');
					}
				}
				else
				{
					if (isset($this->_paymentAddress))
					{
						$currentCountryId = $this->_paymentAddress['countryId'];
					}
					else
					{
						$currentCountryId = EShopHelper::getConfigValue('country_id');
					}
				}

				if ($currentCountryId)
				{
					$currentCountryInfo = EShopHelper::getCountry($currentCountryId);

					if (!EShopEuvat::isEUCountry($currentCountryInfo->iso_code_2))
					{
						$amount = 0;
					}
					elseif ($euVatNumber != '')
					{
						if ($homeCountryId != $currentCountryId && EShopEuvat::isEUCountry($homeCountryInfo->iso_code_2) && EShopEuvat::isEUCountry(
								$currentCountryInfo->iso_code_2
							) && EShopEuvat::validateEUVATNumber($euVatNumber))
						{
							$amount = 0;
						}
					}
				}
			}

			$taxRatesData[$taxRate['tax_rate_id']] = [
				'tax_rate_id' => $taxRate['tax_rate_id'],
				'tax_name'    => $taxRate['tax_name'],
				'tax_rate'    => $taxRate['tax_rate'],
				'tax_type'    => $taxRate['tax_type'],
				'amount'      => $amount,
			];
		}

		return $taxRatesData;
	}

	/**
	 * Get tax rates for a tax class from given address and store into variable $taxRates
	 *
	 * @param   array   $taxRates
	 * @param   int     $taxClassId
	 * @param   int     $customerGroupId
	 * @param   string  $baseOn
	 * @param           $countryId
	 * @param           $zoneId
	 * @param           $postcode
	 *
	 * @return void
	 */
	protected function getTaxRatesForGivenAddress(&$taxRates, $taxClassId, $customerGroupId, $baseOn, $countryId, $zoneId, $postcode): void
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('tr.priority, t.*')
			->from('#__eshop_taxrules AS tr')
			->innerJoin('#__eshop_taxes AS t ON (tr.tax_id = t.id)')
			->innerJoin('#__eshop_taxclasses AS tc ON (tr.taxclass_id = tc.id)')
			->innerJoin('#__eshop_taxcustomergroups AS tcg ON (t.id = tcg.tax_id)')
			->innerJoin('#__eshop_geozones AS gz ON (t.geozone_id = gz.id)')
			->innerJoin('#__eshop_geozonezones AS gzz ON (gz.id = gzz.geozone_id)')
			->where('tc.published = 1')
			->where('tr.taxclass_id = ' . intval($taxClassId))
			->where('gzz.country_id = ' . intval($countryId))
			->where('(gzz.zone_id = ' . intval($zoneId) . ' OR gzz.zone_id = 0)')
			->where('tcg.customergroup_id = ' . intval($customerGroupId))
			->where('tr.based_on = ' . $db->quote($baseOn))
			->order('tr.priority');
		$db->setQuery($query);

		$rows = $db->loadObjectList();

		for ($i = 0; $n = count($rows), $i < $n; $i++)
		{
			$row       = $rows[$i];
			$gzpStatus = EShopHelper::getGzpStatus($row->geozone_id, $postcode);

			if ($gzpStatus)
			{
				$taxRates[$row->id] = [
					'tax_rate_id' => $row->id,
					'tax_name'    => $row->tax_name,
					'tax_rate'    => $row->tax_rate,
					'tax_type'    => $row->tax_type,
					'priority'    => $row->priority,
				];
			}
		}
	}

	/**
	 * Function to get name of a specific tax rate
	 *
	 * @param   int  $taxRateId
	 *
	 * @return string
	 */
	public function getTaxRateName($taxRateId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('tax_name')
			->from('#__eshop_taxes')
			->where('id = ' . intval($taxRateId));
		$db->setQuery($query);

		return $db->loadResult();
	}
}