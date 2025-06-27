<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class PlgUserOSMembership extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;
	/**
	 * Database object
	 *
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	public static function getSubscribedEvents(): array
	{
		return [
			'onUserAfterSave' => 'onUserAfterSave',
		];
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_osmembership'))
		{
			return;
		}

		parent::registerListeners();
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method creates a subscription record for the saved user
	 *
	 * @param   Event  $event
	 *
	 * @return  void
	 *
	 */
	public function onUserAfterSave(Event $event)
	{
		[$user, $isnew, $success, $msg] = array_values($event->getArguments());

		// If the user wasn't stored we don't resync
		if (!$success)
		{
			return;
		}

		// If the user isn't new we don't sync
		if (!$isnew)
		{
			return;
		}

		// Ensure the user id is really an int
		$userId = (int) $user['id'];

		// If the user id appears invalid then bail out just in case
		if (empty($userId))
		{
			return;
		}

		$planId = $this->params->get('plan_id', 0);

		if (empty($planId))
		{
			return;
		}

		if ($this->app->input->getCmd('option') === 'com_osmembership')
		{
			return;
		}

		// If user has existing subscription of this plan, no need for creating it
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $userId)
			->where('plan_id = ' . $planId)
			->where(('(published >= 1 OR payment_method LIKE "os_offline%")'));
		$db->setQuery($query);
		$total = $db->loadResult();

		if ($total)
		{
			return;
		}

		// Create subscription record
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		// Initial basic data for the subscription record
		$name = $user['name'];
		$pos  = strpos($name, ' ');

		if ($pos !== false)
		{
			$firstName = substr($name, 0, $pos);
			$lastName  = substr($name, $pos + 1);
		}
		else
		{
			$firstName = $name;
			$lastName  = '';
		}

		$data = [
			'plan_id'    => $planId,
			'user_id'    => $userId,
			'first_name' => $firstName,
			'last_name'  => $lastName,
			'email'      => $user['email'],
		];

		$model = new OSMembershipModelApi();

		try
		{
			$model->store($data);
		}
		catch (Exception $e)
		{
			// Ignore error for now
		}
	}
}
