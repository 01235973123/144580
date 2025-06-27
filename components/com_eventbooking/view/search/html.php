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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;

class EventbookingViewSearchHtml extends RADViewHtml
{
	/**
	 * The search model state
	 *
	 * @var RADModelState
	 */
	protected $state;

	/**
	 * Events search result
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var Pagination
	 */
	protected $pagination;

	/**
	 * The current user view levels
	 *
	 * @var array
	 */
	protected $viewLevels;

	/**
	 * The null date string
	 *
	 * @var string
	 */
	protected $nullDate;

	/**
	 * Component config
	 *
	 * @var RADConfig
	 */
	protected $config;

	/**
	 * Bootstrap helper
	 *
	 * @var EventbookingHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * ID of active category, needed because we are using shared layout
	 *
	 * @var int
	 */
	protected $categoryId = 0;

	/**
	 * Active category, it is needed because we are using shared layout with category
	 *
	 * @var stdClass
	 */
	protected $category = null;

	/**
	 * Prepare view data
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$app      = Factory::getApplication();
		$document = $app->getDocument();
		$document->setTitle(Text::_('EB_SEARCH_RESULT'));

		$config = EventbookingHelper::getConfig();

		$active = $app->getMenu()->getActive();
		$layout = $this->getLayout();

		// Handle layout
		if ($active && isset($active->query['view']) && $active->query['view'] == $this->getName())
		{
			// This is direct menu link to category view, so use the layout from menu item setup
		}
		elseif ($this->input->getInt('hmvc_call') && $this->input->getCmd('layout'))
		{
			// Use layout from the HMVC call, in this case, it's from EB view module
		}
		elseif (in_array($layout, ['default', 'table', 'columns', 'timeline', 'grid', 'list']))
		{
			// One of the supported layout
		}
		else
		{
			// Use default layout
			$this->setLayout('default');
		}

		if ($config->multiple_booking)
		{
			if ($this->deviceType == 'mobile')
			{
				EventbookingHelperJquery::colorbox('eb-colorbox-addcart', '100%', '450px', 'false', 'false');
			}
			else
			{
				EventbookingHelperJquery::colorbox('eb-colorbox-addcart', '800px', 'false', 'false', 'false', 'false');
			}
		}

		if ($config->show_list_of_registrants)
		{
			EventbookingHelperModal::iframeModal('a.eb-colorbox-register-lists', 'eb-registrant-lists-modal');
		}

		if ($config->show_location_in_category_view || $this->getLayout() == 'timeline')
		{
			EventbookingHelperModal::iframeModal('a.eb-colorbox-map', 'eb-map-modal');
		}

		$this->viewLevels      = Factory::getApplication()->getIdentity()->getAuthorisedViewLevels();
		$this->state           = $this->model->getState();
		$this->items           = $this->model->getData();
		$this->pagination      = $this->model->getPagination();
		$this->config          = $config;
		$this->nullDate        = Factory::getContainer()->get('db')->getNullDate();
		$this->bootstrapHelper = EventbookingHelperBootstrap::getInstance();

		// Prepare display data
		EventbookingHelper::callOverridableHelperMethod(
			'Data',
			'prepareDisplayData',
			[$this->items, $this->categoryId, $this->config, $this->Itemid]
		);

		// Add cancelRegistration method
		$document
			->getWebAssetManager()
			->addInlineScript(
				'
			function cancelRegistration(registrantId)
			{
				var form = document.adminForm ;
		
				if (confirm("' . Text::_('EB_CANCEL_REGISTRATION_CONFIRM') . '"))
				{
					form.task.value = "registrant.cancel" ;
					form.id.value = registrantId ;
					form.submit() ;
				}
			}
		'
			);

		$this->setPaginationAdditionalParams();
	}

	/**
	 * Add additional params to pagination in case some filters are selected on search bar
	 *
	 * @return void
	 */
	protected function setPaginationAdditionalParams(): void
	{
		// Internal search filer states - passed from search module
		if ($this->state->search)
		{
			$this->pagination->setAdditionalUrlParam('search', $this->state->search);
		}

		if ($this->state->filter_from_date)
		{
			$this->pagination->setAdditionalUrlParam('filter_from_date', $this->state->filter_from_date);
		}

		if ($this->state->filter_to_date)
		{
			$this->pagination->setAdditionalUrlParam('filter_to_date', $this->state->filter_to_date);
		}

		if ($this->state->category_id)
		{
			$this->pagination->setAdditionalUrlParam('category_id', $this->state->category_id);
		}

		if ($this->state->location_id)
		{
			$this->pagination->setAdditionalUrlParam('location_id', $this->state->location_id);
		}

		if ($this->state->filter_address)
		{
			$this->pagination->setAdditionalUrlParam('filter_address', $this->state->filter_address);
		}

		if ($this->state->filter_distance)
		{
			$this->pagination->setAdditionalUrlParam('filter_distance', $this->state->filter_distance);
		}

		if ($this->state->filter_order)
		{
			$this->pagination->setAdditionalUrlParam('filter_order', $this->state->filter_order);
		}

		if ($this->state->filter_order_Dir)
		{
			$this->pagination->setAdditionalUrlParam('filter_order_Dir', $this->state->filter_order_Dir);
		}

		// External states, passed by link to search view
		if ($this->state->created_by)
		{
			$this->pagination->setAdditionalUrlParam('created_by', $this->state->created_by);
		}

		if ($this->state->filter_city)
		{
			$this->pagination->setAdditionalUrlParam('filter_city', $this->state->filter_city);
		}

		if ($this->state->filter_state)
		{
			$this->pagination->setAdditionalUrlParam('filter_state', $this->state->filter_state);
		}
	}
}
