<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class OSMembershipViewGroupmemberHtml extends MPFViewItem
{
	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;
	/**
	 * Group member form
	 *
	 * @var MPFForm
	 */
	protected $form;

	/**
	 * Date picker format
	 *
	 * @var string
	 */
	protected $datePickerFormat;

	/**
	 * The selected state, we need to pass it to javascript
	 *
	 * @var string
	 */
	protected $selectedState = '';

	/**
	 * Prepare view data
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$db    = $this->model->getDbo();
		$query = $db->getQuery(true);

		// Initialize plan ID with selected plan
		if (!$this->item->id)
		{
			$this->item->plan_id = $this->input->getInt('filter_plan_id', 0);
		}

		$item   = $this->item;
		$config = OSMembershipHelper::getConfig();

		// Plan section
		$query->select('id, title')
			->from('#__osmembership_plans')
			->where('published = 1')
			->where('(number_group_members > 0 OR number_members_field > 0)')
			->order('ordering');

		$db->setQuery($query);
		$options                = [];
		$options[]              = HTMLHelper::_('select.option', 0, Text::_('OSM_SELECT_PLAN'), 'id', 'title');
		$options                = array_merge($options, $db->loadObjectList());
		$this->lists['plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'plan_id',
			'class="form-select"',
			'id',
			'title',
			$item->plan_id
		);

		// Group selection
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_SELECT_GROUP'), 'user_id', 'name');

		if ($item->plan_id)
		{
			$query->clear()
				->select('DISTINCT user_id, CONCAT(first_name, " ", last_name) AS name')
				->from('#__osmembership_subscribers AS a')
				->where('plan_id = ' . $item->plan_id)
				->where('group_admin_id = 0')
				->group('user_id')
				->order('name');
			$db->setQuery($query);
			$groupAdmins = $db->loadObjectList();

			if (count($groupAdmins))
			{
				$options = array_merge($options, $groupAdmins);
			}

			$plan                      = OSMembershipHelperDatabase::getPlan($item->plan_id);
			$item->lifetime_membership = (int) $plan->lifetime_membership;
		}
		else
		{
			$item->lifetime_membership = 0;
		}

		$this->lists['group_admin_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'group_admin_id',
			' class="form-select"',
			'user_id',
			'name',
			$item->group_admin_id
		);

		// Form field data
		$rowFields = OSMembershipHelper::getProfileFields($item->plan_id, true, $item->language);
		$data      = [];

		$firstName = $this->input->getString('first_name', '');

		if ($firstName)
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
			$setDefault = true;
		}
		if (!isset($data['country']) || !$data['country'])
		{
			$data['country'] = $config->default_country;
		}

		$form = new MPFForm($rowFields);
		$form->setData($data)->bindData($setDefault);
		$form->buildFieldsDependency();

		$this->config = $config;
		$this->form   = $form;

		$this->datePickerFormat = $config->get('date_field_format', '%Y-%m-%d');

		$fields = $form->getFields();

		if (isset($fields['state']))
		{
			$this->selectedState = $fields['state']->value;
		}

		$this->lists['plan_id']        = OSMembershipHelperHtml::getChoicesJsSelect(
			$this->lists['plan_id'],
			Text::_('OSM_TYPE_OR_SELECT_ONE_PLAN')
		);
		$this->lists['group_admin_id'] = OSMembershipHelperHtml::getChoicesJsSelect($this->lists['group_admin_id']);
	}
}
