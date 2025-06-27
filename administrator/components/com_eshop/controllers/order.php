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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerOrder extends EShopAdminController
{
	/**
	 *
	 * Function to download attached file for order
	 */
	public function downloadFile()
	{
		$input = Factory::getApplication()->input;
		$id    = $input->getInt('id');
		$model = $this->getModel('Order');
		$model->downloadFile($id);
	}

	/**
	 *
	 * Function to download invoice for orders
	 */
	public function downloadInvoice()
	{
		$input       = Factory::getApplication()->input;
		$fromExports = $input->getInt('from_exports');

		if ($fromExports)
		{
			$dateStart = $input->getString('date_start', '');

			if ($dateStart == '')
			{
				$dateStart = date('Y-m-d', strtotime(date('Y') . '-' . date('m') . '-01'));
			}

			$dateEnd = $input->getString('date_end', '');

			if ($dateEnd == '')
			{
				$dateEnd = date('Y-m-d');
			}

			$groupBy       = $input->getString('group_by', 'week');
			$orderStatusId = $input->getInt('order_status_id', 0);

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id')
				->from('#__eshop_orders');

			if ($orderStatusId)
			{
				$query->where('order_status_id = ' . (int) $orderStatusId);
			}

			if ($dateStart != '')
			{
				// In case use only select date, we will set time of From Date to 00:00:00
				if (strpos($dateStart, ' ') === false && strlen($dateStart) <= 10)
				{
					$dateStart = $dateStart . ' 00:00:00';
				}

				try
				{
					$date = DateTime::createFromFormat('Y-m-d H:i:s', $dateStart, new DateTimeZone(Factory::getApplication()->get('offset')));

					if ($date !== false)
					{
						$date->setTime(0, 0, 0);
						$date->setTimezone(new DateTimeZone("UTC"));
						$query->where('created_date >= ' . $db->quote($date->format('Y-m-d H:i:s')));
					}
				}
				catch (Exception $e)
				{
					// Do-nothing
				}
			}

			if ($dateEnd != '')
			{
				// In case use only select date, we will set time of To Date to 23:59:59
				if (strpos($dateEnd, ' ') === false && strlen($dateEnd) <= 10)
				{
					$dateEnd = $dateEnd . ' 23:59:59';
				}

				try
				{
					$date = DateTime::createFromFormat('Y-m-d H:i:s', $dateEnd, new DateTimeZone(Factory::getApplication()->get('offset')));

					if ($date !== false)
					{
						$date->setTime(23, 59, 59);
						$date->setTimezone(new DateTimeZone("UTC"));
						$query->where('created_date <= ' . $db->quote($date->format('Y-m-d H:i:s')));
					}
				}
				catch (Exception $e)
				{
					// Do-nothing
				}
			}

			$db->setQuery($query);
			$cid = $db->loadColumn();
		}
		else
		{
			$cid = $input->get('cid', [0], '', 'array');
		}

		$filteredId = [];

		if (count($cid))
		{
			$db  = Factory::getDbo();
			$row = new EShopTable('#__eshop_orders', 'id', $db);

			foreach ($cid as $id)
			{
				$row->load($id);

				if (EShopHelper::isInvoiceAvailable($row, '1', false))
				{
					$filteredId[] = $id;
				}
			}
		}

		if (!count($filteredId))
		{
			$mainframe = Factory::getApplication();
			$mainframe->enqueueMessage(Text::_('ESHOP_NO_DATA_TO_DOWNLOAD_INVOICE'));
			$mainframe->redirect('index.php?option=com_eshop&view=orders');
		}
		else
		{
			EShopHelper::downloadInvoice($filteredId);
		}
	}

	/**
	 *
	 * Function to download orders to CSV
	 */
	public function export()
	{
		$input        = Factory::getApplication()->input;
		$cid          = $input->get('cid', [0], '', 'array');
		$currency     = EShopCurrency::getInstance();
		$exportFormat = EShopHelper::getConfigValue('export_data_format', 'csv');

		$db       = Factory::getDbo();
		$nullDate = $db->getNullDate();
		$query    = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_orders');
		if (count($cid))
		{
			$query->where('id IN (' . implode(',', $cid) . ')');
		}
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$orderRows = [];

		if (isset($rows) && count($rows))
		{
			$fields = [];

			$fields[] = 'order_id';
			$fields[] = 'order_number';
			$fields[] = 'invoice_number';
			$fields[] = 'customer_firstname';
			$fields[] = 'customer_lastname';
			$fields[] = 'customer_email';
			$fields[] = 'customer_telephone';
			$fields[] = 'customer_fax';
			$fields[] = 'payment_firstname';
			$fields[] = 'payment_lastname';
			$fields[] = 'payment_email';
			$fields[] = 'payment_telephone';
			$fields[] = 'payment_fax';
			$fields[] = 'payment_company';
			$fields[] = 'payment_company_id';
			$fields[] = 'payment_address_1';
			$fields[] = 'payment_address_2';
			$fields[] = 'payment_city';
			$fields[] = 'payment_postcode';
			$fields[] = 'payment_country_name';
			$fields[] = 'payment_zone_name';
			$fields[] = 'payment_method';
			$fields[] = 'payment_method_title';
			$fields[] = 'transaction_id';
			$fields[] = 'shipping_firstname';
			$fields[] = 'shipping_lastname';
			$fields[] = 'shipping_email';
			$fields[] = 'shipping_telephone';
			$fields[] = 'shipping_fax';
			$fields[] = 'shipping_company';
			$fields[] = 'shipping_company_id';
			$fields[] = 'shipping_address_1';
			$fields[] = 'shipping_address_2';
			$fields[] = 'shipping_city';
			$fields[] = 'shipping_postcode';
			$fields[] = 'shipping_country_name';
			$fields[] = 'shipping_zone_name';
			$fields[] = 'shipping_method';
			$fields[] = 'shipping_method_title';
			$fields[] = 'shipping_tracking_number';
			$fields[] = 'shipping_tracking_url';
			$fields[] = 'shipping_amount';
			$fields[] = 'tax_amount';
			$fields[] = 'total';
			$fields[] = 'comment';
			$fields[] = 'order_status';
			$fields[] = 'created_date';
			$fields[] = 'modified_date';
			$fields[] = 'product_id';
			$fields[] = 'product_name';
			$fields[] = 'option_name';
			$fields[] = 'option_value';
			$fields[] = 'option_sku';
			$fields[] = 'model';
			$fields[] = 'quantity';
			$fields[] = 'unit_price';
			$fields[] = 'unit_total';

			foreach ($rows as $row)
			{
				$row->order_id           = $row->id;
				$row->customer_firstname = $row->firstname;
				$row->customer_lastname  = $row->lastname;
				$row->customer_email     = $row->email;
				$row->customer_telephone = $row->telephone;
				$row->customer_fax       = $row->fax;
				$row->comment			 = strip_tags($row->comment);

				$query->clear()
					->select('text')
					->from('#__eshop_ordertotals')
					->where('order_id = ' . intval($row->id))
					->where('(name = "shipping" OR name="tax")')
					->order('name ASC');
				$db->setQuery($query);
				$orderTotals = $db->loadColumn();

				$row->shipping_amount = $orderTotals[0] ?? '';
				$row->tax_amount      = $orderTotals[1] ?? '';
				$row->total           = $currency->format($row->total, $row->currency_code, $row->currency_exchanged_value);
				$row->order_status    = EShopHelper::getOrderStatusName(
					$row->order_status_id,
					ComponentHelper::getParams('com_languages')->get('site', 'en-GB')
				);

				if ($row->created_date != $nullDate)
				{
					$row->created_date = HTMLHelper::_('date', $row->created_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'), null);
				}
				else
				{
					$row->created_date = '';
				}

				if ($row->modified_date != $nullDate)
				{
					$row->modified_date = HTMLHelper::_('date', $row->modified_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'), null);
				}
				else
				{
					$row->modified_date = '';
				}

				$query->clear();
				$query->select('*')
					->from('#__eshop_orderproducts')
					->where('order_id = ' . intval($row->id));

				$db->setQuery($query);
				$orderProducts = $db->loadObjectList();

				for ($i = 0; $n = count($orderProducts), $i < $n; $i++)
				{
					$orderProductRow = clone $row;

					if ($i > 0)
					{
						$orderProductRow->order_id           = '';
						$orderProductRow->order_number       = '';
						$orderProductRow->invoice_number     = '';
						$orderProductRow->customer_firstname = '';
						$orderProductRow->customer_lastname  = '';
						$orderProductRow->customer_email     = '';
						$orderProductRow->customer_telephone = '';
						$orderProductRow->customer_fax       = '';
						$orderProductRow->transaction_id     = '';
						$orderProductRow->shipping_amount    = '';
						$orderProductRow->tax_amount         = '';
						$orderProductRow->total              = '';
						$orderProductRow->comment            = '';
						$orderProductRow->order_status       = '';
						$orderProductRow->created_date       = '';
						$orderProductRow->modified_date      = '';
					}

					$orderProductRow->product_id   = $orderProducts[$i]->product_id;
					$orderProductRow->product_name = $orderProducts[$i]->product_name;
					$orderProductRow->model        = $orderProducts[$i]->product_sku;
					$orderProductRow->quantity     = $orderProducts[$i]->quantity;
					$orderProductRow->unit_price   = $currency->format(
						$orderProducts[$i]->price,
						$row->currency_code,
						$row->currency_exchanged_value
					);
					$orderProductRow->unit_total   = $currency->format(
						$orderProducts[$i]->total_price,
						$row->currency_code,
						$row->currency_exchanged_value
					);

					$query->clear();
					$query->select('*')
						->from('#__eshop_orderoptions')
						->where('order_product_id = ' . intval($orderProducts[$i]->id));

					$db->setQuery($query);
					$options = $db->loadObjectList();

					if (count($options))
					{
						for ($j = 0; $m = count($options), $j < $m; $j++)
						{
							$optionRow = clone $orderProductRow;

							if ($j > 0)
							{
								$optionRow->order_id           = '';
								$optionRow->order_number       = '';
								$optionRow->invoice_number     = '';
								$optionRow->customer_firstname = '';
								$optionRow->customer_lastname  = '';
								$optionRow->customer_email     = '';
								$optionRow->customer_telephone = '';
								$optionRow->customer_fax       = '';
								$optionRow->transaction_id     = '';
								$optionRow->shipping_amount    = '';
								$optionRow->tax_amount         = '';
								$optionRow->total              = '';
								$optionRow->comment            = '';
								$optionRow->order_status       = '';
								$optionRow->created_date       = '';
								$optionRow->modified_date      = '';
								$optionRow->product_id         = '';
								$optionRow->product_name       = '';
								$optionRow->model              = '';
								$optionRow->quantity           = '';
								$optionRow->unit_price         = '';
								$optionRow->unit_total         = '';
							}

							$optionRow->option_name  = $options[$j]->option_name;
							$optionRow->option_value = $options[$j]->option_value;
							$optionRow->option_sku   = $options[$j]->sku;

							$orderRows[] = $optionRow;
						}
					}
					else
					{
						$orderRows[] = $orderProductRow;
					}
				}
			}

			$filename = 'orders_' . date('YmdHis') . '.' . $exportFormat;
			$filePath = EShopHelper::excelExport($fields, $orderRows, $filename, $fields, $exportFormat);
			EShopHelper::processDownload($filePath, $filename, true);
			Factory::getApplication()->close();
		}
		else
		{
			$mainframe = Factory::getApplication();
			$mainframe->enqueueMessage(Text::_('ESHOP_NO_DATA_TO_EXPORT'), 'notice');
			$mainframe->redirect('index.php?option=com_eshop&view=orders');
		}
	}

	/**
	 *
	 * Function to download orders to CSV
	 */
	public function downloadXML()
	{
		$input    = Factory::getApplication()->input;
		$cid      = $input->get('cid', [0], '', 'array');
		$currency = EShopCurrency::getInstance();
		$db       = Factory::getDbo();
		$nullDate = $db->getNullDate();
		$query    = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_orders');
		if (count($cid))
		{
			$query->where('id IN (' . implode(',', $cid) . ')');
		}
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$csvOutput = [];
		if (count($rows))
		{
			foreach ($rows as $row)
			{
				//list product order
				$orderProductXmlArray = [];
				$query->clear();
				$query->select('*')
					->from('#__eshop_orderproducts')
					->where('order_id = ' . intval($row->id));
				$db->setQuery($query);
				$orderProducts = $db->loadObjectList();
				foreach ($orderProducts as $orderProduct)
				{
					$productXmlArray        = [
						'sku'          => $orderProduct->product_sku,
						'product_name' => $orderProduct->product_name,
						'quantity'     => $orderProduct->quantity,
						'unit_price'   => $orderProduct->price,
					];
					$orderProductXmlArray[] = $productXmlArray;
				}
				//start customer xml
				$customerXml = [
					'firstname' => $row->firstname,
					'lastname'  => $row->lastname,
					'email'     => $row->email,
					'telephone' => $row->telephone,
					'fax'       => $row->fax,
				];
				//end customer xml


				$orderXmlArray                 = [];
				$xmlarray['Orders']['Order'][] = [
					'order_id'                 => $row->id,
					'order_number'             => $row->order_number,
					'order_date'               => ($row->created_date != $nullDate) ? HTMLHelper::_(
						'date',
						$row->created_date,
						EShopHelper::getConfigValue('date_format', 'm-d-Y'),
						null
					) : '',
					'invoice_number'           => $row->invoice_number,
					'payment_firstname'        => $row->payment_firstname,
					'payment_lastname'         => $row->payment_lastname,
					'payment_email'            => $row->payment_email,
					'payment_telephone'        => $row->payment_telephone,
					'payment_fax'              => $row->payment_fax,
					'payment_company'          => $row->payment_company,
					'payment_company_id'       => $row->payment_company_id,
					'payment_address_1'        => $row->payment_address_1,
					'payment_address_2'        => $row->payment_address_2,
					'payment_city'             => $row->payment_city,
					'payment_postcode'         => $row->payment_postcode,
					'payment_country_name'     => $row->payment_country_name,
					'payment_zone_name'        => $row->payment_zone_name,
					'payment_method'           => $row->payment_method,
					'payment_method_title'     => $row->payment_method_title,
					'transaction_id'           => $row->transaction_id,
					'shipping_firstname'       => $row->shipping_firstname,
					'shipping_lastname'        => $row->shipping_lastname,
					'shipping_email'           => $row->shipping_email,
					'shipping_telephone'       => $row->shipping_telephone,
					'shipping_fax'             => $row->shipping_fax,
					'shipping_company'         => $row->shipping_company,
					'shipping_company_id'      => $row->shipping_company_id,
					'shipping_address_1'       => $row->shipping_address_1,
					'shipping_address_2'       => $row->shipping_address_2,
					'shipping_city'            => $row->shipping_city,
					'shipping_postcode'        => $row->shipping_postcode,
					'shipping_country_name'    => $row->shipping_country_name,
					'shipping_zone_name'       => $row->shipping_zone_name,
					'shipping_method'          => $row->shipping_method,
					'shipping_method_title'    => $row->shipping_method_title,
					'shipping_tracking_number' => $row->shipping_tracking_number,
					'shipping_tracking_url'    => $row->shipping_tracking_url,
					'Orderlines'               => [
						'line' => $orderProductXmlArray,
					],
					'customers'                => $customerXml,

				];
				$orderXmlArray[]               = $xmlarray;
			}

			$filename = 'orders_' . date('YmdHis') . '.xml';
			include_once JPATH_ROOT . '/components/com_eshop/helpers/array2xml.php';
			$xml = Array2XML::createXML('SConnectData', $orderXmlArray[0]);
			File::write(JPATH_ROOT . '/media/com_eshop/files/' . $filename, $xml->saveXML());
			EShopHelper::processDownload(JPATH_ROOT . '/media/com_eshop/files/' . $filename, $filename, true);
			exit();
		}
	}
}