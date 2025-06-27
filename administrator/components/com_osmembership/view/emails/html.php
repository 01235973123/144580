<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class OSMembershipViewEmailsHtml extends MPFViewList
{
	/**
	 * Email Types
	 *
	 * @var array<string, string>
	 */
	protected $emailTypes;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   array  $config  The configuration data for the view
	 *
	 * @since  1.0
	 */
	public function __construct($config = [])
	{
		$config['hide_buttons'] = ['add', 'edit', 'publish'];

		parent::__construct($config);
	}

	/**
	 * Build necessary data for the view before it is being displayed
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$emailTypes = [
			'new_subscription_emails'             => Text::_('OSM_NEW_SUBSCRIPTION_EMAILS'),
			'subscription_renewal_emails'         => Text::_('OSM_SUBSCRIPTION_RENEWAL_EMAILS'),
			'subscription_upgrade_emails'         => Text::_('OSM_SUBSCRIPTION_UPGRADE_EMAILS'),
			'subscription_approved_emails'        => Text::_('OSM_SUBSCRIPTION_APPROVED_EMAILS'),
			'subscription_cancel_emails'          => Text::_('OSM_SUBSCRIPTION_CANCEL_EMAILS'),
			'profile_updated_emails'              => Text::_('OSM_PROFILE_UPDATED_EMAILS'),
			'first_reminder_emails'               => Text::_('OSM_FIRST_REMINDER_EMAILS'),
			'second_reminder_emails'              => Text::_('OSM_SECOND_REMINDER_EMAILS'),
			'third_reminder_emails'               => Text::_('OSM_THIRD_REMINDER_EMAILS'),
			'fourth_reminder_emails'              => Text::_('OSM_FOURTH_REMINDER_EMAILS'),
			'fifth_reminder_emails'               => Text::_('OSM_FIFTH_REMINDER_EMAILS'),
			'sixth_reminder_emails'               => Text::_('OSM_SIXTH_REMINDER_EMAILS'),
			'subscription_end_emails'             => Text::_('OSM_SUBSCRIPTION_END_EMAILS'),
			'mass_mails'                          => Text::_('OSM_MASS_EMAILS'),
			'offline_recurring_email'             => Text::_('OSM_OFFLINE_RECURRING_EMAILS'),
			'request_payment_email'               => Text::_('OSM_REQUEST_PAYMENT_EMAILS'),
			'group_membership_invite_email'       => Text::_('OSM_GROUP_MEMBERSHIP_INVITE_EMAILS'),
			'icps_notify_email'                   => Text::_('OSM_INCOMPLETE_PAYMENT_NOTIFICATION_EMAILS'),
			'new_group_member_email'              => Text::_('OSM_NEW_GROUP_MEMBER_NOTIFICATION_EMAILS'),
			'user_join_group_notification_emails' => Text::_('OSM_USER_JOIN_GROUP_NOTIFICATION_EMAILS'),
		];

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_EMAIL_TYPE'));

		foreach ($emailTypes as $key => $value)
		{
			$options[] = HTMLHelper::_('select.option', $key, $value);
		}

		$this->lists['filter_email_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_email_type',
			'class="form-select" onchange="submit();"',
			'value',
			'text',
			$this->state->filter_email_type
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_SENT_TO'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('OSM_ADMIN'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('OSM_SUBSCRIBERS'));

		$this->lists['filter_sent_to'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_sent_to',
			'class="form-select" onchange="submit();"',
			'value',
			'text',
			$this->state->filter_sent_to
		);

		$this->emailTypes = $emailTypes;
	}

	/**
	 * Add custom toolbar buttons use by this view
	 *
	 * @return void
	 */
	protected function addCustomToolbarButtons()
	{
		ToolbarHelper::trash('delete_all', 'OSM_DELETE_ALL', false);
	}
}
