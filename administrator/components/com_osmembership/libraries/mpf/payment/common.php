<?php
/**
 * Part of the Ossolution Payment Package
 *
 * @copyright  Copyright (C) 2016 - 2016 Ossolution Team. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipActive;

/**
 * Abstract Payment Class
 *
 * @since  1.0
 */
trait MPFPaymentCommon
{
	/**
	 * Flag to determine whether this payment method has payment processing fee
	 *
	 * @var bool
	 */
	public $paymentFee;

	/**
	 * The payment method icon Uri
	 *
	 * @var string
	 */
	public $iconUri;

	/**
	 *  This method is called when payment for the registration is success, it needs to be used by all payment class
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   string                       $transactionId
	 */
	protected function onPaymentSuccess($row, $transactionId)
	{
		$config              = OSMembershipHelper::getConfig();
		$row->transaction_id = $transactionId;
		$row->payment_date   = gmdate('Y-m-d H:i:s');

		if ($row->act !== 'renew')
		{
			$row->published = $this->params->get('paid_payment_subscription_status', 1);
		}
		else
		{
			$row->published = 1;
		}
		
		$row->store();

		if ($row->act == 'upgrade')
		{
			OSMembershipHelper::callOverridableHelperMethod('Subscription', 'processUpgradeMembership', [$row]);
		}

		if (OSMembershipHelperSubscription::needToTriggerActiveEvent($row))
		{
			PluginHelper::importPlugin('osmembership');

			$event = new MembershipActive(['row' => $row]);

			Factory::getApplication()->triggerEvent($event->getName(), $event);
		}
		else
		{
			$row->active_event_triggered = 0;
			$row->store();
		}

		if ($row->process_payment_for_subscription)
		{
			$row->payment_method = $this->name;

			$rowPlan = OSMembershipHelperDatabase::getPlan($row->plan_id);

			if (str_starts_with($row->payment_method ?? '', 'os_offline')
				&& !(int) $rowPlan->expired_date)
			{
				$this->reCalculateSubscriptionDuration($row);
			}

			$row->store();
			OSMembershipHelperMail::sendSubscriptionPaymentEmail($row, $config);
		}
		else
		{
			OSMembershipHelperMail::sendEmails($row, $config);
		}
	}

	/**
	 * Recalculate subscription from_date and to_date for offline payment subscription when the subscription is approved
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return void
	 */
	protected function reCalculateSubscriptionDuration($row)
	{
		$createdDate = Factory::getDate($row->created_date);
		$fromDate    = Factory::getDate($row->from_date);
		$toDate      = Factory::getDate($row->to_date);
		$todayDate   = Factory::getDate('now');
		$diff        = $createdDate->diff($todayDate);
		$fromDate->add($diff);
		$toDate->add($diff);
		$row->from_date = $fromDate->toSql();
		$row->to_date   = $toDate->toSql();
		$row->store();
	}

	/**
	 * Process renew recurring subscription after receiving payment
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   string                       $subscriptionId
	 * @param   string                       $transactionId
	 *
	 * @return void
	 */
	protected function processRenewRecurringSubscription($row, $subscriptionId, $transactionId)
	{
		/* @var OSMembershipModelApi $model */
		$model               = MPFModel::getInstance('Api', 'OSMembershipModel', ['ignore_request' => true]);
		$renewedSubscription = $model->renewRecurringSubscription($row->id, $subscriptionId, $transactionId);

		// Set payment_method for the renewed subscription in case it is lost for some reasons
		if (!$renewedSubscription->payment_method)
		{
			$renewedSubscription->payment_method = $this->getName();
			$renewedSubscription->store();
		}

		$rowPlan = OSMembershipHelperDatabase::getPlan($row->plan_id);

		if ($rowPlan->number_payments > 0 && $rowPlan->number_payments <= ($row->payment_made + 1))
		{
			if ($rowPlan->last_payment_action == 1)
			{
				$renewedSubscription->to_date = '2099-12-31 23:59:59';
				$renewedSubscription->store();
			}
			elseif ($rowPlan->last_payment_action == 2
				&& $rowPlan->extend_duration > 0
				&& $rowPlan->extend_duration_unit)
			{
				$date = Factory::getDate($renewedSubscription->to_date);
				$date->add(new DateInterval('P' . $rowPlan->extend_duration . $rowPlan->extend_duration_unit));
				$renewedSubscription->to_date = $date->toSql();
				$renewedSubscription->store();
			}
		}
	}

	/**
	 * Method to check if payment plugin support cancel recurring subscription
	 *
	 * @return bool
	 */
	public function supportCancelRecurringSubscription()
	{
		return method_exists($this, 'cancelSubscription');
	}

	/**
	 * Method to check if payment plugin support refund payment
	 *
	 * @return bool
	 */
	public function supportRefundPayment()
	{
		return method_exists($this, 'refund');
	}

	/**
	 * Method to check whether we need to show card type on form for this payment method. From now on, we don't have to
	 * show card type on form because it can be detected from card number. Keep it here for B/C reason only
	 *
	 * @return bool|int
	 */
	public function getCardType()
	{
		return 0;
	}

	/**
	 * Method to check whether we need to show card holder name in the form
	 *
	 * @return bool|int
	 */
	public function getCardHolderName()
	{
		return $this->type;
	}

	/**
	 * Method to check whether we need to show card cvv input on form
	 *
	 * @return bool|int
	 */
	public function getCardCvv()
	{
		return $this->type;
	}

