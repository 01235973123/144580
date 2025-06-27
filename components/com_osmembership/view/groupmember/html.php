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
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class OSMembershipViewGroupmemberHtml extends MPFViewHtml
{
	/**
	 * The plan object which group member belong to
	 *
	 * @var stdClass
	 */
	protected $plan;

	/**
	 * List of select lists use by the view
	 *
	 * @var array<string, mixed>
	 */
	protected $lists;

	/**
	 * Group member data
	 *
	 * @var stdClass
	 */
	protected $item;

	/**
	 * Group member form object
	 *
	 * @var MPFForm
	 */
	protected $form;

	/**
	 * Should we allow select existing users?
	 *
	 * @var bool
	 */
	protected $showExistingUsers;

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
	 * The selected state, we need it to pass to javascript
	 *
	 * @var string
	 */
	protected $selectedState = '';

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
		$item             = $this->model->getData();

		// Check add/edit group member permission
		$canAccess = false;

		if (($item->id && $canManage >= 1) || ($canManage == 2))
		{
			$canAccess = true;
		}

		// Check and make sure group admin can only manage his own group members
		if ($item->id && $item->group_admin_id != Factory::getApplication()->getIdentity()->id)
		{
			$canAccess = false;
		}

		if (!$canAccess)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('OSM_NOT_ALLOW_TO_MANAGE_GROUP_MEMBERS'));
			$app->redirect(Uri::root(), 403);
		}

		$db                      = $this->model->getDbo();
		$query                   = $db->getQuery(true);
		$config                  = OSMembershipHelper::getConfig();
		$this->showExistingUsers = false;

		if (count($addMemberPlanIds) == 1 || $item->id)
		{
			if ($item->id)
			{
				$planId = $item->plan_id;
			}
			else
			{
				$planId = (int) $addMemberPlanIds[0];
			}

			$query->select('id, title')
				->from('#__osmembership_plans')
				->where('id = ' . (int) $planId);
			$db->setQuery($query);
			$this->plan = $db->loadObject();
		}
		else
		{
			// List of existing plans
			$query->select('id, title')
				->from('#__osmembership_plans')
				->where('published = 1')
				->whereIn('id', $addMemberPlanIds)
				->order('ordering');
			$db->setQuery($query);
			$options          = [];
			$options[]        = HTMLHelper::_('select.option', '', Text::_('OSM_SELECT_PLAN'), 'id', 'title');
			$options          = array_merge($options, $db->loadObjectList());
			$lists['plan_id'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'plan_id',
				'form-select class="validate[required]"',
				'id',
				'title',
				$item->plan_id
			);

			$this->lists = $lists;
		}

		OSMembershipHelper::addLangLinkForAjax();

		OSMembershipHelperJquery::loadjQuery();

		$wa = Factory::getApplication()->getDocument()
			->getWebAssetManager()
			->registerAndUseScript(
				'com_osmembership.paymentmethods',
				'media/com_osmembership/assets/js/paymentmethods.min.js'
			);

		$customJSFile = JPATH_ROOT . '/media/com_osmembership/assets/js/custom.js';

		if (file_exists($customJSFile) && filesize($customJSFile) > 0)
		{
			$wa->registerAndUseScript('com_osmembership.custom', 'media/com_osmembership/assets/js/custom.js');
		}

		if ($item->id)
		{
			$memberPlanId = $item->plan_id;
		}
		elseif (isset($planId))
		{
			$memberPlanId = $planId;
		}
		else
		{
			$memberPlanId = 0;
		}

		$rowFields = OSMembershipHelper::getProfileFields($memberPlanId, true);
		$data      = [];

		if ($this->input->getInt('validate_error'))
		{
			$data       = $this->input->post->getData();
			$setDefault = false;
		}
		elseif ($item->id)
		{
			$data       = OSMembershipHelper::getProfileData($item, $item->plan_id, $rowFields);
			$setDefault = false;
		}
		else
		{
			$populateFields = [];

			foreach ($rowFields as $rowField)
			{
				if ($rowField->populate_from_group_admin)
				{
					$populateFields[] = $rowField;
				}
			}

			if (count($populateFields))
			{
				$userId = Factory::getApplication()->getIdentity()->id;
				$planId = (int) $addMemberPlanIds[0];
				$query->clear()
					->select('*')
					->from('#__osmembership_subscribers')
					->where('user_id = ' . $userId)
					->where('plan_id = ' . $planId)
					->order('id');
				$db->setQuery($query);
				$groupAdminSubscription = $db->loadObject();

				$data = OSMembershipHelper::getProfileData($groupAdminSubscription, $planId, $populateFields);
			}

			$setDefault = true;
		}

		if (!isset($data['country']))
		{
			$data['country'] = $config->default_country;
		}

		// Form
		$form = new MPFForm($rowFields);
		$form->setData($data)->bindData($setDefault);
		$form->buildFieldsDependency();

		$fields = $form->getFields();

		if (isset($fields['state']))
		{
			$this->selectedState = $fields['state']->value;
		}

		$this->item            = $item;
		$this->form            = $form;
		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
		$this->config          = $config;

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
		ToolbarHelper::apply('apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('save', 'JTOOLBAR_SAVE');

		if ($this->item->id)
		{
			ToolbarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			ToolbarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
		}
	}
}
