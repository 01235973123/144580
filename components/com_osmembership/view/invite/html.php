<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;

class OSMembershipViewInviteHtml extends MPFViewHtml
{
	/**
	 * This view does not have an associated model
	 *
	 * @var bool
	 */
	public $hasModel = false;

	/**
	 * List of select lists use by the view
	 *
	 * @var array<string, mixed>
	 */
	protected $lists;

	/**
	 * Bootstrap Helper
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Component messages
	 *
	 * @var MPFConfig
	 */
	protected $message;

	/**
	 * Display the view
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display()
	{
		// Check permission
		$addMemberPlanIds = [];
		$canManage        = OSMembershipHelper::getManageGroupMemberPermission($addMemberPlanIds);

		if (!$canManage)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('OSM_NOT_ALLOW_TO_INVITE_GROUP_MEMBERS'));
			$app->redirect(Uri::root(), 403);

			return;
		}

		if (count($addMemberPlanIds) == 0)
		{
			$Itemid = OSMembershipHelperRoute::findView('groupmembers', $this->input->getInt('Itemid', 0));

			$url = Route::_('index.php?option=com_osmembership&view=groupmembers&Itemid=' . $Itemid, false);

			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('OSM_CANNOT_INVITE_MORE_MEMBERS'));
			$app->redirect($url);

			return;
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('id, title')
			->from('#__osmembership_plans')
			->where('published = 1')
			->whereIn('id', $addMemberPlanIds)
			->order('ordering');
		$db->setQuery($query);
		$options                = [];
		$options[]              = HTMLHelper::_('select.option', '', Text::_('OSM_SELECT_PLAN'), 'id', 'title');
		$options                = array_merge($options, $db->loadObjectList());
		$this->lists['plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'plan_id',
			'class="form-select input-xxlarge"',
			'id',
			'title',
			$this->input->getInt('plan_id', $addMemberPlanIds[0])
		);

		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
		$this->config          = OSMembershipHelper::getConfig();
		$this->message         = OSMembershipHelper::getMessages();

		$this->addToolbar();

		parent::display();
	}

	/**
	 * Method to add toolbar button
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		ToolbarHelper::save('groupmember.send_invite', 'OSM_SEND_INVITE');
		ToolbarHelper::cancel('groupmember.cancel', 'JTOOLBAR_CANCEL');
	}
}
