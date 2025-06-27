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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Plugin\PluginHelper;

class OSMembershipViewScheduleK2itemsHtml extends MPFViewHtml
{
	/**
	 * The schedule K2 items
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var Pagination
	 */
	protected $pagination;

	/**
	 * The component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * The current user's subscriptions
	 *
	 * @var array
	 */
	protected $subscriptions;

	/**
	 * Display the view
	 *
	 * @throws Exception
	 */
	public function display()
	{
		if (!PluginHelper::isEnabled('system', 'schedulek2items'))
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('Schedule K2 Items feature is not enabled. Please contact super administrator')
			);

			return;
		}

		$this->requestLogin();

		/* @var $model OSMembershipModelScheduleK2items */
		$model               = $this->getModel();
		$this->items         = $model->getData();
		$this->pagination    = $model->getPagination();
		$this->config        = OSMembershipHelper::getConfig();
		$this->subscriptions = OSMembershipHelper::callOverridableHelperMethod(
			'Subscription',
			'getUserSubscriptionsInfo'
		);

		parent::display();
	}
}
