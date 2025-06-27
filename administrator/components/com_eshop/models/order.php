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
use Joomla\Filesystem\File;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelOrder extends EShopModel
{

	public function __construct($config)
	{
		parent::__construct($config);
	}

	public function store(&$data)
	{
		$input = Factory::getApplication()->input;

		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$data[$key] = json_encode($value);
			}
		}

		if (!isset($data['payment_country_id']) || $data['payment_country_id'] == '')
		{
			$data['payment_country_id'] = 0;
		}

		if (!isset($data['payment_zone_id']) || $data['payment_zone_id'] == '')
		{
			$data['payment_zone_id'] = 0;
		}

		if (!isset($data['shipping_country_id']) || $data['shipping_country_id'] == '')
		{
			$data['shipping_country_id'] = 0;
		}

		if (!isset($data['shipping_zone_id']) || $data['shipping_zone_id'] == '')
		{
			$data['shipping_zone_id'] = 0;
		}

		$row = new EShopTable('#__eshop_orders', 'id', $this->getDbo());
		$row->load($data['id']);
		$paymentCountryInfo = EShopHelper::getCountry($data['payment_country_id']);

		if (is_object($paymentCountryInfo))
		{
			$data['payment_country_name'] = $paymentCountryInfo->country_name;
		}

		$paymentZoneInfo = EShopHelper::getZone($data['payment_zone_id']);

		if (is_object($paymentZoneInfo))
		{
			$data['payment_zone_name'] = $paymentZoneInfo->zone_name;
		}

		$shippingCountryInfo = EShopHelper::getCountry($data['shipping_country_id']);

		if (is_object($shippingCountryInfo))
		{
			$data['shipping_country_name'] = $shippingCountryInfo->country_name;
		}

		$shippingZoneInfo = EShopHelper::getZone($data['shipping_zone_id']);

		if (is_object($shippingZoneInfo))
		{
			$data['shipping_zone_name'] = $shippingZoneInfo->zone_name;
		}

		$orderStatusChanged = false;
		$updateStock        = false;

		if ($data['order_status_id'] != $row->order_status_id)
		{
			$orderStatusChanged = true;
			$orderStatusFrom    = $row->order_status_id;
			$orderStatusTo      = $data['order_status_id'];
		}
		$data['modified_date'] = Factory::getDate()->toSql();
		parent::store($data);
		$row->load($data['id']);

		if ($orderStatusChanged)
		{
			$updateInventory = false;

			//Check to update Inventory
			if (strpos($row->payment_method, 'os_offline') !== false)
			{
				//Offline payment plugin
				if ($orderStatusTo == EShopHelper::getConfigValue('canceled_status_id'))
				{
					$updateInventory = true;
					$updateType      = '+';
				}
				elseif ($orderStatusFrom == EShopHelper::getConfigValue('canceled_status_id'))
				{
					$updateInventory = true;
					$updateType      = '-';
				}
			}
			else
			{
				//Online payment plugin
				if ($orderStatusTo == EShopHelper::getConfigValue('complete_status_id'))
				{
					$updateInventory = true;
					$updateType      = '-';
				}
				elseif ($orderStatusFrom == EShopHelper::getConfigValue('complete_status_id') && $orderStatusTo == EShopHelper::getConfigValue(
						'canceled_status_id'
					))
				{
					$updateInventory = true;
					$updateType      = '+';
				}
			}

			if ($updateInventory)
			{
				EShopHelper::updateInventory($row, $updateType);
			}

			if ($data['order_status_id'] == EShopHelper::getConfigValue('complete_status_id'))
			{
				PluginHelper::importPlugin('eshop');
				Factory::getApplication()->triggerEvent('onAfterCompleteOrder', [$row]);
			}

			if ($input->getInt('send_notification_email'))
			{
				$mailer    = Factory::getMailer();
				$sendFrom  = EShopHelper::getSendFrom();
				$fromName  = $sendFrom['from_name'];
				$fromEmail = $sendFrom['from_email'];
				$subject   = EShopHelper::getMessageValue('order_status_change_subject', $row->language);
				$subject   = str_replace('[STORE_NAME]', EShopHelper::getConfigValue('store_name'), $subject);
				$subject   = str_replace('[ORDER_STATUS_FROM]', EShopHelper::getOrderStatusName($orderStatusFrom, $row->language), $subject);
				$subject   = str_replace('[ORDER_STATUS_TO]', EShopHelper::getOrderStatusName($orderStatusTo, $row->language), $subject);
				$subject   = str_replace('[ORDER_ID]', $row->id, $subject);
				$subject   = str_replace('[ORDER_NUMBER]', $row->order_number, $subject);
				$body      = EShopHelper::getNotificationEmailBody($row, $orderStatusFrom, $orderStatusTo);
				$body      = EShopHelper::convertImgTags($body);
				$mailer->ClearAllRecipients();
				$attachment = null;

				if (EShopHelper::isInvoiceAvailable($row, '0', true))
				{
					if (!$row->invoice_number)
					{
						$row->invoice_number = EShopHelper::getInvoiceNumber();
						$row->store();
					}
					if (!is_file(
						JPATH_ROOT . '/media/com_eshop/invoices/' . EShopHelper::formatInvoiceNumber(
							$row->invoice_number,
							$row->created_date
						) . '.pdf'
					))
					{
						EShopHelper::generateInvoicePDF([$row->id]);
					}
					$attachment = JPATH_ROOT . '/media/com_eshop/invoices/' . EShopHelper::formatInvoiceNumber(
							$row->invoice_number,
							$row->created_date
						) . '.pdf';
				}

				try
				{
					$mailer->sendMail($fromEmail, $fromName, $row->email, $subject, $body, 1, null, null, $attachment);
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
				}
			}
		}

		if ($input->getInt('send_shipping_notification_email') && $input->getString('shipping_tracking_number') != '' && $input->getString(
				'shipping_tracking_url'
			) != '')
		{
			$mailer    = Factory::getMailer();
			$sendFrom  = EShopHelper::getSendFrom();
			$fromName  = $sendFrom['from_name'];
			$fromEmail = $sendFrom['from_email'];
			$subject   = EShopHelper::getMessageValue('shipping_notification_email_subject', $row->language);
			$body      = EShopHelper::getShippingNotificationEmailBody($row);
			$body      = EShopHelper::convertImgTags($body);
			$mailer->ClearAllRecipients();

			try
			{
				$mailer->sendMail($fromEmail, $fromName, $row->email, $subject, $body, 1);
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			}
		}

		return true;
	}

	/**
	 * Method to remove orders
	 *
	 * @access    public
	 * @return boolean True on success
	 * @since     1.5
	 */
	public function delete($cid = [])
	{
		if (count($cid))
		{
			//Update Inventory first
			foreach ($cid as $id)
			{
				$row = Table::getInstance('Eshop', 'Order');
				$row->load($id);
				EShopHelper::updateInventory($row, '+');
			}

			$cids = implode(',', $cid);

			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			$query->delete('#__eshop_orders')
				->where('id IN (' . $cids . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			$numItemsDeleted = $db->getAffectedRows();

			//Delete order products
			$query->clear();
			$query->delete('#__eshop_orderproducts')
				->where('order_id IN (' . $cids . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete order totals
			$query->clear();
			$query->delete('#__eshop_ordertotals')
				->where('order_id IN (' . $cids . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete order options
			$query->clear();
			$query->delete('#__eshop_orderoptions')
				->where('order_id IN (' . $cids . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete order downloads
			$query->clear();
			$query->delete('#__eshop_orderdownloads')
				->where('order_id IN (' . $cids . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			if ($numItemsDeleted < count($cid))
			{
				//Removed warning
				return 2;
			}
		}

		//Removed success
		return 1;
	}

	/**
	 *
	 * Function to download file
	 *
	 * @param   int  $id
	 */
	public function downloadFile($id, $download = true)
	{
		$app   = Factory::getApplication();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('option_value')
			->from('#__eshop_orderoptions')
			->where('id = ' . intval($id));
		$db->setQuery($query);
		$filename = $db->loadResult();
		while (@ob_end_clean())
		{
		}
		EShopHelper::processDownload(JPATH_ROOT . '/media/com_eshop/files/' . $filename, $filename, $download);
		$app->close(0);
	}
}