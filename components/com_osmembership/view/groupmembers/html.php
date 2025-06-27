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
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class OSMembershipViewGroupmembersHtml extends MPFViewHtml
{
	/**
	 * Group members data
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Model state
	 *
	 * @var MPFModelState
	 */
	protected $state;

	/**
	 * Pagination object
	 *
	 * @var Pagination
	 */
	protected $pagination;

	/**
	 * Fields being displayed on Group Members Management
	 *
	 * @var array
	 */
	protected $fields;

	/**
	 * Group members custom fields data
	 *
	 * @var array
	 */
	protected $fieldsData;

	/**
	 * The current user manage group members permission
	 *
	 * @var int
	 */
	protected $canManage;

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
	 * Display the view
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display()
	{
		$canManage = OSMembershipHelper::getManageGroupMemberPermission();

		if (!$canManage)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('OSM_NOT_ALLOW_TO_MANAGE_GROUP_MEMBERS'));
			$app->redirect(Uri::root(), 403);

			return;
		}

		$fields = OSMembershipHelper::getProfileFields(0, true);

		foreach ($fields as $i => $field)
		{
			if (!$field->show_on_subscriptions)
			{
				unset($fields[$i]);
			}
		}

		/* @var OSMembershipModelGroupmembers $model */
		$model = $this->getModel();

		$this->state           = $model->getState();
		$this->items           = $model->getData();
		$this->fieldsData      = $model->getFieldsData();
		$this->config          = OSMembershipHelper::getConfig();
		$this->pagination      = $model->getPagination();
		$this->canManage       = $canManage;
		$this->fields          = $fields;
		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();

		$this->addToolbar();

		parent::display();
	}

	/**
	 * Add toolbar buttons
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		if ($this->canManage == 2)
		{
			ToolbarHelper::addNew('add', 'JTOOLBAR_NEW');

			if ($this->params->get('show_invite_members_button', 1))
			{
				ToolbarHelper::custom('invite_members', 'mail', 'mail', 'OSM_INVITE_MEMBERS', false);
			}
		}

		if (count($this->items))
		{
			ToolbarHelper::editList('edit', 'JTOOLBAR_EDIT');
			ToolbarHelper::deleteList(Text::_('OSM_DELETE_CONFIRM'), 'delete');
		}
	}
}
