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
use Joomla\CMS\MVC\Controller\BaseController;

require_once __DIR__ . '/jsonresponse.php';

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerCustomer extends BaseController
{
	use EShopControllerJsonresponse;

	/**
	 *
	 * Function to download invoice
	 */
	public function downloadInvoice()
	{
		$orderId = $this->input->getInt('order_id');
		$db      = Factory::getDbo();
		$row     = new EShopTable('#__eshop_orders', 'id', $db);
		$row->load($orderId);
		$user        = Factory::getUser();
		$canDownload = false;

		if ($user->get('id'))
		{
			$query = $db->getQuery(true);
			$query->select('COUNT(*)')
				->from('#__eshop_orders')
				->where('id = ' . intval($orderId))
				->where('customer_id = ' . intval($user->get('id')));
			$db->setQuery($query);

			if ($db->loadResult() && EShopHelper::isInvoiceAvailable($row, '0', true))
			{
				$canDownload = true;
			}
		}

		if (!$canDownload)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('ESHOP_DOWNLOAD_INVOICE_NOT_AVAILABLE'), 'Error');
			$app->redirect(EShopRoute::getViewRoute('customer') . '&layout=orders');
		}
		else
		{
			EShopHelper::downloadInvoice([$orderId]);
		}
	}

	/**
	 *
	 * Function to download file
	 */
	public function downloadFile()
	{
		$orderId      = $this->input->getInt('order_id');
		$downloadCode = $this->input->getString('download_code');
		$db           = Factory::getDbo();
		$query        = $db->getQuery(true);
		$query->select('a.*')
			->from('#__eshop_orderdownloads AS a')
			->innerJoin('#__eshop_orders AS b ON (a.order_id = b.id)')
			->where('a.order_id = ' . (int) $orderId)
			->where('a.download_code = ' . $db->quote($downloadCode));
		$db->setQuery($query);
		$row = $db->loadObject();

		$canDownload = false;
		$fileName    = '';

		if ($row)
		{
			$query->clear()
				->select('total_downloads_allowed')
				->from('#__eshop_downloads')
				->where('id = ' . intval($row->download_id));
			$db->setQuery($query);
			$totalDownloadsAllowed = $db->loadResult();
			if ($totalDownloadsAllowed > 0)
			{
				if ($row->remaining)
				{
					$fileName = $row->filename;

					//Update remaining
					$query->clear()
						->update('#__eshop_orderdownloads')
						->set('remaining = remaining - 1')
						->where('id = ' . $row->id);
					$db->setQuery($query);
					$db->execute();

					$canDownload = true;
				}
				else
				{
					$message = Text::_('ESHOP_TOTAL_DOWNLOAD_ALLOWED_REACH');
				}
			}
			else
			{
				$canDownload = true;
			}
		}
		else
		{
			$message = Text::_('ESHOP_DO_NOT_HAVE_DOWNLOAD_PERMISSION');
		}

		if ($canDownload)
		{
			while (@ob_end_clean())
			{
			}
			$filePath = JPATH_ROOT . '/media/com_eshop/downloads/' . $fileName;
			EShopHelper::processDownload($filePath, $fileName, true);
		}
		else
		{
			$application = Factory::getApplication();
			$application->enqueueMessage($message, 'notice');
			$application->redirect('index.php');
		}
	}

	/**
	 * Function to process payment method
	 */
	public function processUser()
	{
		$post = $this->input->post->getArray();

		/* @var EShopModelCustomer $model */
		$model = $this->getModel('Customer');
		$json  = $model->processUser($post);

		$this->sendJsonResponse($json);
	}

	/**
	 *
	 * Function to process (add/update) address
	 */
	public function processAddress()
	{
		$app     = Factory::getApplication();
		$session = $app->getSession();

		$post = $this->input->post->getArray();

		/* @var EShopModelCustomer $model */
		$model = $this->getModel('Customer');
		$json  = $model->processAddress($post);

		if ($session->get('shipping_address_id') && $session->get('shipping_address_id') == $post['id'])
		{
			$session->set('shipping_country_id', $post['country_id']);
			$session->set('shipping_zone_id', $post['zone_id']);
			$session->set('shipping_postcode', $post['postcode']);

			$session->clear('shipping_method');
			$session->clear('shipping_methods');
		}
		if ($session->get('payment_address_id') && $session->get('payment_address_id') == $post['id'])
		{
			$session->set('payment_country_id', $post['country_id']);
			$session->set('payment_zone_id', $post['zone_id']);

			$session->clear('payment_method');
		}

		$this->sendJsonResponse($json);
	}

	/**
	 *
	 * Function to delete address
	 */
	public function deleteAddress()
	{
		$id = $this->input->getInt('aid', 0);

		/* @var EShopModelCustomer $model */
		$model = $this->getModel('Customer');
		$json  = $model->deleteAddress($id);

		$this->sendJsonResponse($json);
	}
}