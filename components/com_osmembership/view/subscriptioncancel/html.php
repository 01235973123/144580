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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;

class OSMembershipViewSubscriptioncancelHtml extends MPFViewHtml
{
	/**
	 * The message display above form
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * The subscription record
	 *
	 * @var stdClass
	 */
	protected $rowSubscriber;

	/**
	 * Flag to mark that this view does not have an associated model
	 *
	 * @var bool
	 */
	public $hasModel = false;

	/**
	 * Set data and render the view
	 *
	 * @return void
	 * @throws Exception
	 */
	public function display()
	{
		$messageObj  = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		$this->setLayout('default');
		$subscriptionId = (int) Factory::getApplication()->getSession()->get('mp_subscription_id');

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('a.*')
			->select($db->quoteName('b.title' . $fieldSuffix, 'plan_title'))
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__osmembership_plans AS b ON a.plan_id = b.id')
			->where('a.id = ' . $subscriptionId);
		$db->setQuery($query);
		$rowSubscriber = $db->loadObject();

		if (!$rowSubscriber)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('Invalid subscription code'));
			$app->redirect(Uri::root(), 404);
		}

		if (OSMembershipHelper::isValidMessage($messageObj->{'recurring_subscription_cancel_message' . $fieldSuffix}))
		{
			$message = $messageObj->{'recurring_subscription_cancel_message' . $fieldSuffix};
		}
		else
		{
			$message = $messageObj->recurring_subscription_cancel_message;
		}

		$message = str_replace('[PLAN_TITLE]', $rowSubscriber->plan_title, $message);

		// Get latest subscription end date
		$query->clear()
			->select('MAX(to_date)')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $rowSubscriber->user_id)
			->where('plan_id = ' . $rowSubscriber->plan_id);
		$db->setQuery($query);
		$subscriptionEndDate = $db->loadResult();

		if ($subscriptionEndDate)
		{
			$config              = OSMembershipHelper::getConfig();
			$subscriptionEndDate = HTMLHelper::_('date', $subscriptionEndDate, $config->date_format);
		}

		$message = str_replace('[SUBSCRIPTION_END_DATE]', $subscriptionEndDate, $message);

		// Common tags
		$replaces = [];

		if ($config->common_tags)
		{
			$commonTags = json_decode($config->common_tags, true);

			foreach ($commonTags as $commonTag)
			{
				$replaces[$commonTag['name']] = $commonTag['value'];
			}
		}

		$message = OSMembershipHelper::replaceCaseInsensitiveTags($message, $replaces);

		$this->message       = $message;
		$this->rowSubscriber = $rowSubscriber;

		parent::display();
	}
}
