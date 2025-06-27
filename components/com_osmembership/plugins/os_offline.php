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
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseDriver;

class os_offline extends MPFPayment
{
	/**
	 * Process payment
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   array                        $data
	 *
	 * @throws Exception
	 */
	public function processPayment($row, $data)
	{
		$app     = Factory::getApplication();
		$Itemid  = $app->getInput()->getInt('Itemid');
		$rowPlan = OSMembershipHelperDatabase::getPlan($row->plan_id);

		if ($rowPlan->recurring_subscription)
		{
			$row->subscription_id = UserHelper::genRandomPassword(15);
			$row->store();
		}

		$subscriptionStatus = $this->params->get('subscription_status');

		if ($subscriptionStatus == 1)
		{
			$this->onPaymentSuccess($row, $row->transaction_id);
		}
		else
		{
			$config = OSMembershipHelper::getConfig();
			OSMembershipHelper::sendEmails($row, $config);
		}

		$app->redirect(Route::_(OSMembershipHelperRoute::getViewRoute('complete', $Itemid), false));
	}

	/**
	 * Cancel recurring subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function cancelSubscription($row)
	{
		// Update all other renewed records as recurring_subscription_cancelled so that offline recurring invoice won't end to them anymore
		if ($row->user_id > 0 && $row->plan_id > 0)
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->update('#__osmembership_subscribers')
				->set('recurring_subscription_cancelled = 1')
				->where('user_id = ' . $row->user_id)
				->where('plan_id = ' . $row->plan_id)
				->where('id != ' . $row->id)
				->where('payment_method = ' . $db->quote($this->name));
			$db->setQuery($query)
				->execute();
		}

		return true;
	}
}
