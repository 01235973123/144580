<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for Membership Pro component
 *
 * @static
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class DonationViewEmailsHtml extends OSFViewList
{
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
	 *
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$emailTypes = [
			'confirmation'				   => Text::_('JD_DONATION_CONFIRMATION'),
			'member_email'				   => Text::_('JD_DONATION_CONFIRMATION_TO_MEMBERS'),
			'admin_email'				   => Text::_('JD_DONATION_NOTIFICATION_TO_ADMIN'),
			'honoree_email'				   => Text::_('JD_HONOREE_NOTIFICATION_EMAILS'),
			'recurring_email'			   => Text::_('JD_RECURRING_DONATION_NOTIFICATION_EMAIL'),
			'member_recurring_email'       => Text::_('JD_RECURRING_DONATION_NOTIFICATION_EMAIL_TO_MEMBERS'),
			'cancel_recurring_admin_email' => Text::_('JD_RECURRING_DONATION_CANCELLATION_NOTIFICATION_EMAIL_TO_ADMIN'),
			'cancel_recurring_email'       => Text::_('JD_RECURRING_DONATION_CANCELLATION_NOTIFICATION_EMAIL'),
		];

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('JD_EMAIL_TYPE'));

		foreach ($emailTypes as $key => $value)
		{
			$options[] = HTMLHelper::_('select.option', $key, $value);
		}

		$this->lists['filter_email_type'] = HTMLHelper::_('select.genericlist', $options, 'filter_email_type', ' onchange="submit();" class="input-large form-select"', 'value', 'text', $this->state->filter_email_type);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('JD_SENT_TO'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('JD_ADMIN'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('JD_DONORS'));

		$this->lists['filter_sent_to'] = HTMLHelper::_('select.genericlist', $options, 'filter_sent_to', ' onchange="submit();" class="input-medium form-select"', 'value', 'text', $this->state->filter_sent_to);
		$this->emailTypes              = $emailTypes;
	}

	/**
	 * Method to add toolbar buttons
	 */
	protected function addToolbar()
	{
		//parent::addToolbar();

		ToolbarHelper::title(Text::_('JD_EMAIL_LOGS'), 'mail');
		ToolbarHelper::custom('delete', 'delete', 'delete', 'JD_DELETE', true) ;
		ToolbarHelper::trash('delete_all', 'JD_DELETE_ALL', false);
	}
}
