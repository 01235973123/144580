<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php'))
{
	return;
}

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

class plgSystemScheduleDocuments extends CMSPlugin implements SubscriberInterface
{
	use MPFEventResult;

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	/**
	 * Make language files will be loaded automatically.
	 *
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	public static function getSubscribedEvents(): array
	{
		return [
			'onEditSubscriptionPlan'      => 'onEditSubscriptionPlan',
			'onAfterSaveSubscriptionPlan' => 'onAfterSaveSubscriptionPlan',
			'onProfileDisplay'            => 'onProfileDisplay',
		];
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_osmembership'))
		{
			return;
		}

		parent::registerListeners();
	}

	/**
	 * Render setting form
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onEditSubscriptionPlan(Event $event): void
	{
		/* @var OSMembershipTablePlan $row */
		[$row] = array_values($event->getArguments());

		if (!$this->isExecutable())
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);
		$form = ob_get_contents();
		ob_end_clean();

		$result = [
			'title' => Text::_('OSM_SCHEDULE_DOCUMENTS'),
			'form'  => $form,
		];

		$this->addResult($event, $result);
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onAfterSaveSubscriptionPlan(Event $event): void
	{
		/**
		 * @var string                $context
		 * @var OSMembershipTablePlan $row
		 * @var array                 $data
		 * @var                       $isNew
		 */
		[$context, $row, $data, $isNew] = array_values($event->getArguments());

		if (!$this->isExecutable())
		{
			return;
		}

		$scheduleDocuments   = $data['schedule_documents'] ?? [];
		$scheduleDocumentIds = [];
		$ordering            = 1;

		foreach ($scheduleDocuments as $scheduleDocument)
		{
			if (empty($scheduleDocument['document']))
			{
				continue;
			}

			$rowScheduleDocument = new OSMembershipTableScheduledocument($this->db);

			$rowScheduleDocument->bind($scheduleDocument);

			// Prevent item being moved to new plan on save as copy

			if ($isNew)
			{
				$rowScheduleDocument->id = 0;
			}

			$rowScheduleDocument->plan_id  = $row->id;
			$rowScheduleDocument->ordering = $ordering++;
			$rowScheduleDocument->store();
			$scheduleDocumentIds[] = $rowScheduleDocument->id;
		}

		if (!$isNew)
		{
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->delete('#__osmembership_scheduledocuments')
				->where('plan_id = ' . $row->id);

			if (count($scheduleDocumentIds))
			{
				$query->whereNotIn('id', $scheduleDocumentIds);
			}

			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Render setting form
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onProfileDisplay(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		ob_start();
		$this->drawScheduleContent($row);

		$result = [
			'title' => Text::_('OSM_MY_SCHEDULE_DOCUMENTS'),
			'form'  => ob_get_clean(),
		];

		$this->addResult($event, $result);
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		$numberArticlesEachTime         = $this->params->get('number_new_documents_each_time', 10);
		$form                           = Form::getInstance(
			'schedule_documents',
			JPATH_ROOT . '/plugins/system/scheduledocuments/form/scheduledocuments.xml'
		);
		$formData['schedule_documents'] = [];

		// Load existing schedule documents for this plan
		if ($row->id)
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('*')
				->from('#__osmembership_scheduledocuments')
				->where('plan_id = ' . $row->id)
				->order('ordering');
			$db->setQuery($query);

			foreach ($db->loadObjectList() as $scheduleContent)
			{
				$formData['schedule_documents'][] = [
					'id'          => $scheduleContent->id,
					'document'    => $scheduleContent->document,
					'number_days' => $scheduleContent->number_days,
				];
			}
		}

		for ($i = 0; $i < $numberArticlesEachTime; $i++)
		{
			$formData['schedule_documents'][] = [
				'id '         => 0,
				'document'    => '',
				'number_days' => 0,
			];
		}

		$form->bind($formData);

		foreach ($form->getFieldset() as $field)
		{
			echo $field->input;
		}
	}

	/**
	 * Display Display List of Documents which the current subscriber can download from his subscription
	 *
	 * @param   object  $row
	 */
	private function drawScheduleContent($row)
	{
		$config = OSMembershipHelper::getConfig();

		$subscriptions = OSMembershipHelper::callOverridableHelperMethod('Subscription', 'getUserSubscriptionsInfo');

		if (empty($subscriptions))
		{
			return;
		}

		$accessiblePlanIds = array_keys($subscriptions);

		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select('a.*')
			->from('#__osmembership_scheduledocuments AS a')
			->whereIn('a.plan_id', $accessiblePlanIds)
			->order('a.plan_id')
			->order('a.number_days');
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (empty($items))
		{
			return;
		}

		echo OSMembershipHelperHtml::loadCommonLayout('plugins/tmpl/scheduledocuments.php', ['items' => $items, 'subscriptions' => $subscriptions]);
	}

	/**
	 * Method to check if the plugin is executable
	 *
	 * @return bool
	 */
	private function isExecutable()
	{
		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		return true;
	}
}
