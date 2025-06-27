<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgOSMembershipFields extends CMSPlugin implements SubscriberInterface
{
	use MPFEventResult;

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Database object.
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
		];
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

		$result = [
			'title' => Text::_('OSM_FIELDS_ASSIGNMENT'),
			'form'  => ob_get_clean(),
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

		$db         = $this->db;
		$query      = $db->getQuery(true);
		$formFields = $data['subscription_form_fields'] ?? [];
		$formFields = array_filter($formFields);

		if (!$isNew)
		{
			$query->delete('#__osmembership_field_plan')
				->where('plan_id = ' . $row->id);
			$db->setQuery($query)
				->execute();
		}

		if (!count($formFields))
		{
			return;
		}

		$query->clear()
			->insert('#__osmembership_field_plan')
			->columns($this->db->quoteName(['field_id', 'plan_id']));

		foreach ($formFields as $field)
		{
			$query->values(implode(',', $db->quote([$field, $row->id])));
		}

		$db->setQuery($query)
			->execute();
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

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   OSMembershipTablePlan  $row
	 */
	private function drawSettingForm($row)
	{
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('id, plan_id, name, title')
			->from('#__osmembership_fields')
			->where('published = 1')
			->order('plan_id, ordering');
		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		$selectedFieldIds = [];

		// Load assigned fields for this event
		if ($row->id)
		{
			$query->clear()
				->select('field_id')
				->from('#__osmembership_field_plan')
				->where('plan_id = ' . $row->id);
			$db->setQuery($query);
			$selectedFieldIds = $db->loadColumn();
		}

		$count           = 0;
		$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
		$rowFluid        = $bootstrapHelper->getClassMapping('row-fluid');
		$spanClass       = $bootstrapHelper->getClassMapping('span3');
		?>
		<div class="<?php
		echo $rowFluid; ?>">
			<?php
			foreach ($rowFields as $rowField)
			{
				$count++;
				$attributes = [];

				if ($rowField->plan_id == 0 || $rowField->name == 'email')
				{
					$attributes[] = 'disabled';
					$attributes[] = 'checked';
				}
				elseif (in_array($rowField->id, $selectedFieldIds))
				{
					$attributes[] = 'checked';
				}
				?>
				<div class="<?php
				echo $spanClass; ?>">
					<label class="checkbox">
						<input type="checkbox" value="<?php
						echo $rowField->id ?>"
						       name="subscription_form_fields[]"<?php
						if (count($attributes))
						{
							echo ' ' . implode(' ', $attributes);
						} ?>><?php
						echo '[' . $rowField->id . '] - ' . $rowField->title; ?>
					</label>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}
}
