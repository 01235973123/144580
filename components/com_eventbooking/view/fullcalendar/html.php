<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\ArrayHelper;

class EventbookingViewFullcalendarHtml extends RADViewHtml
{
	/**
	 * Model state
	 *
	 * @var RADModelState
	 */
	protected $state;

	/**
	 * Store list of filter array
	 *
	 * @var array
	 */
	protected $lists = [];

	/**
	 * Display full calendar
	 *
	 * @return void
	 * @throws Exception
	 */
	public function display()
	{
		$config = EventbookingHelper::getConfig();

		$app = Factory::getApplication();

		$wa = $app->getDocument()
			->addScriptOptions('calendarOptions', $this->getCalendarOptions())
			->addScriptOptions('displayEventInTooltip', (bool) $config->display_event_in_tooltip)
			->addScriptOptions('showFilterBar', (bool) $this->params->get('show_filter_bar', 0))
			->addScriptOptions('openEventInNewTab', (bool) $this->params->get('open_event_in_new_tab', 0))
			->getWebAssetManager()
			->useScript('core');

		// Full calendar tooltip needs jQuery, so we need to load it
		if ($config->display_event_in_tooltip)
		{
			$wa->useScript('jquery')
				->useScript('jquery-noconflict');
		}

		if ($app->getLanguage()->getTag() !== 'en-GB')
		{
			$wa->registerAndUseScript(
				'com_eventbooking.fullcalendar.moment-with-locales',
				'media/com_eventbooking/fullcalendar/moment-with-locales.min.js'
			);
		}
		else
		{
			$wa->registerAndUseScript(
				'com_eventbooking.fullcalendar.moment',
				'media/com_eventbooking/fullcalendar/moment.min.js'
			);
		}

		$wa->registerAndUseScript(
			'com_eventbooking.fullcalendar.main',
			'media/com_eventbooking/fullcalendar/main.min.js'
		)
			->registerAndUseScript(
				'com_eventbooking.fullcalendar.main.global',
				'media/com_eventbooking/fullcalendar/main.global.min.js'
			);

		EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-fullcalendar-default.min.js');

		$wa->registerAndUseStyle(
			'com_eventbooking.fullcalendar.main',
			'media/com_eventbooking/fullcalendar/main.min.css'
		);

		if ($this->params->get('menu_item_id') > 0)
		{
			$this->Itemid = $this->params->get('menu_item_id');
		}

		$this->setDocumentMetadata();

		if ($this->params->get('show_filter_bar'))
		{
			if (!$this->model->getState('id'))
			{
				$filters = [];

				$categoryIds        = array_filter(ArrayHelper::toInteger($this->params->get('category_ids', [])));
				$excludeCategoryIds = array_filter(
					ArrayHelper::toInteger($this->params->get('exclude_category_ids', []))
				);

				$filters[] = '`access` IN (' . implode(
						',',
						Factory::getApplication()->getIdentity()->getAuthorisedViewLevels()
					) . ')';

				if (count($categoryIds))
				{
					$filters[] = 'id IN (' . implode(',', $categoryIds) . ')';
				}

				if (count($excludeCategoryIds))
				{
					$filters[] = 'id NOT IN (' . implode(',', $excludeCategoryIds) . ')';
				}

				$this->lists['filter_category_id'] = EventbookingHelperHtml::getCategoryListDropdown(
					'filter_category_id',
					0,
					'class="input-large form-select"',
					EventbookingHelper::getFieldSuffix(),
					$filters
				);
			}

			if (!$this->params->get('location_id'))
			{
				// Check locations
				$locations = EventbookingHelperDatabase::getAllLocations();

				if (count($locations) > 1)
				{
					$options   = [];
					$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_ALL_LOCATIONS'), 'id', 'name');

					foreach ($locations as $location)
					{
						$options[] = HTMLHelper::_('select.option', $location->id, $location->name, 'id', 'name');
					}

					$this->lists['filter_location_id'] = HTMLHelper::_(
						'select.genericlist',
						$options,
						'filter_location_id',
						'class="input-large form-select" onchange="submit();"',
						'id',
						'name',
						0
					);
				}
			}
		}

		$this->state = $this->model->getState();

		parent::display();
	}

