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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use OSSolution\MembershipPro\Admin\Event\Profile\MemberDisplay;

/**
 * HTML View class for Membership Pro component
 *
 * @static
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipViewMemberHtml extends MPFViewHtml
{
	/**
	 * Member data
	 *
	 * @var stdClass
	 */
	protected $item;

	/**
	 * Model state
	 *
	 * @var MPFModelState
	 */
	protected $state;

	/**
	 * Fields which will be displayed on member page
	 *
	 * @var array
	 */
	protected $fields;

	/**
	 * Member data array
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * @var
	 */
	protected $plugins;

	/**
	 * Display member
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display()
	{
		$app = Factory::getApplication();

		if (!$app->getIdentity()->authorise('core.viewmembers', 'com_osmembership'))
		{
			$app->enqueueMessage(Text::_('OSM_NOT_ALLOW_TO_VIEW_MEMBERS'));
			$app->redirect(Uri::root(), 403);
		}

		/* @var OSMembershipModelMember $model */
		$model = $this->getModel();
		$item  = $model->getData();
		$state = $model->getState();
		$db    = $model->getDbo();

		if (!$item)
		{
			throw new Exception(sprintf('Member ID %d does not exist in the system', $state->get('id')));
		}

		$wheres = [
			'show_on_profile = 1',
			'name NOT IN (' . implode(',', $db->quote(['first_name', 'last_name'])) . ')',
		];

		$fields = OSMembershipHelper::getCustomFieldsForPlans($item->plan_id, true, $wheres);

		$this->item   = $item;
		$this->state  = $state;
		$this->fields = $fields;
		$this->data   = OSMembershipHelper::getProfileData($item, $item->plan_id, $fields);
		$this->config = OSMembershipHelper::getConfig();
		$this->params = $app->getParams();

		foreach ($fields as $field)
		{
			if ($field->is_core)
			{
				continue;
			}

			if (isset($this->data[$field->name]))
			{
				$fieldValue = $this->data[$field->name];
			}
			else
			{
				$fieldValue = '';
			}

			$this->item->{$field->name} = $fieldValue;
		}

		// Trigger third party add-on
		PluginHelper::importPlugin('osmembership');

		$event = new MemberDisplay(['row' => $item]);

		$this->plugins = array_filter($app->triggerEvent($event->getName(), $event));

		// Force to use default layout
		$this->setLayout('default');

		parent::display();
	}
}
