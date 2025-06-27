<?php
/**
 * Part of the Ossolution Payment Package
 *
 * @copyright  Copyright (C) 2016 - 2016 Ossolution Team. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_LIBRARIES . '/omnipay3/vendor/autoload.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Omnipay\Common\Message\AbstractRequest;
use Ossolution\Payment\OmnipayPayment;

/**
 * Payment class which use Omnipay payment class for processing payment
 *
 * @since 1.0
 */
class MPFPaymentOmnipay extends OmnipayPayment
{
	use MPFPaymentCommon;

	/**
	 * This method need to be implemented by the payment plugin class. It needs to set url which users will be
	 * redirected to after a successful payment. The url is stored in paymentSuccessUrl property
	 *
	 * @param   int    $id
	 * @param   array  $data
	 *
	 * @return void
	 */
	protected function setPaymentSuccessUrl($id, $data = [])
	{
		$app    = Factory::getApplication();
		$db     = Factory::getContainer()->get('db');
		$task   = $app->getInput()->getCmd('task');
		$Itemid = $app->getInput()->getInt('Itemid', OSMembershipHelper::getItemid());

		$row = new OSMembershipTableSubscriber($db);
		$row->load($id);

		if ($task == 'process')
		{
			$Itemid = OSMembershipHelperRoute::findView('payment', $Itemid);

			$this->paymentSuccessUrl = Route::_(
				'index.php?option=com_osmembership&view=payment&layout=complete&subscription_code=' . $row->subscription_code . '&Itemid=' . $Itemid,
				false
			);
		}
		else
		{
			$this->paymentSuccessUrl = Route::_(
				OSMembershipHelperRoute::getViewRoute('complete', $Itemid) . '&subscription_code=' . $row->subscription_code,
				false
			);
		}
	}

	/**
	 * This method need to be implemented by the payment plugin class. It needs to set url which users will be
	 * redirected to when the payment is not success for some reasons. The url is stored in paymentFailureUrl property
	 *
	 * @param   int    $id
	 * @param   array  $data
	 *
	 * @return void
	 */
	protected function setPaymentFailureUrl($id, $data = [])
	{
		if (empty($id))
		{
			$id = Factory::getApplication()->getInput()->getInt('id', 0);
		}

		$Itemid = Factory::getApplication()->getInput()->getInt('Itemid', OSMembershipHelper::getItemid());

		$this->paymentFailureUrl = Route::_('index.php?option=com_osmembership&view=failure&id=' . $id . '&Itemid=' . $Itemid, false);
	}

	/**
	 * This method need to be implemented by the payment gateway class. It needs to init the JTable order record,
	 * update it with transaction data and then call onPaymentSuccess method to complete the order.
	 *
	 * @param   int     $id
	 * @param   string  $transactionId
	 *
	 * @return mixed
	 */
	protected function onVerifyPaymentSuccess($id, $transactionId)
	{
		$row = $this->getSubscriberTable();
		$row->load($id);

		if (!$row->id)
		{
			return false;
		}

		if ($row->published)
		{
			return false;
		}

		$this->onPaymentSuccess($row, $transactionId);
	}

	/**
	 * This method is usually called by payment method class to add additional data
	 * to the request message before that message is actually sent to the payment gateway
	 *
	 * @param   AbstractRequest          $request
	 * @param   \Joomla\CMS\Table\Table  $row
	 * @param   array                    $data
	 */
	protected function beforeRequestSend($request, $row, $data)
	{
		parent::beforeRequestSend($request, $row, $data);

		// Set return, cancel and notify URL
		$Itemid  = Factory::getApplication()->getInput()->getInt('Itemid', 0);
		$siteUrl = Uri::base();
		$request->setCancelUrl($siteUrl . 'index.php?option=com_osmembership&view=cancel&id=' . $row->id . '&Itemid=' . $Itemid);
		$request->setReturnUrl(
			$siteUrl . 'index.php?option=com_osmembership&task=payment_confirm&id=' . $row->id . '&payment_method=' . $this->name . '&Itemid=' . $Itemid
		);
		$request->setNotifyUrl(
			$siteUrl . 'index.php?option=com_osmembership&task=payment_confirm&id=' . $row->id . '&payment_method=' . $this->name . '&notify=1&Itemid=' . $Itemid
		);
		$request->setAmount($data['amount']);
		$request->setCurrency($data['currency']);
		$request->setDescription($data['item_name']);

		if (empty($this->redirectHeading))
		{
			$language    = Factory::getApplication()->getLanguage();
			$languageKey = 'OSM_WAIT_' . strtoupper(substr($this->name, 3));

			if ($language->hasKey($languageKey))
			{
				$redirectHeading = Text::_($languageKey);
			}
			else
			{
				$redirectHeading = Text::sprintf('OSM_REDIRECT_HEADING', $this->getTitle());
			}

			$this->setRedirectHeading($redirectHeading);
		}
	}
}
