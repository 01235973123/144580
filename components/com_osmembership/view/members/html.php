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
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Class OSMembershipViewMembersHtml
 *
 * @property OSMembershipHelperBootstrap $bootstrapHelper
 * @property Registry                    $params
 */
class OSMembershipViewMembersHtml extends MPFViewHtml
{
	/**
	 * Members data
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
	 * Filter Fields
	 *
	 * @var array
	 */
	protected $filters = [];

	/**
	 * Pagination object
	 *
	 * @var Pagination
	 */
	protected $pagination;

	/**
	 * Custom Fields which will be shown on Members page
	 *
	 * @var array
	 */
	protected $fields;

	/**
	 * Custom Fields Data
	 *
	 * @var array
	 */
	protected $fieldsData;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Bootstrap Helper
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * Display members list
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display()
	{
		$user = Factory::getApplication()->getIdentity();

		if (!$user->authorise('core.viewmembers', 'com_osmembership'))
		{
			if (!$user->id)
			{
				$this->requestLogin();
			}
			else
			{
				$app = Factory::getApplication();
				$app->enqueueMessage(Text::_('OSM_NOT_ALLOW_TO_VIEW_MEMBERS'));
				$app->redirect(Uri::root(), 403);
			}
		}

		/* @var OSMembershipModelMembers $model */
		$model = $this->getModel();
		$state = $model->getState();

		$this->fields          = OSMembershipHelper::getCustomFieldsForPlans(
			$state->id,
			true,
			['show_on_members_list = 1']
		);
		$this->state           = $state;
		$this->items           = $model->getData();
		$this->pagination      = $model->getPagination();
		$this->fieldsData      = $model->getFieldsData();
		$this->config          = OSMembershipHelper::getConfig();
		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();

		// Build filters
		$this->buildFilters();

		parent::display();
	}

	/**
	 * Build filter fields
	 *
	 * @param   Registry  $params
	 *
	 * @return void
	 */
	protected function buildFilters()
	{
		$rowFields = OSMembershipHelper::getCustomFieldsForPlans(0, true, ['filterable = 1']);

		$filterFieldsValues = $this->state->get('filter_fields', []);

		$db    = $this->model->getDbo();
		$query = $db->getQuery(true);

		foreach ($rowFields as $rowField)
		{
			if ($rowField->filterable && in_array($rowField->id, $this->params->get('filter_fields', [])))
			{
				if ($rowField->name == 'country')
				{
					$query->clear()
						->select('DISTINCT country')
						->from('#__osmembership_subscribers')
						->where('LENGTH(country) > 0')
						->order('country');
					$db->setQuery($query);
					$fieldOptions = $db->loadColumn();
				}
				elseif ($rowField->name == 'state')
				{
					$query->clear()
						->select('DISTINCT state')
						->from('#__osmembership_subscribers')
						->where('LENGTH(state) > 0')
						->order('state');
					$db->setQuery($query);
					$fieldOptions = $db->loadColumn();
				}
				else
				{
					$fieldOptions = explode("\r\n", $rowField->values);
				}

				$options   = [];
				$options[] = HTMLHelper::_('select.option', '', $rowField->title);

				foreach ($fieldOptions as $option)
				{
					$options[] = HTMLHelper::_('select.option', $option, $option);
				}

				$this->filters['field_' . $rowField->id] = HTMLHelper::_(
					'select.genericlist',
					$options,
					'filter_fields[field_' . $rowField->id . ']',
					' class="form-select input-medium" onchange="submit();" ',
					'value',
					'text',
					ArrayHelper::getValue($filterFieldsValues, 'field_' . $rowField->id)
				);
			}
		}
	}
}
