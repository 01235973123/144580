<?php
/**
 * @package     MPF
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Joomla CMS View List class, used to render list of records from front-end or back-end of your component
 *
 * @package      MPF
 * @subpackage   View
 * @since        2.0
 *
 * @property MPFModelList $model
 */
class MPFViewList extends MPFViewHtml
{
	/**
	 * The model state
	 *
	 * @var MPFModelState
	 */
	protected $state;

	/**
	 * List of records which will be displayed
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var \Joomla\CMS\Pagination\Pagination
	 */
	protected $pagination;

	/**
	 * The array which keeps list of "list" options which will used to display as the filter on the list
	 *
	 * @var array
	 */
	protected $lists = [];

	/**
	 * Items ordering
	 *
	 * @var array
	 */
	protected $ordering = [];

	/**
	 * Name of item view
	 *
	 * @var string
	 */
	protected $viewItem;

	/**
	 * Constructor
	 *
	 * @param   array  $config
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->viewItem = $config['view_item'] ?? MPFInflector::singularize($this->name);
	}

	/**
	 * Prepare the view before it is displayed
	 */
	protected function prepareView()
	{
		$this->state      = $this->model->getState();
		$this->items      = $this->model->getData();
		$this->pagination = $this->model->getPagination();

		if ($this->isAdminView)
		{
			$this->lists['filter_state']    = str_replace(
				['class="inputbox"', 'class="form-control"'],
				'class="input-medium form-select w-auto"',
				HTMLHelper::_('grid.state', $this->state->filter_state)
			);
			$this->lists['filter_access']   = HTMLHelper::_(
				'access.level',
				'filter_access',
				$this->state->filter_access,
				'class="form-select w-auto" onchange="submit();"',
				false
			);
			$this->lists['filter_language'] = HTMLHelper::_(
				'select.genericlist',
				HTMLHelper::_('contentlanguage.existing', true, true),
				'filter_language',
				' class="form-select" onchange="submit();" ',
				'value',
				'text',
				$this->state->filter_language
			);

			OSMembershipHelperHtml::renderSubmenu($this->name);

			$this->addToolbar();
		}
	}

