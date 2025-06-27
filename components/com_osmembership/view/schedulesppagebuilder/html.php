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
use Joomla\Registry\Registry;

class OSMembershipViewSchedulesppagebuilderHtml extends MPFViewHtml
{
	/**
	 * The schedule SP Page Builder pages
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
	 * Number days pages will be released for access
	 *
	 * @var int
	 */
	protected $releasePagesOlderThanXDays;

	/**
	 * How pageb link to article will be opened
	 *
	 * @var int
	 */
	protected $openPages;

	/**
	 * Display the view
	 *
	 * @throws Exception
	 */
	public function display()
	{
		if (!PluginHelper::isEnabled('system', 'schedulesppagebuilder'))
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('Schedule Content feature is not enabled. Please contact super administrator')
			);

			return;
		}

		$this->requestLogin();

		$plugin = PluginHelper::getPlugin('system', 'schedulesppagebuilder');

		$params = new Registry($plugin->params);

		/* @var $model OSMembershipModelSchedulesppagebuilder */
		$model                            = $this->getModel();
		$this->items                      = $model->getData();
		$this->config                     = OSMembershipHelper::getConfig();
		$this->pagination                 = $model->getPagination();
		$this->subscriptions              = OSMembershipHelper::callOverridableHelperMethod(
			'Subscription',
			'getUserSubscriptionsInfo'
		);
		$this->releasePagesOlderThanXDays = (int) $params->get('release_pages_older_than_x_days', 0);
		$this->openPages                  = $params->get('open_pages');

		parent::display();
	}
}
