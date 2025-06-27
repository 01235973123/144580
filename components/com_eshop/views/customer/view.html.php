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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewCustomer extends EShopView
{
	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	/**
	 *
	 * @var $success
	 */
	protected $success;

	/**
	 *
	 * @var $user
	 */
	protected $user;

	/**
	 *
	 * @var $customergroup_id
	 */
	protected $customergroup_id;

	/**
	 *
	 * @var $default_customergroup_id
	 */
	protected $default_customergroup_id;

	/**
	 *
	 * @var $userInfo
	 */
	protected $userInfo;

	/**
	 *
	 * @var $tax
	 */
	protected $tax;

	/**
	 *
	 * @var $orders
	 */
	protected $orders;

	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	/**
	 *
	 * @var $warning
	 */
	protected $warning;

	/**
	 *
	 * @var $paymentFields
	 */
	protected $paymentFields;

	/**
	 *
	 * @var $shippingFields
	 */
	protected $shippingFields;

	/**
	 *
	 * @var $orderProducts
	 */
	protected $orderProducts;

	/**
	 *
	 * @var $orderInfor
	 */
	protected $orderInfor;

	/**
	 *
	 * @var $orderTotals
	 */
	protected $orderTotals;

	/**
	 *
	 * @var $downloads
	 */
	protected $downloads;

	/**
	 *
	 * @var $addresses
	 */
	protected $addresses;

	/**
	 *
	 * @var $address
	 */
	protected $address;

	/**
	 *
	 * @var $lists
	 */
	protected $lists;

	/**
	 *
	 * @var $form
	 */
	protected $form;

	public function display($tpl = null)
	{
		$app = Factory::getApplication();

		if (EShopHelper::isCatalogMode())
		{
			$app->getSession()->set('warning', Text::_('ESHOP_CATALOG_MODE_ON'));
			$app->redirect(Route::_(EShopRoute::getViewRoute('categories')));
		}
		else
		{
			$this->bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
			$layout                = $this->getLayout();

			if ($layout == 'account')
			{
				if (EShopHelper::getConfigValue('customer_manage_account', '1'))
				{
					$this->displayAccount($tpl);

					return;
				}
				else
				{
					$app->redirect(Route::_(EShopRoute::getViewRoute('customer')));
				}
			}
			elseif ($layout == 'orders')
			{
				if (EShopHelper::getConfigValue('customer_manage_order', '1'))
				{
					$this->displayOrders($tpl);

					return;
				}
				else
				{
					$app->redirect(Route::_(EShopRoute::getViewRoute('customer')));
				}
			}
			elseif ($layout == 'quotes')
			{
				if (EShopHelper::getConfigValue('customer_manage_quote', '1'))
				{
					$this->displayQuotes($tpl);
			
					return;
				}
				else
				{
					$app->redirect(Route::_(EShopRoute::getViewRoute('customer')));
				}
			}
			elseif ($layout == 'order')
			{
				if (EShopHelper::getConfigValue('customer_manage_order', '1'))
				{
					$this->displayOrder($tpl);

					return;
				}
				else
				{
					$app->redirect(Route::_(EShopRoute::getViewRoute('customer')));
				}
			}
			elseif ($layout == 'quote')
			{
				if (EShopHelper::getConfigValue('customer_manage_quote', '1'))
				{
					$this->displayQuote($tpl);
			
					return;
				}
				else
				{
					$app->redirect(Route::_(EShopRoute::getViewRoute('customer')));
				}
			}
			elseif ($layout == 'downloads')
			{
				if (EShopHelper::getConfigValue('customer_manage_download', '1'))
				{
					$this->displayDownloads($tpl);

					return;
				}
				else
				{
					$app->redirect(Route::_(EShopRoute::getViewRoute('customer')));
				}
			}
			elseif ($layout == 'addresses')
			{
				if (EShopHelper::getConfigValue('customer_manage_address', '1'))
				{
					$this->displayAddresses($tpl);

					return;
				}
				else
				{
					$app->redirect(Route::_(EShopRoute::getViewRoute('customer')));
				}
			}
			elseif ($layout == 'address')
			{
				if (EShopHelper::getConfigValue('customer_manage_address', '1'))
				{
					$this->displayAddress($tpl);

					return;
				}
				else
				{
					$app->redirect(Route::_(EShopRoute::getViewRoute('customer')));
				}
			}
			else
			{
				$user = Factory::getUser();

				if ($user->id)
				{
					$session = $app->getSession();

					$userInfo = $this->get('user');

					// Success message
					if ($session->get('success'))
					{
						$this->success = $session->get('success');
						$session->clear('success');
					}

					$this->user = $userInfo;

					parent::display($tpl);
				}
				else
				{
					$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();
					$app->redirect(
						Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()) . $usersMenuItemStr)
					);
				}
			}
		}
	}

	/**
	 *
	 * Function to display edit account page
	 *
	 * @param   string  $tpl
	 */
	protected function displayAccount($tpl)
	{
		$user = Factory::getUser();

		if ($user->id)
		{
			$userInfo = $this->get('user');

			if ($userInfo->customergroup_id)
			{
				$selected = $userInfo->customergroup_id;
			}
			else
			{
				$selected = EShopHelper::getConfigValue('customergroup_id');
			}

			$customerGroupDisplay = EShopHelper::getConfigValue('customer_groupdisplay');
			$countCustomerGroup   = count(explode(',', $customerGroupDisplay));

			if ($countCustomerGroup > 1)
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select('a.id, b.customergroup_name AS name')
					->from('#__eshop_customergroups AS a')
					->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
					->where('a.published = 1')
					->where('b.language = "' . Factory::getLanguage()->getTag() . '"');

				if ($customerGroupDisplay != '')
				{
					$query->where('a.id IN (' . $customerGroupDisplay . ')');
				}

				$query->order('b.customergroup_name');
				$db->setQuery($query);

				$this->customergroup_id = HTMLHelper::_('select.genericlist', $db->loadObjectList(), 'customergroup_id', '', 'id', 'name', $selected);
			}
			elseif ($countCustomerGroup == 1)
			{
				$this->default_customergroup_id = $customerGroupDisplay;
			}

			$this->user     = $user;
			$this->userInfo = $userInfo;

			parent::display($tpl);
		}
		else
		{
			$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();
			Factory::getApplication()->redirect(
				'index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()) . $usersMenuItemStr
			);
		}
	}

	/**
	 *
	 * Function to display list orders for user
	 *
	 * @param   string  $tpl
	 */
	protected function displayOrders($tpl)
	{
		$user = Factory::getUser();

		if ($user->id)
		{
			$tax      = new EShopTax(EShopHelper::getConfig());
			$currency = EShopCurrency::getInstance();
			$orders   = $this->get('Orders');

			for ($i = 0; $n = count($orders), $i < $n; $i++)
			{
				$orders[$i]->total = $currency->format($orders[$i]->total, $orders[$i]->currency_code, $orders[$i]->currency_exchanged_value);
			}

			$this->tax      = $tax;
			$this->orders   = $orders;
			$this->currency = $currency;

			// Warning message
			$session = Factory::getApplication()->getSession();

			if ($session->get('warning'))
			{
				$this->warning = $session->get('warning');
				$session->clear('warning');
			}

			parent::display($tpl);
		}
		else
		{
			$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();
			Factory::getApplication()->redirect(
				'index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()) . $usersMenuItemStr
			);
		}
	}
	
	/**
	 *
	 * Function to display list quotes for user
	 *
	 * @param   string  $tpl
	 */
	protected function displayQuotes($tpl)
	{
		$user = Factory::getUser();
	
		if ($user->id)
		{
			$tax      = new EShopTax(EShopHelper::getConfig());
			$currency = EShopCurrency::getInstance();
			$quotes   = $this->get('Quotes');
			
			for ($i = 0; $n = count($quotes), $i < $n; $i++)
			{
				$quotes[$i]->total = $currency->format($quotes[$i]->total, $quotes[$i]->currency_code, $quotes[$i]->currency_exchanged_value);
			}
	
			$this->tax      = $tax;
			$this->currency = $currency;
			$this->quotes   = $quotes;
	
			// Warning message
			$session = Factory::getApplication()->getSession();
	
			if ($session->get('warning'))
			{
				$this->warning = $session->get('warning');
				$session->clear('warning');
			}
	
			parent::display($tpl);
		}
		else
		{
			$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();
			Factory::getApplication()->redirect(
				'index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()) . $usersMenuItemStr
				);
		}
	}

	/**
	 *
	 * Function to display order information
	 *
	 * @param   string  $tpl
	 */
	protected function displayOrder($tpl)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		if ($user->id)
		{
			$orderId = $this->input->getInt('order_id');

			//Get order infor
			$orderInfor = EShopHelper::getOrder((int) $orderId);

			if (!is_object($orderInfor) || (is_object($orderInfor) && $orderInfor->customer_id != $user->get('id')))
			{
				$app->getSession()->set('warning', Text::_('ESHOP_ORDER_DOES_NOT_EXITS'));
				$app->redirect(EShopRoute::getViewRoute('customer') . '&layout=orders');
			}
			else
			{
				$tax      = new EShopTax(EShopHelper::getConfig());
				$currency = EShopCurrency::getInstance();

				$orderProducts = EShopHelper::getOrderProducts($orderId);

				for ($i = 0; $n = count($orderProducts), $i < $n; $i++)
				{
					$orderProducts[$i]->options = $orderProducts[$i]->orderOptions;
				}

				$orderTotals = EShopHelper::getOrderTotals($orderId);

				//Payment custom fields here
				$form                = new EshopRADForm(EShopHelper::getFormFields('B'));
				$this->paymentFields = $form->getFields();

				//Shipping custom fields here
				$form                 = new EshopRADForm(EShopHelper::getFormFields('S'));
				$this->shippingFields = $form->getFields();
				$this->orderProducts  = $orderProducts;
				$this->orderInfor     = $orderInfor;
				$this->orderTotals    = $orderTotals;
				$this->tax            = $tax;
				$this->currency       = $currency;

				parent::display($tpl);
			}
		}
		else
		{
			$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();
			$app->redirect(
				'index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()) . $usersMenuItemStr
			);
		}
	}
	
	/**
	 *
	 * Function to display quote information
	 *
	 * @param   string  $tpl
	 */
	protected function displayQuote($tpl)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();
	
		if ($user->id)
		{
			$quoteId = $this->input->getInt('quote_id');
	
			//Get quote infor
			$quoteInfor = EShopHelper::getQuote((int) $quoteId);
	
			if (!is_object($quoteInfor) || (is_object($quoteInfor) && $quoteInfor->customer_id != $user->get('id')))
			{
				$app->getSession()->set('warning', Text::_('ESHOP_QUOTE_DOES_NOT_EXITS'));
				$app->redirect(EShopRoute::getViewRoute('customer') . '&layout=quotes');
			}
			else
			{
				$tax      = new EShopTax(EShopHelper::getConfig());
				$currency = EShopCurrency::getInstance();
				
				//Quote products
				$quoteProducts = EShopHelper::getQuoteProducts($quoteId);
				
				//Quote totals
				$quoteTotals = EShopHelper::getQuoteTotals($quoteId);
				
				$this->quoteProducts  = $quoteProducts;
				$this->quoteInfor     = $quoteInfor;
				$this->quoteTotals    = $quoteTotals;
				$this->tax            = $tax;
				$this->currency       = $currency;
	
				parent::display($tpl);
			}
		}
		else
		{
			$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();
			$app->redirect(
				'index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()) . $usersMenuItemStr
				);
		}
	}

	/**
	 *
	 * Function to display list downloads for user
	 *
	 * @param   string  $tpl
	 */
	protected function displayDownloads($tpl)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		if ($user->id)
		{
			$downloads = $this->get('Downloads');

			foreach ($downloads as $download)
			{
				$size   = filesize(JPATH_SITE . '/media/com_eshop/downloads/' . $download->filename);
				$i      = 0;
				$suffix = [
					'B',
					'KB',
					'MB',
					'GB',
					'TB',
					'PB',
					'EB',
					'ZB',
					'YB',
				];

				while (($size / 1024) > 1)
				{
					$size = $size / 1024;
					$i++;
				}

				$download->size = round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i];
			}

			$this->downloads = $downloads;

			// Warning message
			$session = $app->getSession();

			if ($session->get('warning'))
			{
				$this->warning = $session->get('warning');
				$session->clear('warning');
			}

			parent::display($tpl);
		}
		else
		{
			$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();
			$app->redirect(
				'index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()) . $usersMenuItemStr
			);
		}
	}

	/**
	 *
	 * Function to display addresses for user
	 *
	 * @param   string  $tpl
	 */
	protected function displayAddresses($tpl)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		if ($user->id)
		{
			$addresses       = $this->get('addresses');
			$this->addresses = $addresses;

			// Warning message
			$session = $app->getSession();

			if ($session->get('success'))
			{
				$this->success = $session->get('success');
				$session->clear('success');
			}

			if ($session->get('warning'))
			{
				$this->warning = $session->get('warning');
				$session->clear('warning');
			}

			parent::display($tpl);
		}
		else
		{
			$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();
			$app->redirect(
				'index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()) . $usersMenuItemStr
			);
		}
	}

	/**
	 *
	 * Function to display address form
	 *
	 * @param   string  $tpl
	 */
	protected function displayAddress($tpl)
	{
		$app     = Factory::getApplication();
		$user    = Factory::getUser();
		$session = $app->getSession();

		if ($user->id)
		{
			$address = $this->get('address');
			$lists   = [];

			if (is_object($address))
			{
				(EShopHelper::getDefaultAddressId($address->customer_id) == $address->id) ? $isDefault = 1 : $isDefault = 0;
			}
			else
			{
				$isDefault = 0;
			}

			$fields       = EShopHelper::getFormFields();
			$form         = new EshopRADForm($fields);
			$countryField = $form->getField('country_id');
			$zoneField    = $form->getField('zone_id');

			if (is_object($address))
			{
				$data = [];

				foreach ($fields as $field)
				{
					if (property_exists($address, $field->name))
					{
						$data[$field->name] = $address->{$field->name};
					}
				}

				$form->bind($data);

				if ($zoneField instanceof EshopRADFormFieldZone)
				{
					$zoneField->setCountryId($address->country_id);
				}
			}
			else
			{
				if (EShopHelper::isFieldPublished('country_id'))
				{
					$countryField = $form->getField('country_id');
					$countryId    = (int) $session->get('payment_country_id') ? $session->get('payment_country_id') : $countryField->getValue();
				}
				else
				{
					$countryId = EShopHelper::getConfigValue('country_id');
				}

				if ($countryId && $zoneField instanceof EshopRADFormFieldZone)
				{
					$zoneField->setCountryId($countryId);
				}
			}

			$lists['default_address'] = HTMLHelper::_('select.booleanlist', 'default_address', '', $isDefault);

			$this->address = $address;
			$this->lists   = $lists;
			$this->form    = $form;

			parent::display($tpl);
		}
		else
		{
			$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();
			$app->redirect(
				'index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()) . $usersMenuItemStr
			);
		}
	}
}