	/**
	 * Method to add toolbar buttons
	 */
	protected function addToolbar()
	{
		$helperClass = $this->viewConfig['class_prefix'] . 'Helper';

		if (is_callable($helperClass . '::getActions'))
		{
			$canDo = call_user_func([$helperClass, 'getActions'], $this->name, $this->state);
		}
		else
		{
			$canDo = call_user_func(['MPFHelper', 'getActions'], $this->viewConfig['option'], $this->name, $this->state);
		}

		$languagePrefix = $this->viewConfig['language_prefix'];

		if ($this->isAdminView)
		{
			ToolbarHelper::title(Text::_(strtoupper($languagePrefix . '_' . $this->name . '_MANAGEMENT')), 'link ' . $this->name);
		}

		if ($canDo->get('core.create') && !in_array('add', $this->hideButtons))
		{
			ToolbarHelper::addNew('add', 'JTOOLBAR_NEW');
		}

		if (($canDo->get('core.edit') || $canDo->get('core.edit.own')) && isset($this->items[0]) && !in_array('edit', $this->hideButtons))
		{
			ToolbarHelper::editList('edit', 'JTOOLBAR_EDIT');
		}

		if (($canDo->get('core.delete') || $canDo->get('core.delete.own')) && isset($this->items[0]) && !in_array('delete', $this->hideButtons))
		{
			ToolbarHelper::deleteList(Text::_($languagePrefix . '_DELETE_CONFIRM'), 'delete');
		}

		if (($canDo->get('core.edit.state') || $canDo->get('core.edit.state.own'))
			&& !in_array('publish', $this->hideButtons)
			&& (isset($this->items[0]->published) || isset($this->items[0]->state)))
		{
			ToolbarHelper::publish('publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}

		// Allow child-class to add it's custom toolbar button
		$this->addCustomToolbarButtons();

		if ($this->isAdminView && $canDo->get('core.admin'))
		{
			ToolbarHelper::preferences($this->viewConfig['option']);
		}
	}

	/**
	 * Load search tools
	 *
	 * @return void
	 * @throws Exception
	 */
	protected function loadSearchTools(string $formId = '#adminForm'): void
	{
		$customOptions = [
			'filtersHidden'       => true,
			'defaultLimit'        => Factory::getApplication()->get('list_limit', 20),
			'searchFieldSelector' => '#filter_search',
			'orderFieldSelector'  => '#filter_full_ordering',
		];

		HTMLHelper::_('searchtools.form', $formId, $customOptions);
	}

	/**
	 * Load Draggable script uses to allow drag and drop re-ordering items
	 *
	 * @param   string  $tableId
	 *
	 * @return void
	 */
	protected function loadDraggableLib(string $tableId)
	{
		if ($this->state->filter_order == 'tbl.ordering')
		{
			if (MPFHelper::isJoomla4())
			{
				HTMLHelper::_('draggablelist.draggable');
			}
			else
			{
				$controller = $this->viewItem;

				$saveOrderingUrl = 'index.php?option=' . $this->viewConfig['option'] . '&task=' . $controller . '.save_order_ajax';
				HTMLHelper::_('sortablelist.sortable', $tableId, 'adminForm', strtolower($this->state->filter_order_Dir), $saveOrderingUrl);
			}
		}
	}

	/**
	 * Method to allow adding sortable header to the list view
	 *
	 * @param   string  $title
	 * @param   string  $sortBy
	 *
	 * @return string
	 */
	protected function gridSort(string $title, string $sortBy): string
	{
		return HTMLHelper::_('grid.sort', Text::_($title), $sortBy, $this->state->filter_order_Dir, $this->state->filter_order);
	}

	/**
	 * Method to allow adding searchtools sort header to the list view
	 *
	 * @param   string  $title
	 * @param   string  $sortBy
	 *
	 * @return string
	 */
	protected function searchToolsSort(string $title, string $sortBy): string
	{
		return HTMLHelper::_('searchtools.sort', $title, $sortBy, $this->state->filter_order_Dir, $this->state->filter_order);
	}

	/**
	 * Search tools ordering header
	 *
	 * @return string
	 */
	protected function searchToolsSortHeader(): string
	{
		return HTMLHelper::_(
			'searchtools.sort',
			'',
			'tbl.ordering',
			$this->state->filter_order_Dir,
			$this->state->filter_order,
			null,
			'asc',
			'JGRID_HEADING_ORDERING',
			'icon-menu-2'
		);
	}

	/**
	 * Render re-order cell
	 *
	 * @param   stdClass  $row
	 *
	 * @return void
	 */
	protected function reOrderCell($row): void
	{
		include __DIR__ . '/tmpl/reorder_cell.php';
	}

	/**
	 * Get form action
	 *
	 * @return string
	 */
	protected function getFormAction(): string
	{
		return Route::_('index.php?option=' . $this->option . '&view=' . $this->name, false);
	}

	/**
	 * Get link to edit item
	 *
	 * @param   stdClass  $row
	 * @param   string    $append
	 *
	 * @return string
	 */
	protected function getEditItemLink($row, $append = ''): string
	{
		return Route::_('index.php?option=' . $this->option . '&view=' . $this->viewItem . '&id=' . $row->id . $append, false);
	}

	/**
	 * Method to render hidden variables for management list view
	 *
	 * @return void
	 */
	protected function renderFormHiddenVariables()
	{
		include __DIR__ . '/tmpl/form_list_hidden_vars.php';
	}

	/**
	 * Method to allow adding custom toolbar buttons before Options button being displayed
	 *
	 * @return void
	 */
	protected function addCustomToolbarButtons()
	{
	}
}
