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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use OSSolution\MembershipPro\Admin\Event\Profile\ProfileDisplay;

class OSMembershipViewSubscriberHtml extends MPFViewItem
{
	/**
	 * Component config data
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Plugin's output
	 *
	 * @var array
	 */
	protected $plugins;

	/**
	 * Subscriber's subscription records
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Form object
	 *
	 * @var MPFForm
	 */
	protected $form;

	/**
	 * The selected state
	 *
	 * @var string
	 */
	protected $selectedState = '';

	/**
	 * Flag to mark if state field is State field type or not
	 *
	 * @var bool
	 */
	protected $stateType = false;

	/**
	 * Set data and render the view
	 *
	 * @return void
	 * @throws Exception
	 */
	public function display()
	{
		$db    = $this->model->getDbo();
		$query = $db->getQuery(true);
		$item  = $this->model->getData();

		if (empty($item))
		{
			$app = Factory::getApplication();
			$app->enqueueMessage('Invalid Subscribers', 'warning');
			$app->redirect(Route::_('index.php?option=com_osmembership&view=subscribers', false));
		}

		$query->select('a.*, b.title AS plan_title, b.lifetime_membership, b.enable_renewal, b.recurring_subscription')
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__osmembership_plans AS b ON a.plan_id = b.id')
			->where('a.profile_id = ' . $item->id)
			->order('a.id DESC');
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (OSMembershipHelper::isUniquePlan($item->user_id))
		{
			$planId = $item->plan_id;
		}
		else
		{
			$planId = 0;
		}

		//Form fields
		$rowFields = OSMembershipHelper::getProfileFields($planId, true, $item->language);

		// Disable readonly for adding/editing subscription
		foreach ($rowFields as $rowField)
		{
			$rowField->readonly = 0;
		}

		$data = OSMembershipHelper::getProfileData($item, $planId, $rowFields);

		if (!isset($data['country']) || !$data['country'])
		{
			$config          = OSMembershipHelper::getConfig();
			$data['country'] = $config->default_country;
		}

		$form = new MPFForm($rowFields);
		$form->setData($data)->bindData();
		$form->buildFieldsDependency();

		$fields = $form->getFields();

		if (isset($fields['state']))
		{
			$this->selectedState = $fields['state']->value;

			if ($fields['state']->type == 'State')
			{
				$this->stateType = true;
			}
		}

		//Trigger third party add-on
		PluginHelper::importPlugin('osmembership');

		//Trigger plugins
		$event = new ProfileDisplay(['row' => $item]);

		$results       = Factory::getApplication()->triggerEvent($event->getName(), $event);
		$this->item    = $item;
		$this->config  = OSMembershipHelper::getConfig();
		$this->plugins = $results;
		$this->items   = $items;
		$this->form    = $form;
		parent::display();
	}

	/**
	 * Empty addToolbar method to prevent toolbar buttons from being displayed
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
	}
}