	/**
	 * Method to get full calendar options
	 *
	 * @return array
	 */
	protected function getCalendarOptions()
	{
		$config = EventbookingHelper::getConfig();
		$date   = new DateTime('now', new DateTimeZone(Factory::getApplication()->get('offset')));
		$year   = $this->params->get('default_year') ?: $date->format('Y');
		$month  = $this->params->get('default_month') ?: $date->format('m');
		$month  = str_pad($month, 2, '0', STR_PAD_LEFT);

		$buttons = [];

		if ($this->params->get('show_month_button'))
		{
			$buttons[] = 'dayGridMonth';
		}

		if ($this->params->get('show_week_button'))
		{
			$buttons[] = 'timeGridWeek';
		}

		if ($this->params->get('show_day_button'))
		{
			$buttons[] = 'timeGridDay';
		}

		if (count($buttons) == 1)
		{
			$buttons = [];
		}

		$defaultView = $this->params->get('default_view', 'month');

		$oldVersionViews = [
			'month'      => 'dayGridMonth',
			'agendaWeek' => 'timeGridWeek',
			'agendaDay'  => 'timeGridDay',
		];

		if (isset($oldVersionViews[$defaultView]))
		{
			$defaultView = $oldVersionViews[$defaultView];
		}

		$options = [
			'headerToolbar'    => [
				'left'   => 'prev,next' . ($this->params->get('show_today_button', 1) ? ' today' : ''),
				'center' => 'title',
				'right'  => implode(',', $buttons),
			],
			'locale'           => $this->getLocale(),
			'eventDisplay'     => 'block',
			'initialView'      => $defaultView,
			'initialDate'      => $year . '-' . $month . '-' . $date->format('d'),
			'navLinks'         => true,
			'eventSources'     => [
				Route::_(
					'index.php?option=com_eventbooking&view=fullcalendar&format=raw&Itemid=' . $this->Itemid,
					false
				),
			],
			'firstDay'         => (int) $config->calendar_start_date,
			'weekends'         => (bool) $this->params->get('show_weekend', 1),
			'hiddenDays'       => ArrayHelper::toInteger($this->params->get('hidden_days')),
			'displayEventTime' => (bool) $config->show_event_time,
			'eventTimeFormat'  => $this->params->get('event_time_format', 'HH:mm'),
			'slotLabelFormat'  => $this->params->get('slot_label_format', 'ha'),
			'slotMinTime'      => $this->params->get('slot_min_time', '00:00:00'),
			'slotMaxTime'      => $this->params->get('slot_max_time', '24:00:00'),
			'allDaySlot'       => (bool) $this->params->get('show_all_day_slot', 0),
			'buttonText'       => [
				'today' => Text::_('EB_TODAY'),
				'month' => Text::_('EB_MONTH'),
				'week'  => Text::_('EB_WEEK'),
				'day'   => Text::_('EB_DAY'),
			],
			'views'            => [
				'dayGridMonth' => [
					'titleFormat'         => $this->params->get('title_format_month', 'MMMM YYYY'),
					'showNonCurrentDates' => (bool) $this->params->get('show_non_current_dates', false),
					'fixedWeekCount'      => false,
				],
				'timeGridWeek' => [
					'titleFormat'     => $this->params->get('title_format_week', 'MMM D YYYY'),
					'dayHeaderFormat' => $this->params->get('day_header_format_week', 'ddd M/D'),
				],
				'timeGridDay'  => [
					'titleFormat' => $this->params->get('title_format_day', 'MMMM D YYYY'),
				],
			],
		];

		if ($options['locale'] !== 'en')
		{
			unset($options['views']['dayGridMonth']['titleFormat']);
		}

		return $options;
	}

	/**
	 * Get locale for calendar
	 *
	 * @return string
	 */
	private function getLocale()
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select($db->quoteName('sef'))
			->from('#__languages')
			->where('lang_code = ' . $db->quote(Factory::getApplication()->getLanguage()->getTag()));
		$db->setQuery($query);

		return $db->loadResult() ?: 'en';
	}
}
