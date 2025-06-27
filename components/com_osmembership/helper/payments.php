<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

class OSMembershipHelperPayments
{
	/**
	 * Get list of payment methods
	 *
	 * @return array
	 */
	public static function getPaymentMethods($onlyRecurring = false, $methodIds = null, $excludeOfflinePayment = false)
	{
		static $methods = [];

		if (!$methods)
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__osmembership_plugins')
				->where('published = 1')
				->whereIn('access', Factory::getApplication()->getIdentity()->getAuthorisedViewLevels())
				->order('`ordering`');

			if ($onlyRecurring)
			{
				$query->where('(support_recurring_subscription = 1 OR name LIKE "os_offline%")');
			}

			if ($methodIds)
			{
				$query->whereIn('id', explode(',', $methodIds));
			}

			if ($excludeOfflinePayment)
			{
				$query->where('name NOT LIKE "os_offline%"');
			}

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$baseUri = Uri::base(true);

			foreach ($rows as $row)
			{
				if (file_exists(JPATH_ROOT . '/components/com_osmembership/plugins/' . $row->name . '.php'))
				{
					require_once JPATH_ROOT . '/components/com_osmembership/plugins/' . $row->name . '.php';

					$params = new Registry($row->params);
					$method = new $row->name($params);
					$method->setTitle($row->title);

					if ($params->get('payment_fee_amount') > 0 || $params->get('payment_fee_percent'))
					{
						$method->paymentFee = true;
					}
					else
					{
						$method->paymentFee = false;
					}

					$iconUri = '';

					if ($icon = $params->get('icon'))
					{
						if (file_exists(JPATH_ROOT . '/media/com_osmembership/assets/images/paymentmethods/' . $icon))
						{
							$iconUri = $baseUri . '/media/com_osmembership/assets/images/paymentmethods/' . $icon;
						}
						elseif (file_exists(JPATH_ROOT . '/' . $icon))
						{
							$iconUri = $baseUri . '/' . $icon;
						}
					}

					$method->iconUri = $iconUri;

					$methods[] = $method;
				}
			}
		}

		return $methods;
	}

	/**
	 * Write the javascript objects to show the page
	 *
	 * @return string
	 */
	public static function writeJavascriptObjects()
	{
		$methods  = static::getPaymentMethods();
		$jsString = " methods = new PaymentMethods();\n";

		if (count($methods))
		{
			foreach ($methods as $method)
			{
				$jsString .= " method = new PaymentMethod('" . $method->getName() . "'," . $method->getCreditCard(
					) . ',' . $method->getCardType() . ',' . $method->getCardCvv() . ',' . $method->getCardHolderName(
					) . ");\n";
				$jsString .= " methods.Add(method);\n";
			}
		}

		Factory::getApplication()
			->getDocument()
			->getWebAssetManager()
			->addInlineScript($jsString);
	}

	/**
	 * Load payment method object
	 *
	 * @param $name string Name of payment method
	 *
	 * @return object
	 */
	public static function loadPaymentMethod($name)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_plugins')
			->where('name = ' . $db->quote($name));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get default payment plugin
	 *
	 * @return string
	 */
	public static function getDefautPaymentMethod(
		$methodIds = null,
		$onlyRecurring = false,
		$excludeOfflinePayment = false
	) {
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('name')
			->from('#__osmembership_plugins')
			->where('published = 1')
			->whereIn('access', Factory::getApplication()->getIdentity()->getAuthorisedViewLevels())
			->order('ordering');

		if ($methodIds)
		{
			$query->whereIn('id', explode(',', $methodIds));
		}

		if ($onlyRecurring)
		{
			$query->where('(support_recurring_subscription = 1 OR name = "os_offline")');
		}

		if ($excludeOfflinePayment)
		{
			$query->where('name NOT LIKE "os_offline%"');
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get the payment method object based on it's name
	 *
	 * @param   string  $name
	 *
	 * @return object
	 */
	public static function getPaymentMethod($name)
	{
		$methods = static::getPaymentMethods();

		foreach ($methods as $method)
		{
			if ($method->getName() == $name)
			{
				return $method;
			}
		}

		return;
	}
}