	/**
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   int                          $Itemid
	 * @param   string                       $queryString
	 *
	 * @return string
	 */
	protected function getPaymentNotifyUrl($row, $Itemid = 0, $queryString = ''): string
	{
		$url = Uri::root() . 'index.php?option=com_osmembership&task=payment_confirm&payment_method=' . $this->getName(
			);

		if ($queryString)
		{
			$url .= '&' . $queryString;
		}

		if ($Itemid > 0)
		{
			$url .= '&Itemid=' . $Itemid;
		}

		$url .= OSMembershipHelper::getLangLink();

		return $url;
	}

	/**
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   int                          $Itemid
	 *
	 * @return string
	 */
	protected function getRecurringPaymentNotifyUrl($row, $Itemid = 0): string
	{
		$url = Uri::root(
			) . 'index.php?option=com_osmembership&task=recurring_payment_confirm&payment_method=' . $this->getName();

		if ($Itemid > 0)
		{
			$url .= '&Itemid=' . $Itemid;
		}

		$url .= OSMembershipHelper::getLangLink();

		return $url;
	}

	/**
	 * Get SEF return URL after processing payment
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   int                          $Itemid
	 * @param   bool                         $absolute
	 *
	 * @return string
	 */
	protected function getPaymentCompleteUrl($row, $Itemid, $absolute = false)
	{
		$langLink = OSMembershipHelper::getLangLink();

		if ($row->process_payment_for_subscription)
		{
			$Itemid = OSMembershipHelperRoute::getViewRoute('payment', $Itemid);

			$url = 'index.php?option=com_osmembership&view=payment&layout=complete&subscription_code=' . $row->subscription_code . '&Itemid=' . $Itemid . $langLink;
		}
		else
		{
			$url = OSMembershipHelperRoute::getViewRoute(
					'complete',
					$Itemid
				) . '&subscription_code=' . $row->subscription_code . $langLink;
		}

		return Route::_($url, false, 0, $absolute);
	}

	/**
	 * Get payment failure URL
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   int                          $Itemid
	 * @param   bool                         $absolute
	 *
	 * @return string
	 */
	protected function getPaymentFailureUrl($row, $Itemid, $absolute = false): string
	{
		$url = 'index.php?option=com_osmembership&view=failure&Itemid=' . $Itemid;

		return Route::_($url, false, 0, $absolute);
	}

	/**
	 * Get payment failure URL
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   int                          $Itemid
	 * @param   bool                         $absolute
	 *
	 * @return string
	 */
	protected function getPaymentCancelUrl($row, $Itemid, $absolute = false): string
	{
		$url = 'index.php?option=com_osmembership&view=cancel&layout=default&id=' . $row->id . '&Itemid=' . $Itemid;

		return Route::_($url, false, 0, $absolute);
	}

	/**
	 * Store payment error message into session to have it displayed on payment failure page
	 *
	 * @param   string  $error
	 *
	 * @return void
	 */
	protected function setPaymentErrorMessage($error): void
	{
		Factory::getApplication()->getSession()->set('omnipay_payment_error_reason', $error);
	}

	/**
	 * Method to check if the payment success process for this transaction already processed
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   string                       $transactionId
	 *
	 * @return bool
	 */
	protected function transactionProcessed($row, $transactionId = ''): bool
	{
		// Make sure each transaction is only processed once
		if ($transactionId && OSMembershipHelper::isTransactionProcessed($transactionId))
		{
			return true;
		}

		if ($row->published)
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to get a table class from Membership Pro
	 *
	 * @param   string  $name
	 *
	 * @return bool|Table
	 */
	protected function getTable($name)
	{
		$className = 'OSMembershipTable' . ucfirst($name);

		$db = Factory::getContainer()->get('db');

		return new $className($db);
	}

	/**
	 * Method to get Subscriber table object
	 *
	 * @return bool|OSMembershipTableSubscriber
	 */
	protected function getSubscriberTable()
	{
		return $this->getTable('Subscriber');
	}

	/**
	 * Handle payment error. Store the error message in the session and redirect user to payment
	 * failure page
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   int                          $Itemid
	 * @param   string                       $message
	 *
	 * @return void
	 */
	protected function handlePaymentError($row, $Itemid, $message)
	{
		$url = $this->getPaymentFailureUrl($row, $Itemid);
		$this->setPaymentErrorMessage($message);
		Factory::getApplication()->redirect($url);
	}

	/**
	 * Get payment plugin layout
	 *
	 * @param   string  $layout
	 *
	 * @return string
	 */
	protected function getLayoutPath($layout = 'form')
	{
		/* @var \Joomla\CMS\Application\CMSApplication $app */
		$app      = Factory::getApplication();
		$template = $app->getTemplate();

		// Remove os_ from plugin name and remove any _ characters to get folder name
		$name = str_replace(
			'_',
			'',
			substr($this->getName(), 3)
		);

		if (is_file(
			JPATH_THEMES . '/' . $template . '/html/com_osmembership/plugins/' . $name . '/' . $layout . '.php'
		))
		{
			return JPATH_THEMES . '/' . $template . '/html/com_osmembership/plugins/' . $name . '/' . $layout . '.php';
		}

		return JPATH_ROOT . '/components/com_osmembership/plugins/' . $name . '/tmpl/' . $layout . '.php';
	}
}
