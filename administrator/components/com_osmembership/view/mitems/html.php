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

class OSMembershipViewMitemsHtml extends MPFViewList
{
	/**
	 * Prepare data for the view
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		parent::prepareView();

		// Insert messages for additional offline payment plugins
		$this->model->insertAdditionalOfflinePaymentMessages();

		$options = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_MESSAGE_GROUP'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('OSM_GENERAL_MESSAGES'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('OSM_RENEWAL_MESSAGES'));
		$options[] = HTMLHelper::_('select.option', 3, Text::_('OSM_UPGRADE_MESSAGES'));
		$options[] = HTMLHelper::_('select.option', 4, Text::_('OSM_RECURRING_MESSAGES'));
		$options[] = HTMLHelper::_('select.option', 5, Text::_('OSM_REMINDER_MESSAGES'));
		$options[] = HTMLHelper::_('select.option', 6, Text::_('OSM_GROUP_MEMBERSHIP_MESSAGES'));
		$options[] = HTMLHelper::_('select.option', 7, Text::_('OSM_SUBSCRIPTION_PAYMENT_MESSAGES'));
		$options[] = HTMLHelper::_('select.option', 8, Text::_('OSM_SMS_MESSAGES'));
		$options[] = HTMLHelper::_('select.option', 9, Text::_('OSM_CUSTOM_MESSAGES'));

		$this->lists['filter_group'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_group',
			' class="form-select" onchange="submit();" ',
			'value',
			'text',
			$this->state->filter_group
		);
	}

	/**
	 * Override addToolbar method, we only need to set title, no other buttons
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(
			Text::_(strtoupper('OSM_' . MPFInflector::singularize($this->name) . '_MANAGEMENT')),
			'link ' . $this->name
		);
	}
}
