<?php

/**
 * @version        3.5.0
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\UserHelper;

class PlgUserEshop extends CMSPlugin
{

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method creates a contact for the saved user
	 *
	 * @param   array    $user     Holds the new user data.
	 * @param   boolean  $isnew    True if a new user is stored.
	 * @param   boolean  $success  True if user was succesfully stored in the database.
	 * @param   string   $msg      Message.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		// If the user wasn't stored we don't resync
		if (!$success)
		{
			return false;
		}

		// If the user isn't new we don't sync
		if (!$isnew)
		{
			return false;
		}

		// If from com_eshop, then don't need to sync
		$input = Factory::getApplication()->input;

		if ($input->get('option') == 'com_eshop')
		{
			return false;
		}

		// Ensure the user id is really an int
		$userId = (int) $user['id'];

		// If the user id appears invalid then bail out just in case
		if (empty($userId))
		{
			return false;
		}

		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_eshop/eshop.php'))
		{
			return true;
		}
		require_once JPATH_ROOT . '/components/com_eshop/helpers/helper.php';
		require_once JPATH_ROOT . '/components/com_eshop/helpers/api.php';
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_eshop/tables');
		$db   = Factory::getDbo();
		$data = [];
		$name = $user['name'];
		//Get first name, last name from username
		$pos = strpos($name, ' ');
		if ($pos !== false)
		{
			$data['firstname'] = substr($name, 0, $pos);
			$data['lastname']  = substr($name, $pos + 1);
		}
		else
		{
			$data['firstname'] = $name;
			$data['lastname']  = '';
		}
		$data['email'] = $user['email'];
		if (PluginHelper::isEnabled('user', 'profile'))
		{
			$profile           = UserHelper::getProfile($userId);
			$data['address_1'] = $profile->profile['address1'];
			$data['address_2'] = $profile->profile['address2'];
			$data['city']      = $profile->profile['city'];
			$country           = $profile->profile['country'];
			if ($country)
			{
				$query = $db->getQuery(true);
				$query->select('iso_code_3')
					->from('#__eshop_countries')
					->where('country_name = ' . $db->quote($country));
				$db->setQuery($query);
				$countryCode          = $db->loadResult();
				$data['country_code'] = $countryCode;
				if ($countryCode != '')
				{
					$region = $profile->profile['region'];
					if ($region)
					{
						$query->clear();
						$query->select('z.zone_code')
							->from('#__eshop_zones AS z')
							->innerJoin('#__eshop_countries AS c ON (z.country_id = c.id)')
							->where('c.iso_code_3 = ' . $db->quote($countryCode))
							->where('z.zone_name = ' . $db->quote($region));
						$db->setQuery($query);
						$data['zone_code'] = $db->loadResult();
					}
				}
			}
			$data['postcode']  = $profile->profile['postal_code'];
			$data['telephone'] = $profile->profile['phone'];
		}
		EShopAPI::addCustomer($userId, $data);

		return true;
	}

	/**
	 * We set the authentication cookie only after login is successfully finished.
	 * We set a new cookie either for a user with no cookies or one
	 * where the user used a cookie to authenticate.
	 *
	 * @param   array  $options  Array holding options
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2
	 */
	public function onUserAfterLogin($options)
	{
		// No process for admin
		if (Factory::getApplication()->isClient('administrator'))
		{
			return false;
		}

		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_eshop/eshop.php'))
		{
			return false;
		}

		require_once JPATH_ROOT . '/components/com_eshop/helpers/helper.php';

		if (!EShopHelper::getConfigValue('store_cart', 0))
		{
			return false;
		}

		$user  = Factory::getUser();
		$cart  = Factory::getApplication()->getSession()->get('cart');
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			->from('#__eshop_carts')
			->where('customer_id = ' . intval($user->get('id')));
		$db->setQuery($query);
		$cartRow = $db->loadObject();

		if (is_object($cartRow))
		{
			if (empty($cart))
			{
				$cart = json_decode($cartRow->cart_data, true);
			}
			else
			{
				$storedCart = json_decode($cartRow->cart_data, true);

				foreach ($storedCart as $key => $quantity)
				{
					if (!isset($cart[$key]))
					{
						$cart[$key] = $quantity;
					}
					else
					{
						$cart[$key] += $quantity;
					}
				}
			}

			Factory::getApplication()->getSession()->set('cart', $cart);
		}

		if (!empty($cart))
		{
			//Store cart
			Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_eshop/tables');

			$row              = Table::getInstance('Eshop', 'Cart');
			$row->id          = is_object($cartRow) ? $cartRow->id : 0;
			$row->customer_id = $user->get('id');
			$row->cart_data   = json_encode($cart);

			if (!$row->id)
			{
				$row->created_date = Factory::getDate()->toSql();
			}

			$row->modified_date = Factory::getDate()->toSql();
			$row->store();
		}
	}
}
