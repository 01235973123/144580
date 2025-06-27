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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use OSSolution\MembershipPro\Admin\Event\Field\GetFieldsMapping;
use OSSolution\MembershipPro\Admin\Event\Field\GetNewsletterFields;

class OSMembershipViewFieldHtml extends MPFViewItem
{
	/**
	 * @var array
	 */
	protected $dependOnOptions;

	/**
	 * @var array
	 */
	protected $dependOptions;

	/**
	 * Prepare view data
	 *
	 * @return void
	 * @throws Exception
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$db    = $this->model->getDbo();
		$query = $db->getQuery(true);
		$item  = $this->item;
		$lists = &$this->lists;

		$fieldTypes = [
			'Text',
			'Url',
			'Email',
			'Number',
			'Tel',
			'Range',
			'Password',
			'Textarea',
			'List',
			'Checkboxes',
			'Radio',
			'Date',
			'Heading',
			'Message',
			'File',
			'Countries',
			'State',
			'SQL',
			'Hidden',
		];

		$options   = [];
		$options[] = HTMLHelper::_('select.option', -1, Text::_('OSM_FIELD_TYPE'));

		foreach ($fieldTypes as $fieldType)
		{
			$options[] = HTMLHelper::_('select.option', $fieldType, $fieldType);
		}

		// Allow adding custom field type
		$files     = Folder::files(
			JPATH_ADMINISTRATOR . '/components/com_osmembership/libraries/mpf/form/field',
			'php$'
		);
		$coreFiles = [];

		foreach ($fieldTypes as $fieldType)
		{
			$coreFiles[] = strtolower($fieldType . '.php');
		}

		foreach ($files as $file)
		{
			if (!in_array($file, $coreFiles))
			{
				$fieldType = ucfirst(File::stripExt($file));
				$options[] = HTMLHelper::_('select.option', $fieldType, $fieldType);
			}
		}

		if ($item->is_core)
		{
			$readOnly = ' readonly="true" ';
		}
		else
		{
			$readOnly = '';
		}

		$lists['fieldtype'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'fieldtype',
			' class="form-select" ' . $readOnly,
			'value',
			'text',
			$item->fieldtype
		);

		// Assignment
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_ALL_PLANS'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('OSM_ALL_SELECTED_PLANS'));
		$options[] = HTMLHelper::_('select.option', -1, Text::_('OSM_ALL_EXCEPT_SELECTED_PLANS'));

		$lists['assignment'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'assignment',
			'class="form-select"',
			'value',
			'text',
			$item->assignment
		);

		$query->select('id, title')
			->from('#__osmembership_plans')
			->where('published = 1')
			->order('ordering');

		$db->setQuery($query);
		$options = [];
		$options = array_merge($options, $db->loadObjectList());

		if ($item->id && $item->plan_id == 0)
		{
			$planIds = [0];
		}
		elseif ($item->id)
		{
			$query->clear()
				->select('plan_id')
				->from('#__osmembership_field_plan')
				->where('field_id = ' . $item->id);
			$db->setQuery($query);
			$planIds = array_map('abs', $db->loadColumn());
		}
		else
		{
			$planIds = [0];
		}

		$lists['plan_id']  = HTMLHelper::_(
			'select.genericlist',
			$options,
			'plan_id[]',
			' class="form-select chosen" multiple="multiple" ',
			'id',
			'title',
			$planIds
		);
		$options           = [];
		$options[]         = HTMLHelper::_('select.option', 1, Text::_('Yes'));
		$options[]         = HTMLHelper::_('select.option', 2, Text::_('No'));
		$lists['required'] = OSMembershipHelperHtml::getBooleanInput('required', $item->required);
		$lists['multiple'] = OSMembershipHelperHtml::getBooleanInput('multiple', $item->multiple);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('None'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('Integer Number'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('Number'));
		$options[] = HTMLHelper::_('select.option', 3, Text::_('Email'));
		$options[] = HTMLHelper::_('select.option', 4, Text::_('Url'));
		$options[] = HTMLHelper::_('select.option', 5, Text::_('Phone'));
		$options[] = HTMLHelper::_('select.option', 6, Text::_('Date'));
		$options[] = HTMLHelper::_('select.option', 7, Text::_('Past Date'));
		$options[] = HTMLHelper::_('select.option', 8, Text::_('Future Date'));
		$options[] = HTMLHelper::_('select.option', 9, Text::_('Ipv4'));
		$options[] = HTMLHelper::_('select.option', 10, Text::_('Ipv6'));
		$options[] = HTMLHelper::_('select.option', 11, Text::_('Min Number Characters'));
		$options[] = HTMLHelper::_('select.option', 12, Text::_('Max Number Characters'));
		$options[] = HTMLHelper::_('select.option', 13, Text::_('Min Integer'));
		$options[] = HTMLHelper::_('select.option', 14, Text::_('Max Integer'));
		$options[] = HTMLHelper::_('select.option', 15, Text::_('Min Selected Checkboxes'));
		$options[] = HTMLHelper::_('select.option', 16, Text::_('Max Selected Checkboxes'));
		$options[] = HTMLHelper::_('select.option', 17, Text::_('Only Number and Space characters'));
		$options[] = HTMLHelper::_('select.option', 18, Text::_('Only Letter and Space characters'));
		$options[] = HTMLHelper::_('select.option', 19, Text::_('Only Letter and Number characters, No Space'));
		$options[] = HTMLHelper::_('select.option', 20, Text::_('Equals'));

		$lists['datatype_validation'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'datatype_validation',
			'class="form-select"',
			'value',
			'text',
			$item->datatype_validation
		);

		// Payment methods
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_ALL_PAYMENT_METHODS'), 'name', 'title');
		$query->clear()
			->select('name, title')
			->from('#__osmembership_plugins')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);
		$methods = $db->loadObjectList();

		$options                       = array_merge($options, $methods);
		$this->lists['payment_method'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'payment_method',
			' class="form-select" ',
			'name',
			'title',
			$this->item->payment_method
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_ABOVE_PAYMENT_INFORMATION'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('OSM_BELOW_PAYMENT_INFORMATION'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('OSM_BELOW_PAYMENT_METHODS'));

		$this->lists['position'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'position',
			'class="form-select"',
			'value',
			'text',
			$this->item->position
		);

		// Trigger plugins to get list of fields for mapping
		PluginHelper::importPlugin('osmembership');

		$event = new GetFieldsMapping();

		$results = array_filter(Factory::getApplication()->triggerEvent($event->getName(), $event));
		$fields  = [];

		foreach ($results as $res)
		{
			if (is_array($res) && count($res))
			{
				$fields = $res;
				break;
			}
		}

		if (count($fields))
		{
			$options                = [];
			$options[]              = HTMLHelper::_('select.option', '', Text::_('Select Field'));
			$options                = array_merge($options, $fields);
			$lists['field_mapping'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'field_mapping',
				' class="form-select" ',
				'value',
				'text',
				$item->field_mapping
			);
		}

		// Newsletter field mapping, support for custom fields in ACYMailing
		$event = new GetNewsletterFields();

		$results = Factory::getApplication()->triggerEvent($event->getName(), $event);
		$fields  = [];

		foreach ($results as $res)
		{
			if (is_array($res) && count($res))
			{
				$fields = $res;
				break;
			}
		}

		if (count($fields))
		{
			$options                           = [];
			$options[]                         = HTMLHelper::_('select.option', '', Text::_('Select Field'));
			$options                           = array_merge($options, $fields);
			$lists['newsletter_field_mapping'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'newsletter_field_mapping',
				'class="form-select"',
				'value',
				'text',
				$this->item->newsletter_field_mapping
			);
		}

		$lists['fee_field']                  = OSMembershipHelperHtml::getBooleanInput('fee_field', $item->fee_field);
		$lists['show_on_members_list']       = OSMembershipHelperHtml::getBooleanInput(
			'show_on_members_list',
			$item->show_on_members_list
		);
		$lists['show_on_group_member_form']  = OSMembershipHelperHtml::getBooleanInput(
			'show_on_group_member_form',
			$item->show_on_group_member_form
		);
		$lists['hide_on_membership_renewal'] = OSMembershipHelperHtml::getBooleanInput(
			'hide_on_membership_renewal',
			$item->hide_on_membership_renewal
		);
		$lists['hide_on_email']              = OSMembershipHelperHtml::getBooleanInput(
			'hide_on_email',
			$item->hide_on_email
		);
		$lists['hide_on_export']             = OSMembershipHelperHtml::getBooleanInput(
			'hide_on_export',
			$item->hide_on_export
		);
		$lists['can_edit_on_profile']        = OSMembershipHelperHtml::getBooleanInput(
			'can_edit_on_profile',
			$item->can_edit_on_profile
		);

		// Custom Fee Fields Behavior
		$options = [];

		$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_TRIAL_PAYMENT_ONLY'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('OSM_RECURRING_PAYMENT_ONLY'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('OSM_BOTH'));

		$lists['fee_usage'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'fee_usage',
			'class="form-select"',
			'value',
			'text',
			$item->fee_usage
		);

		if (PluginHelper::isEnabled('osmembership', 'userprofile'))
		{
			$options   = [];
			$options[] = HTMLHelper::_('select.option', '', Text::_('Select Field'));

			$options[] = HTMLHelper::_('select.option', 'username', 'username');
			$options[] = HTMLHelper::_('select.option', 'name', 'name');

			if (PluginHelper::isEnabled('user', 'profile'))
			{
				$fields = [
					'address1',
					'address2',
					'city',
					'region',
					'country',
					'postal_code',
					'phone',
					'website',
					'favoritebook',
					'aboutme',
					'dob'
				];

				foreach ($fields as $field)
				{
					$options[] = HTMLHelper::_('select.option', $field);
				}
			}

			// Get user custom fields if available
			$useFields = OSMembershipHelper::getUserFields();

			foreach ($useFields as $userField)
			{
				$options[] = HTMLHelper::_('select.option', $userField->name);
			}

			$lists['profile_field_mapping'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'profile_field_mapping',
				' class="form-select" ',
				'value',
				'text',
				$item->profile_field_mapping
			);
		}

		// Custom fields dependency
		$query = $db->getQuery(true);
		$query->select('id, title')
			->from('#__osmembership_fields')
			->where('fieldtype IN ("List", "Radio", "Checkboxes")')
			->where('published=1');
		$db->setQuery($query);
		$options                     = [];
		$options[]                   = HTMLHelper::_('select.option', 0, Text::_('Select'), 'id', 'title');
		$options                     = array_merge($options, $db->loadObjectList());
		$lists['depend_on_field_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'depend_on_field_id',
			'class="form-select"',
			'id',
			'title',
			$item->depend_on_field_id
		);

		if ($item->depend_on_field_id)
		{
			//Get the selected options
			if (is_string($item->depend_on_options) && is_array(json_decode($item->depend_on_options)))
			{
				$this->dependOnOptions = json_decode($item->depend_on_options);
			}
			else
			{
				$this->dependOnOptions = explode(',', $item->depend_on_options);
			}

			$query->clear()
				->select('`values`')
				->from('#__osmembership_fields')
				->where('id=' . $item->depend_on_field_id);
			$db->setQuery($query);
			$this->dependOptions = explode("\r\n", $db->loadResult());
		}
		else
		{
			$this->dependOptions = [];
		}

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('None'));
		$options[] = HTMLHelper::_('select.option', 'STRING', Text::_('String'));
		$options[] = HTMLHelper::_('select.option', 'INT', Text::_('Integer'));
		$options[] = HTMLHelper::_('select.option', 'UINT', Text::_('Unsigned Integer'));
		$options[] = HTMLHelper::_('select.option', 'FLOAT', Text::_('Float Number'));
		$options[] = HTMLHelper::_('select.option', 'WORD', Text::_('WORD'));
		$options[] = HTMLHelper::_('select.option', 'ALNUM', Text::_('Alphanumeric'));
		$options[] = HTMLHelper::_('select.option', 'UPPERCASE', Text::_('OSM_UPPERCASE'));
		$options[] = HTMLHelper::_('select.option', 'LOWERCASE', Text::_('OSM_LOWERCASE'));
		$options[] = HTMLHelper::_('select.option', 'TRIM', Text::_('OSM_TRIM'));
		$options[] = HTMLHelper::_('select.option', 'LTRIM', Text::_('OSM_LTRIM'));
		$options[] = HTMLHelper::_('select.option', 'LTRIM', Text::_('OSM_RTRIM'));
		$options[] = HTMLHelper::_('select.option', 'UCFIRST', Text::_('OSM_UCFIRST'));
		$options[] = HTMLHelper::_('select.option', 'UCWORDS', Text::_('OSM_UCWORDS'));

		$lists['filter'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter',
			'class="form-select"',
			'value',
			'text',
			$item->filter
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_SELECT'));
		$options[] = HTMLHelper::_('select.option', 'osm-one-one', Text::_('OSM_FULL_WIDTH'));
		$options[] = HTMLHelper::_('select.option', 'osm-one-half', Text::_('1/2'));
		$options[] = HTMLHelper::_('select.option', 'osm-one-third', Text::_('1/3'));
		$options[] = HTMLHelper::_('select.option', 'osm-two-thirds', Text::_('2/3'));
		$options[] = HTMLHelper::_('select.option', 'osm-one-quarter', Text::_('1/4'));
		$options[] = HTMLHelper::_('select.option', 'osm-two-quarters', Text::_('2/4'));
		$options[] = HTMLHelper::_('select.option', 'osm-three-quarters', Text::_('3/4'));

		$lists['container_size'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'container_size',
			'class="form-select"',
			'value',
			'text',
			$item->container_size
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_DEFAULT'));
		$options[] = HTMLHelper::_('select.option', 'input-mini', Text::_('OSM_EXTRA_SMALL'));
		$options[] = HTMLHelper::_('select.option', 'input-small', Text::_('OSM_SMALL'));
		$options[] = HTMLHelper::_('select.option', 'input-medium', Text::_('OSM_MEDIUM'));
		$options[] = HTMLHelper::_('select.option', 'input-large', Text::_('OSM_LARGE'));
		$options[] = HTMLHelper::_('select.option', 'input-xlarge', Text::_('OSM_EXTRA_LARGE'));
		$options[] = HTMLHelper::_('select.option', 'input-xxlarge', Text::_('OSM_ULTRA_LARGE'));

		$lists['input_size'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'input_size',
			'class="form-select"',
			'value',
			'text',
			$item->input_size
		);

		$lists['plan_id']   = OSMembershipHelperHtml::getChoicesJsSelect(
			$lists['plan_id'],
			Text::_('OSM_TYPE_OR_SELECT_SOME_PLANS')
		);
		$lists['fieldtype'] = OSMembershipHelperHtml::getChoicesJsSelect($lists['fieldtype']);
	}
}
