<?php

/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Eshop Detect Currency Plugin
 *
 * @package        Joomla
 * @subpackage     EShop
 */
class plgSystemEshopDetectCurrency extends CMSPlugin
{

	public function onAfterInitialise()
	{
		$app = Factory::getApplication();

		if ($app->isClient('administrator'))
		{
			return;
		}

		require_once JPATH_ROOT . '/components/com_eshop/helpers/helper.php';

		if (is_file(JPATH_ROOT . '/components/com_eshop/eshop.php'))
		{
			if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
			{
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
			{
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else
			{
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			$xml = "http://www.geoplugin.net/xml.gp?ip=" . $ip;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $xml);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$xml = curl_exec($ch);
			curl_close($ch);

			$country = self::produceXmlObjectTree($xml);

			//Get all currency code of eshop
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('currency_code')
				->from('#__eshop_currencies')
				->where('published = 1');
			$db->setQuery($query);
			$currencyCodeArr = $db->loadColumn();

			// Update currency code corresponding to the country
			if (in_array((string) $country->geoplugin_currencyCode, $currencyCodeArr))
			{
				$currencyCode = (string) $country->geoplugin_currencyCode;
			}
			else
			{
				$currencyCode = $this->params->get('default_currency') != '' ? $this->params->get('default_currency') : EShopHelper::getConfigValue(
					'default_currency_code'
				);
			}

			self::change($currencyCode);
		}

		return true;
	}

	/**
	 * Function to get object tree of country
	 *
	 * @param   xml link $rawXML
	 *
	 * @return boolean|SimpleXMLElement
	 */
	private function produceXmlObjectTree($rawXML)
	{
		libxml_use_internal_errors(true);

		try
		{
			$xmlTree = new SimpleXMLElement($rawXML);
		}
		catch (Exception $e)
		{
			// Something went wrong.
			$errorMessage = 'SimpleXMLElement threw an exception.';

			foreach (libxml_get_errors() as $error_line)
			{
				$errorMessage .= "\t" . $error_line->message;
			}

			trigger_error($errorMessage);

			return false;
		}

		return $xmlTree;
	}

	/**
	 * Function to set currency
	 */
	private function change($currencyCode)
	{
		$session     = Factory::getApplication()->getSession();
		$inputCookie = Factory::getApplication()->input->cookie;

		if (!$session->get('currency_code') || $session->get('currency_code') != $currencyCode)
		{
			$session->set('currency_code', $currencyCode);
		}

		$cookieCurrencyCode = $inputCookie->getString('currency_code');

		if (!$cookieCurrencyCode || $cookieCurrencyCode != $currencyCode)
		{
			setcookie('currency_code', $currencyCode, time() + 60 * 60 * 24 * 30);

			$inputCookie->set('currency_code', $currencyCode);
		}
	}
}
